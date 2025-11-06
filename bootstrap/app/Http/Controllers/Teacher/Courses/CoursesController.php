<?php

namespace App\Http\Controllers\Teacher\Courses;

use App\Http\Controllers\Controller;
use App\Models\Courses;
use App\Models\TeacherStudents;
use App\Models\User;
use Illuminate\Http\Request;

class CoursesController extends Controller
{

    public function index()
    {
        $teacher_students_id = TeacherStudents::where('teacher_id',auth()->id())->pluck('student_id');
        $students = User::whereIn('id',$teacher_students_id)->get();
        $courses = Courses::where('teacher_id', auth()->user()->id)->get();
        return view('teacher.courses.index',compact('students', 'courses'));
    }

    public function create()
    {
        return view('teacher.courses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_name' => 'required',
            'student_id' => 'required',
        ]);
        $data['teacher_id'] = auth()->user()->id;
        $course = Courses::create($data);
        return redirect()->route('courses.index');
    }

    public function edit($id)
    {
        return view('teacher.courses.edit');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'course_name' => 'required',
            'course_description' => 'required',
            'course_price' => 'required',
        ]);
        return redirect()->route('courses.index');
    }

    public function delete($id)
    {
        return redirect()->route('courses.index');
    }
}
