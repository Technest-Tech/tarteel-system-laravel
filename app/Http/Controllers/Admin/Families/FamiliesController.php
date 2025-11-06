<?php

namespace App\Http\Controllers\Admin\Families;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\User;
use App\Models\Billings;
use App\Models\Courses;
use App\Models\Lessons;
use Illuminate\Http\Request;

class FamiliesController extends Controller
{
    public function index()
    {
        $families = Family::with('students')->get();
        return view('admin.families.index', compact('families'));
    }

    public function create()
    {
        $students = User::where('user_type', User::USER_TYPE['student'])->whereNull('family_id')->get();
        return view('admin.families.create', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'family_name' => 'required',
            'whatsapp_number' => 'nullable',
            'students' => 'nullable|array',
        ]);

        $family = Family::create([
            'family_name' => $data['family_name'],
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
        ]);

        if (isset($data['students']) && !empty($data['students'])) {
            User::whereIn('id', $data['students'])
                ->where('user_type', User::USER_TYPE['student'])
                ->update(['family_id' => $family->id]);
        }

        return redirect()->route('families.index');
    }

    public function show(Request $request, $id, $year = null, $month = null)
    {
        $family = Family::with('students')->findOrFail($id);
        
        // Get year and month from request parameters or URL params
        if ($request->has('year')) {
            $year = $request->input('year');
        }
        if ($request->has('month')) {
            $month = $request->input('month');
        }
        
        $year = $year ?? date('Y');
        $month = $month ?? date('m');
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);

        // Get all students in the family
        $students = $family->students;

        // Get courses for all students
        $studentIds = $students->pluck('id');
        $courses = Courses::whereIn('student_id', $studentIds)->get();

        // Get lessons for all courses
        $courseIds = $courses->pluck('id');
        $lessons = Lessons::whereIn('course_id', $courseIds)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();

        // Get individual billings per student
        $individualBillings = Billings::whereIn('student_id', $studentIds)
            ->where('month', $month)
            ->where('year', $year)
            ->selectRaw('student_id, sum(amount) as total_amount, currency')
            ->groupBy('student_id', 'currency')
            ->get();

        // Calculate total family billing
        $familyBillings = Billings::whereIn('student_id', $studentIds)
            ->where('month', $month)
            ->where('year', $year)
            ->selectRaw('sum(amount) as total_amount, currency')
            ->groupBy('currency')
            ->get();

        // Generate family billing link (use first currency found, or default to USD)
        $totalAmount = 0;
        $currency = 'USD';
        if ($familyBillings->isNotEmpty()) {
            // Sum all amounts across currencies (if multiple currencies, use first one's currency)
            $totalAmount = $familyBillings->sum('total_amount');
            $currency = $familyBillings->first()->currency;
        }

        // Generate payment link
        $familyBillingLink = null;
        if ($totalAmount > 0) {
            $familyBillingLink = route('pay.family', [
                'family_id' => $family->id,
                'month' => $month,
                'amount' => round($totalAmount, 2),
                'currency' => $currency
            ]);
        }

        return view('admin.families.show', compact(
            'family',
            'students',
            'courses',
            'lessons',
            'individualBillings',
            'familyBillings',
            'familyBillingLink',
            'year',
            'month'
        ));
    }

    public function edit($id)
    {
        $family = Family::with('students')->findOrFail($id);
        $allStudents = User::where('user_type', User::USER_TYPE['student'])->get();
        return view('admin.families.edit', compact('family', 'allStudents'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'family_name' => 'required',
            'whatsapp_number' => 'nullable',
        ]);

        $family = Family::findOrFail($id);
        $family->update([
            'family_name' => $data['family_name'],
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
        ]);

        return redirect()->route('families.index');
    }

    public function delete($id)
    {
        $family = Family::findOrFail($id);
        
        // Remove family_id from all students in this family
        User::where('family_id', $id)->update(['family_id' => null]);
        
        $family->delete();
        return redirect()->route('families.index');
    }

    public function addStudent(Request $request, $family_id)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $student = User::findOrFail($data['student_id']);
        
        // Ensure student is not already in another family
        if ($student->family_id && $student->family_id != $family_id) {
            return redirect()->back()->withErrors(['student_id' => 'This student already belongs to another family.']);
        }

        $student->update(['family_id' => $family_id]);
        return redirect()->back();
    }

    public function removeStudent($family_id, $student_id)
    {
        $student = User::where('id', $student_id)
            ->where('family_id', $family_id)
            ->firstOrFail();
        
        $student->update(['family_id' => null]);
        return redirect()->back();
    }
}
