<?php

namespace App\Http\Controllers\Admin\Students;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Timetable;
use App\Services\TimezoneService;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    public function index()
    {
        $students = User::where('user_type', User::USER_TYPE['student'])->get();
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_name' => 'required',
            'whatsapp_number' => 'required',
            'currency' => 'required',
            'hour_price' => 'required',
            'student_type' => 'nullable|in:arabic,english',
            'timezone' => 'nullable|string',
        ]);
        $data['user_type'] = User::USER_TYPE['student'];
        $data['password'] = '0000';
        // Default to arabic if not provided
        if (!isset($data['student_type'])) {
            $data['student_type'] = 'arabic';
        }
        // Default to Egypt timezone if not provided
        if (!isset($data['timezone']) || empty($data['timezone'])) {
            $data['timezone'] = 'Africa/Cairo';
        }
        $user = User::create($data);
        return redirect()->route('students.index');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check if this is a timezone-only update (from calendar)
        if ($request->has('timezone') && $request->input('timezone') && count($request->all()) <= 3) {
            // Timezone-only update
            $oldTimezone = $user->timezone ?? 'Africa/Cairo';
            $newTimezone = $request->input('timezone');
            
            $user->update(['timezone' => $newTimezone]);
            
            // If timezone changed, adjust all timetable entries
            if ($oldTimezone !== $newTimezone) {
                TimezoneService::adjustTimetableForTimezone($user->id, $oldTimezone, $newTimezone);
            }
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث المنطقة الزمنية بنجاح'
                ]);
            }
            
            return redirect()->route('students.index')->with('success', 'تم تحديث المنطقة الزمنية بنجاح');
        }
        
        // Full update (from student form)
        $data = $request->validate([
            'user_name' => 'required',
            'whatsapp_number' => 'required',
            'currency' => 'required',
            'hour_price' => 'required',
            'student_type' => 'nullable|in:arabic,english',
            'timezone' => 'nullable|string',
        ]);
        
        // Default to arabic if not provided
        if (!isset($data['student_type'])) {
            $data['student_type'] = $user->student_type ?? 'arabic';
        }
        
        // Default to current timezone if not provided
        if (!isset($data['timezone']) || empty($data['timezone'])) {
            $data['timezone'] = $user->timezone ?? 'Africa/Cairo';
        }
        
        // Check if timezone changed
        $oldTimezone = $user->timezone ?? 'Africa/Cairo';
        $newTimezone = $data['timezone'];
        
        // Update user
        $user->update($data);
        
        // If timezone changed, adjust all timetable entries
        if ($oldTimezone !== $newTimezone) {
            TimezoneService::adjustTimetableForTimezone($user->id, $oldTimezone, $newTimezone);
        }
        
        return redirect()->route('students.index');
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->route('students.index');
    }

    /**
     * Get students by timezone (for bulk hour adjustment)
     */
    public function getStudentsByTimezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
        ]);
        
        $students = User::where('user_type', User::USER_TYPE['student'])
            ->where('timezone', $request->input('timezone'))
            ->select('id', 'user_name', 'timezone')
            ->get();
        
        return response()->json([
            'success' => true,
            'students' => $students
        ]);
    }

    /**
     * Bulk adjust hours for all students in a timezone
     * Used for DST changes or government time adjustments
     */
    public function bulkAdjustHours(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
            'hours' => 'required|integer',
        ]);
        
        $timezone = $request->input('timezone');
        $hours = (int)$request->input('hours');
        
        if ($hours === 0) {
            return response()->json([
                'success' => false,
                'message' => 'عدد الساعات يجب أن يكون غير صفر'
            ], 422);
        }
        
        // Get all students with this timezone
        $students = User::where('user_type', User::USER_TYPE['student'])
            ->where('timezone', $timezone)
            ->get();
        
        $updatedStudents = 0;
        $updatedEntries = 0;
        
        foreach ($students as $student) {
            // Get all timetable entries for this student
            $timetableEntries = Timetable::where('student_id', $student->id)->get();
            
            foreach ($timetableEntries as $entry) {
                // Parse current time
                $timeParts = explode(':', $entry->start_time);
                $hour = (int)$timeParts[0];
                $minute = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
                $second = isset($timeParts[2]) ? (int)$timeParts[2] : 0;
                
                // Add/subtract hours
                $newHour = $hour + $hours;
                
                // Handle day overflow (if hour goes below 0 or above 23)
                if ($newHour < 0) {
                    $newHour = 24 + $newHour; // e.g., -1 becomes 23
                } elseif ($newHour >= 24) {
                    $newHour = $newHour - 24; // e.g., 25 becomes 1
                }
                
                $newStartTime = sprintf('%02d:%02d:%02d', $newHour, $minute, $second);
                
                // Do the same for end_time
                $endTimeParts = explode(':', $entry->end_time);
                $endHour = (int)$endTimeParts[0];
                $endMinute = isset($endTimeParts[1]) ? (int)$endTimeParts[1] : 0;
                $endSecond = isset($endTimeParts[2]) ? (int)$endTimeParts[2] : 0;
                
                $newEndHour = $endHour + $hours;
                if ($newEndHour < 0) {
                    $newEndHour = 24 + $newEndHour;
                } elseif ($newEndHour >= 24) {
                    $newEndHour = $newEndHour - 24;
                }
                
                $newEndTime = sprintf('%02d:%02d:%02d', $newEndHour, $endMinute, $endSecond);
                
                // Update the entry
                $entry->update([
                    'start_time' => $newStartTime,
                    'end_time' => $newEndTime,
                ]);
                
                $updatedEntries++;
            }
            
            $updatedStudents++;
        }
        
        return response()->json([
            'success' => true,
            'updated_students' => $updatedStudents,
            'updated_entries' => $updatedEntries,
            'message' => "تم تعديل {$updatedEntries} موعد لـ {$updatedStudents} طالب"
        ]);
    }
}
