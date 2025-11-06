@extends('layouts.index')

@section('content')
    <!-- Page main content START -->
    <div class="page-content-wrapper border">

        <!-- Title -->
        <div class="row">
            <div class="col-8">
                <h1 class="h3 mb-2 mb-sm-0">العائلات</h1>
            </div>
            <div class="col-4">
                <a href="{{route('families.create')}}" class="btn btn-sm btn-primary mb-0" style="float: left">اضافة عائلة</a>
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
                            </select>
                        </form>
                    </div>
                </div>
                <!-- Search and select END -->
            </div>
            <!-- Card header END -->
            <div class="row">
           @foreach($families as $family)

                  <div class="col-12 p-4 family-card">
                      <div class="card bg-transparent border h-100">
                          <!-- Card header -->
                          <div class="card-header bg-transparent border-bottom d-flex justify-content-between">
                              <div class="d-sm-flex align-items-center">
                                  <!-- Info -->
                                  <div class="ms-0 ms-sm-2 mt-2 mt-sm-0">
                                      <h5 class="mb-0 family-name"><a href="{{route('families.show', $family->id)}}">{{$family->family_name}}</a></h5>
                                  </div>
                              </div>

                              <!-- Edit dropdown -->
                              <div class="dropdown text-end">
                                  <a href="#" class="btn btn-sm btn-light btn-round small mb-0" role="button" id="dropdownShare{{$family->id}}" data-bs-toggle="dropdown" aria-expanded="false">
                                      <i class="bi bi-three-dots fa-fw"></i>
                                  </a>
                                  <!-- dropdown button -->
                                  <ul class="dropdown-menu dropdown-w-sm dropdown-menu-end min-w-auto shadow rounded" aria-labelledby="dropdownShare{{$family->id}}">
                                      <a class="dropdown-item" href="{{route('families.edit', $family->id)}}"><i class="bi bi-pencil-square fa-fw me-2"></i>تعديل</a>
                                      <li>
                                          <a class="dropdown-item" href="javascript:void(0);" onclick="confirmDelete('{{route('families.delete',$family->id)}}')">
                                              <i class="bi bi-trash fa-fw me-2"></i>حذف
                                          </a>
                                      </li>

                                      <script>
                                          function confirmDelete(url) {
                                              Swal.fire({
                                                  title: 'هل أنت متأكد؟',
                                                  text: "أنت على وشك حذف العائلة!",
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
                              <!-- Members -->
                              <div class="d-flex justify-content-between align-items-center mb-3">
                                  <div class="d-flex align-items-center">
                                      <div class="icon-md bg-success bg-opacity-10 text-success rounded-circle flex-shrink-0"><i class="bi bi-people fa-fw"></i></div>
                                      <h6 class="mb-0 ms-2 fw-light">عدد الأعضاء</h6>
                                  </div>
                                  <span class="mb-0 fw-bold">{{$family->students->count()}}</span>
                              </div>

                              <!-- WhatsApp -->
                              @if($family->whatsapp_number)
                              <div class="d-flex justify-content-between align-items-center mb-3">
                                  <div class="d-flex align-items-center">
                                      <div class="icon-md bg-purple bg-opacity-10 text-purple rounded-circle flex-shrink-0"><i class="fas fa-phone"></i></div>
                                      <h6 class="mb-0 ms-2 fw-light">رقم الواتساب</h6>
                                  </div>
                                  <span class="mb-0 fw-bold">{{$family->whatsapp_number}}</span>
                              </div>
                              @endif

                          </div>
                      </div>
                  </div>

           @endforeach
            </div>


        </div>
        <!-- Card END -->
    </div>
    <!-- Page main content END -->

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();

            document.querySelectorAll('.family-card').forEach(function(cardElement) {
                const nameElement = cardElement.querySelector('.family-name');
                const name = nameElement.textContent.toLowerCase();
                if (name.includes(query)) {
                    cardElement.style.display = '';
                } else {
                    cardElement.style.display = 'none';
                }
            });
        });
    </script>

@endsection

