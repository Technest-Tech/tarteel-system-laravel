@extends('layouts.index')

@section('content')
    <!-- Page main content START -->
    <div class="page-content-wrapper border">

        <!-- Title -->
        <div class="row">
            <div class="col-8">
                <h1 class="h3 mb-2 mb-sm-0">الطلاب</h1>
            </div>
            <div class="col-4">
                <a href="{{route('students.create')}}" class="btn btn-sm btn-primary mb-0" style="float: left" type="button" data-bs-toggle="modal" data-bs-target="#addStudentModal">اضافة طالب</a>
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
           @foreach($students as $student)

                  <div class="col-12 p-4 student-card">
                      <div class="card bg-transparent border h-100">
                          <!-- Card header -->
                          <div class="card-header bg-transparent border-bottom d-flex justify-content-between">
                              <div class="d-sm-flex align-items-center">
                                  <!-- Info -->
                                  <div class="ms-0 ms-sm-2 mt-2 mt-sm-0">
                                      <h5 class="mb-0 student-name"><a href="#">{{$student->user_name}}</a></h5>
                                  </div>
                              </div>

                              <!-- Edit dropdown -->
                              <div class="dropdown text-end">
                                  <a href="#" class="btn btn-sm btn-light btn-round small mb-0" role="button" id="dropdownShare2" data-bs-toggle="dropdown" aria-expanded="false">
                                      <i class="bi bi-three-dots fa-fw"></i>
                                  </a>
                                  <!-- dropdown button -->
                                  <ul class="dropdown-menu dropdown-w-sm dropdown-menu-end min-w-auto shadow rounded" aria-labelledby="dropdownShare2">
                                      <a class="dropdown-item" href="#" onclick="openEditModal('{{ $student->id }}', '{{ $student->user_name }}', '{{ $student->whatsapp_number }}', '{{ $student->hour_price }}', '{{ $student->currency }}', '{{ $student->student_type ?? 'arabic' }}', '{{ $student->timezone ?? 'Africa/Cairo' }}')"><i class="bi bi-pencil-square fa-fw me-2"></i>تعديل</a>
                                      <li>
                                          <a class="dropdown-item" href="javascript:void(0);" onclick="confirmDelete('{{route('students.delete',$student->id)}}')">
                                              <i class="bi bi-trash fa-fw me-2"></i>حذف
                                          </a>
                                      </li>

                                      <script>
                                          function confirmDelete(url) {
                                              Swal.fire({
                                                  title: 'هل أنت متأكد؟',
                                                  text: "أنت على وشك حذف الطالب!",
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
                                  </ul>
                              </div>
                          </div><br>

                          <div class="card-body">
                              <!-- Payments -->
                              <div class="d-flex justify-content-between align-items-center mb-3">
                                  <div class="d-flex align-items-center">
                                      <div class="icon-md bg-success bg-opacity-10 text-success rounded-circle flex-shrink-0"><i class="bi bi-currency-dollar fa-fw"></i></div>
                                      <h6 class="mb-0 ms-2 fw-light">سعر الساعة</h6>
                                  </div>
                                  <span class="mb-0 fw-bold">{{$student->hour_price . ' '.$student->currency}}</span>
                              </div>

                              <!-- Total courses -->
                              <div class="d-flex justify-content-between align-items-center mb-3">
                                  <div class="d-flex align-items-center">
                                      <div class="icon-md bg-purple bg-opacity-10 text-purple rounded-circle flex-shrink-0"><i class="fas fa-phone"></i></div>
                                      <h6 class="mb-0 ms-2 fw-light">رقم الواتساب</h6>
                                  </div>
                                  <span class="mb-0 fw-bold">{{$student->whatsapp_number}}</span>
                              </div>

                          </div>
                      </div>
                  </div>

           @endforeach
            </div>


        </div>
        <!-- Card END -->
    </div>
    <!-- Page main content END -->

    <!-- Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">اضف طالب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{route('students.store')}}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="studentName" class="form-label">اسم الطالب</label>
                            <input type="text" class="form-control" id="studentName" name="user_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentEmail" class="form-label">رقم الواتساب</label>
                            <input type="text" class="form-control" id="studentEmail" name="whatsapp_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentEmail" class="form-label">سعر الساعة</label>
                            <input type="text" class="form-control" id="studentEmail" name="hour_price" required>
                        </div>

                        <div class="mb-3">
                            <label for="studentEmail" class="form-label">العملة</label>
                            <select class="form-select" id="currency" name="currency" required>
                                @foreach(\App\Models\User::CURRENCY as $currency)
                                    <option value="{{ $currency }}">{{ $currency }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="studentType" class="form-label">نوع الطالب</label>
                            <select class="form-select" id="studentType" name="student_type" required>
                                <option value="arabic">عربي</option>
                                <option value="english">إنجليزي</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="studentTimezone" class="form-label">المنطقة الزمنية</label>
                            <select class="form-select" id="studentTimezone" name="timezone" required>
                                @foreach(\App\Services\TimezoneService::getTimezoneOptions() as $tzValue => $tzLabel)
                                    <option value="{{ $tzValue }}" {{ $tzValue == 'Africa/Cairo' ? 'selected' : '' }}>{{ $tzLabel }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">سيتم تعديل جميع مواعيد الحصص تلقائياً عند تغيير المنطقة الزمنية</small>
                        </div>

                        <!-- Add more fields as necessary -->
                        <button type="submit" class="btn btn-primary">اضافة</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentModalLabel">تعديل الطالب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editStudentForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="studentName" class="form-label">اسم الطالب</label>
                            <input type="text" class="form-control" id="studentName2" name="user_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="whatsappNumber" class="form-label">رقم الواتساب</label>
                            <input type="text" class="form-control" id="whatsappNumber2" name="whatsapp_number" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentHourPrice" class="form-label">سعر الساعة</label>
                            <input type="text" class="form-control" id="studentHourPrice" name="hour_price" required>
                        </div>
                        <div class="mb-3">
                            <label for="studentCurrency" class="form-label">العملة</label>
                            <select class="form-select" id="studentCurrency" name="currency" required>
                                @foreach(\App\Models\User::CURRENCY as $currency)
                                    <option value="{{ $currency }}">{{ $currency }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="studentType2" class="form-label">نوع الطالب</label>
                            <select class="form-select" id="studentType2" name="student_type" required>
                                <option value="arabic">عربي</option>
                                <option value="english">إنجليزي</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="studentTimezone2" class="form-label">المنطقة الزمنية</label>
                            <select class="form-select" id="studentTimezone2" name="timezone" required>
                                @foreach(\App\Services\TimezoneService::getTimezoneOptions() as $tzValue => $tzLabel)
                                    <option value="{{ $tzValue }}">{{ $tzLabel }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">سيتم تعديل جميع مواعيد الحصص تلقائياً عند تغيير المنطقة الزمنية</small>
                        </div>
                        <button type="submit" class="btn btn-primary">تحديث</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
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

        function openEditModal(id, userName, whatsappNumber, hourPrice, currency, studentType, timezone) {
            console.log(userName, whatsappNumber);
            // Update form action with the student id
            document.getElementById('editStudentForm').action = 'student/update/' + id;

            // Update form fields with the student data
            document.getElementById('studentName2').value = userName;
            document.getElementById('whatsappNumber2').value = whatsappNumber;
            document.getElementById('studentHourPrice').value = hourPrice;
            document.getElementById('studentCurrency').value = currency;
            document.getElementById('studentType2').value = studentType || 'arabic';
            document.getElementById('studentTimezone2').value = timezone || 'Africa/Cairo';

            // Show the modal
            var editStudentModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
            editStudentModal.show();
        }
    </script>

@endsection
