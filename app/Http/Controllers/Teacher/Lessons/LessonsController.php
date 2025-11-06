<?php

namespace App\Http\Controllers\Teacher\Lessons;

use App\Http\Controllers\Controller;
use App\Models\Billings;
use App\Models\Courses;
use App\Models\Lessons;
use Illuminate\Http\Request;

class LessonsController extends Controller
{
    public function index($month,$course_id)
    {
        $lessons = Lessons::where('course_id', $course_id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', now()->year)
            ->get();
        return view('teacher.lessons.index', compact('lessons','month','course_id'));
    }

    public function store(Request $request,$month,$course_id)
    {
        $data = $request->validate([
            'lesson_name' => 'required',
            'lesson_date' => 'required',
            'lesson_duration' => 'required',
        ]);
        $course = Courses::find($course_id);
        $data['course_id'] = $course_id;
        $data['teacher_id'] = auth()->user()->id;
        $data['student_id'] = $course->student_id;
        $lesson = Lessons::create($data);

        //save billing
        $this->createBilling($lesson,$data['teacher_id'],$data['student_id']);

        return redirect()->route('course.lessons',[$request->month,$course_id]);
    }

    public function createBilling($lesson,$teacher_id,$student_id)
    {
       $user = \App\Models\User::find($student_id);
       $data['lesson_id'] = $lesson->id;
       $data['teacher_id'] = $teacher_id;
       $data['student_id'] = $student_id;
       $data['currency'] = $user->currency;
       $data['amount'] = $lesson->lesson_duration * $user->hour_price;
       $data['is_paid'] = '0';
       $data['month'] = date('m');
       $data['year'] = date('Y');

       Billings::create($data);
    }
}
