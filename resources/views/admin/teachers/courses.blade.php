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
                            <p class="card-text">{{$course->student ? $course->student->user_name : 'No student assigned'}}</p>
                            <a href="{{route('teacher.course.lessons',['month'=>1,'course_id'=>$course->id])}}" class="btn btn-primary">دروس هذه الدورة</a>
                            <a href="javascript:void(0);" class="btn btn-danger deleteButton" onclick="confirmDelete('{{route('delete.teacher.course',$course->id)}}')">حذف !!!!!</a>
                            <script>
                                function confirmDelete(url) {
                                    Swal.fire({
                                        title: 'هل أنت متأكد؟',
                                        text: "أنت على وشك حذف الدورة!",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#3085d6',
                                        cancelButtonColor: '#d33',
                                        confirmButtonText: 'نعم، احذفه!',
                                        cancelButtonText: 'إلغاء'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = url;
                                        }
                                    })
                                }
                            </script>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>



    </div>
    <!-- Counter boxes END -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">تأكيد الحذف </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    هل تريد الحذف بالتأكيد ؟؟
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">الغاء</button>
                    <a id="confirmDeleteButton" class="btn btn-danger">حذف</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var deleteButtons = document.querySelectorAll('.deleteButton');
            var confirmDeleteButton = document.getElementById('confirmDeleteButton');
            var deleteConfirmationModal = document.getElementById('deleteConfirmationModal');

            deleteButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    confirmDeleteButton.href = button.dataset.url;
                });
            });
        });
    </script>
    <!-- Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true" style="direction: rtl">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCourseModalLabel">اضافه دورة</h5>
                </div>
                <form action="{{route('teacher.courses.store',$id)}}" method="post">
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
