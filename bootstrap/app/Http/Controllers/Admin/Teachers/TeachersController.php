<?php

namespace App\Http\Controllers\Admin\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Billings;
use App\Models\Courses;
use App\Models\TeacherStudents;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeachersController extends Controller
{
    public function index()
    {
        $teachers = User::where('user_type', User::USER_TYPE['teacher'])->get();
        $students = User::where('user_type', User::USER_TYPE['student'])->get();
        return view('admin.teachers.index', compact('teachers','students'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
            'students' => 'required|array',
        ]);

        $data['user_type'] = User::USER_TYPE['teacher'];
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        foreach ($data['students'] as $student) {
            TeacherStudents::create([
                'teacher_id' => $user->id,
                'student_id' => $student,
            ]);
        }

        return redirect()->route('teachers.index');
    }

    public function edit($id)
    {
        $teacher = User::find($id);
        //here get the students from the teacher students table
        $students_ids = TeacherStudents::where('teacher_id', $id)->pluck('student_id');
        $students = User::where('user_type', User::USER_TYPE['student'])->whereIn('id', $students_ids)->get();

        return view('admin.teachers.edit', compact('teacher','students'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'user_name' => 'required',
            'email' => 'required|email',
        ]);
        $user = User::find($id);
        $user->update(['user_name' => $data['user_name'], 'email' => $data['email']]);
        return redirect()->route('teachers.index');
    }

    public function delete($id)
    {
        $user = User::find($id);
        $teacher_students = TeacherStudents::where('teacher_id', $id)->delete();
        $courses = Courses::where('teacher_id', $id)->delete();
        $lessons = \App\Models\Lessons::where('teacher_id', $id)->delete();
        $billings = Billings::where('teacher_id', $id)->delete();
        $user->delete();
        return redirect()->route('teachers.index');
    }

    public function removeStudent($teacher_id, $student_id)
    {
        TeacherStudents::where('teacher_id', $teacher_id)->where('student_id', $student_id)->delete();
        return redirect()->back();
    }

    public function addStudent($teacher_id,Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required',
        ]);
        TeacherStudents::create([
            'teacher_id' => $teacher_id,
            'student_id' => $data['student_id'],
        ]);
        return redirect()->back();
    }

    public function teacherCourses($id)
    {
        $teacher_students_id = TeacherStudents::where('teacher_id',$id)->pluck('student_id');
        $students = User::whereIn('id',$teacher_students_id)->get();
        $courses = Courses::where('teacher_id', $id )->get();
        return view('admin.teachers.courses',compact('students', 'courses','id'));
    }

    public function storeTeacherCourses(Request $request,$id)
    {
        $data = $request->validate([
            'course_name' => 'required',
            'student_id' => 'required',
        ]);
        $data['teacher_id'] = $id;
        $course = Courses::create($data);
        return redirect()->route('teacher.courses',$id);
    }

    public function deleteTeacherCourse($id)
    {
        $course = Courses::find($id);
        $lessons = \App\Models\Lessons::where('course_id',$id)->pluck('id');
        $billings = Billings::whereIn('lesson_id',$lessons)->delete();
        \App\Models\Lessons::where('course_id',$id)->delete();
        $course->delete();
        return redirect()->back();
    }

    public function teacherCourseLessons($month,$course_id)
    {
        $lessons = \App\Models\Lessons::where('course_id', $course_id)
            ->whereMonth('created_at', $month)
            ->get();
        return view('admin.teachers.lessons', compact('lessons','month','course_id'));
    }

    public function storeTeacherCourseLessons(Request $request,$month,$course_id)
    {
        $data = $request->validate([
            'lesson_name' => 'required',
            'lesson_date' => 'required',
            'lesson_duration' => 'required',
        ]);
        $course = Courses::find($course_id);
        $data['course_id'] = $course_id;
        $data['teacher_id'] = $course->teacher_id;
        $data['student_id'] = $course->student_id;
        $lesson = \App\Models\Lessons::create($data);
        $this->createBilling($lesson,$data['teacher_id'],$data['student_id']);
        return redirect()->route('teacher.course.lessons',[$month,$course_id]);
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

    public function deleteTeacherCourseLesson($id,$month)
    {
        $lesson = \App\Models\Lessons::find($id);
        $course_id = $lesson->course_id;
        $billings = Billings::where('lesson_id',$id)->delete();
        $lesson->delete();
        return redirect()->route('teacher.course.lessons',[$month,$course_id]);
    }
}
