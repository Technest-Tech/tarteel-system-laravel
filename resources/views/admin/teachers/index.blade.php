@extends('layouts.index')

@section('content')
    <!-- Page main content START -->
    <div class="page-content-wrapper border">

        <!-- Title -->
        <div class="row">
            <div class="col-8">
                <h1 class="h3 mb-2 mb-sm-0">المعلمين</h1>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li >{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="col-4">
                <a href="{{route('teachers.create')}}" class="btn btn-sm btn-primary mb-0" style="float: left" type="button" data-bs-toggle="modal" data-bs-target="#addTeacherModal">اضافة معلم</a>
            </div>
        </div><br>

        <!-- Card START -->
        <div class="card bg-transparent border">

            <!-- Card header START -->
            <div class="card-header bg-light border-bottom">
                <!-- Search and select START -->
                <div class="row g-3 align-items-center justify-content-between">

                    <!-- Search bar -->
                    <div class="col-md-8">
                        <form class="rounded position-relative">
                            <input class="form-control bg-body" id="searchInput" type="search" placeholder="بحث" aria-label="Search">
                        </form>
                    </div>

                    <!-- Select option -->
                    <div class="col-md-3" style="display: none">
                        <!-- Short by filter -->
                        <form>
                            <select class="form-select js-choice border-0 z-index-9" aria-label=".form-select-sm">
                                <option value="">Sort by</option>
                                <option>Newest</option>
                                <option>Oldest</option>
                                <option>Accepted</option>
                                <option>Rejected</option>
                            </select>
                        </form>
                    </div>
                </div>
                <!-- Search and select END -->
            </div>
            <!-- Card header END -->
            <div class="row">
           @foreach($teachers as $teacher)

                  <div class="col-12 p-4 student-card">
                      <div class="card bg-transparent border h-100">
                          <!-- Card header -->
                          <div class="card-body  bg-transparent border-bottom d-flex justify-content-between">
                              <div class="d-flex flex-column align-items-start">
                                  <!-- Info -->
                                  <div class="ms-0 ms-sm-2 mt-2 mt-sm-0">
                                      <h6 class="mb-0 student-name"><a href="#">{{$teacher->user_name}}</a></h6>
                                  </div>
                                  <!-- Info -->
                                  <div class="ms-0 ms-sm-2 mt-2 mt-sm-0">
                                      <h6 class="mb-0 student-name"><a href="#">{{$teacher->email}}</a></h6>
                                  </div>
                              </div>

                              <!-- Edit dropdown -->
                               <!-- Edit dropdown -->
                              <div class="dropdown text-end" style="margin-bottom: 70px">
                                  <a href="#" class="btn btn-sm btn-light btn-round small mb-0" role="button" id="dropdownShare2" data-bs-toggle="dropdown" aria-expanded="false">
                                      <i class="bi bi-three-dots fa-fw"></i>
                                  </a>
                                  <!-- dropdown button -->
                                  <ul class="dropdown-menu dropdown-w-sm dropdown-menu-end min-w-auto shadow rounded" aria-labelledby="dropdownShare2">
                                      <a class="dropdown-item" href="{{route('teachers.edit',$teacher->id)}}" ><i class="bi bi-pencil-square fa-fw me-2"></i>تعديل</a>
                                      <li>
                                          <a class="dropdown-item" href="javascript:void(0);" onclick="confirmDelete('{{route('teachers.delete',$teacher->id)}}')">
                                              <i class="bi bi-trash fa-fw me-2"></i>حذف
                                          </a>
                                      </li>

                                      <script>
                                          function confirmDelete(url) {
                                              Swal.fire({
                                                  title: 'هل أنت متأكد؟',
                                                  text: "أنت على وشك حذف المعلم!",
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
                                      <li><a class="dropdown-item" href="{{route('teacher.courses',$teacher->id)}}"><i class="bi bi-laptop fa-fw me-2"></i>دورات المعلم</a></li>
                                  </ul>
                              </div>
                          </div><br>
                      </div>
                  </div>

           @endforeach
            </div>


        </div>
        <!-- Card END -->
    </div>
    <!-- Page main content END -->

    <!-- Modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTeacherModalLabel">اضف معلم</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{route('teachers.store')}}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="teacherName" class="form-label">اسم المعلم</label>
                            <input type="text" class="form-control" id="teacherName" name="user_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="teacherEmail" class="form-label">البريد الإلكتروني</label>
                            <input type="email" class="form-control" id="teacherEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="teacherPassword" class="form-label">كلمة المرور</label>
                            <input type="password" class="form-control" id="teacherPassword" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="teacherPasswordConfirmation" class="form-label">تأكيد كلمة المرور</label>
                            <input type="password" class="form-control" id="teacherPasswordConfirmation" name="password_confirmation" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentList" class="form-label">قائمة الطلاب</label>
                            <select class="form-select" id="studentList" name="students[]" multiple required>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->user_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="salaryArabic" class="form-label">راتب العربي (سعر الساعة)</label>
                            <input type="number" step="0.01" class="form-control" id="salaryArabic" name="salary_arabic" placeholder="سعر الساعة للطلاب العرب">
                        </div>
                        <div class="mb-3">
                            <label for="salaryEnglish" class="form-label">راتب الإنجليزي (سعر الساعة)</label>
                            <input type="number" step="0.01" class="form-control" id="salaryEnglish" name="salary_english" placeholder="سعر الساعة للطلاب الإنجليز">
                        </div>
                        <button type="submit" class="btn btn-primary">اضافة</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            // Hide all options after the first 20
            $('#studentList option').slice(20).hide();

            // Show all options when the select element is clicked
            $('#studentList').on('click', function() {
                $('#studentList option').show();
            });
        });
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();

            document.querySelectorAll('.student-card').forEach(function(cardElement) {
                const nameElement = cardElement.querySelector('.student-name');
                const name = nameElement.textContent.toLowerCase();
                if (name.includes(query)) {
                    cardElement.style.display = '';
                } else {
                    cardElement.style.display = 'none';
                }
            });
        });

        function openEditModal(id, userName, email, students) {
            // Parse the students string into an array
            students = JSON.parse(students);
            // Update form action with the teacher id
            document.getElementById('editTeacherForm').action = 'teachers/update/' + id;

            // Update form fields with the teacher data
            document.getElementById('teacherName').value = userName;
            document.getElementById('teacherEmail').value = email;

            // Show the modal
            var editTeacherModal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
            editTeacherModal.show();
        }
    </script>

@endsection
