@extends('layouts.index')

@section('content')

    <div class="page-content-wrapper border">

        <!-- Title -->
        <div class="row">
            <div class="col-6">
                <!-- Button trigger modal -->
                <button type="button" class="btn-info btn-lg" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                    <i class="fas fa-plus-square me-3"></i> اضافه دورة
                </button>
            </div>
            <div class="col-6 mb-3">
                <h1 class="h3 mb-2 mb-sm-0 text-end">الدورات</h1>
            </div>

        </div>

        <hr>
        <div class="row justify-content-center" style="direction: rtl">
            @foreach($courses as $course)
                <div class="col-md-2 p-2">
                    <div class="card shadow-lg border-0 rounded-lg">
                        <div class="card-body">
                            <h5 class="card-title">{{$course->course_name}}</h5>
                            <p class="card-text">{{ $course->student ? $course->student->user_name : 'No student assigned' }}</p>
                            <a href="{{route('course.lessons',['month'=>1,'course_id'=>$course->id])}}" class="btn btn-primary">دروس هذه الدورة</a>
                        </div>
                    </div>
                </div>
          @endforeach
        </div>



    </div>
    <!-- Counter boxes END -->
    <!-- Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true" style="direction: rtl">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCourseModalLabel">اضافه دورة</h5>
                </div>
                <form action="{{route('courses.store')}}" method="post">
                    @csrf
                <div class="modal-body">

                       <div class="mb-3">
                           <label for="courseName" class="form-label">اسم الدورة</label>
                           <input type="text" class="form-control" id="courseName" name="course_name">
                       </div>

                          <div class="mb-3">
                            <label for="teacherName" class="form-label">اختر الطالب</label>
                            <select class="form-select" id="teacherName" name="student_id">
                                <option selected>اختر الطالب</option>
                                @foreach($students as $student)
                                    <option value="{{$student->id}}">{{$student->user_name}}</option>
                                @endforeach
                            </select>
                        </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">اغلاق</button>
                    <button type="submit" class="btn btn-primary">اضافه </button>
                </div>
                </form>
            </div>
        </div>
    </div>

@endsection
