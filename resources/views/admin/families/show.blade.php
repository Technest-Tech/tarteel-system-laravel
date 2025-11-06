@extends('layouts.index')

@section('content')
    <style>
        .fancy-card {
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            padding: 16px;
            margin: 16px 0;
            background-color: #fff;
        }
    </style>
    <!-- Page main content START -->
    <div class="page-content-wrapper border">

        <!-- Title -->
        <div class="row">
            <div class="col-12 text-end">
                <h1 class="h3 mb-2 mb-sm-0">عائلة: {{ $family->family_name }}</h1>
                <a href="{{ route('families.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div><br>

        <!-- Year/Month Selection -->
        <div class="row">
            <div class="col-12">
                <form method="GET" action="{{route('families.show', $family->id)}}" class="row g-3">
                    <div class="col-md-3">
                        <label for="year" class="form-label">السنة</label>
                        <input type="number" class="form-control" id="year" name="year" value="{{$year}}" min="2020" max="2030" required>
                    </div>
                    <div class="col-md-3">
                        <label for="month" class="form-label">الشهر</label>
                        <select class="form-select" id="month" name="month" required>
                            <option value="1" @if($month == '01') selected @endif>يناير</option>
                            <option value="2" @if($month == '02') selected @endif>فبراير</option>
                            <option value="3" @if($month == '03') selected @endif>مارس</option>
                            <option value="4" @if($month == '04') selected @endif>أبريل</option>
                            <option value="5" @if($month == '05') selected @endif>مايو</option>
                            <option value="6" @if($month == '06') selected @endif>يونيو</option>
                            <option value="7" @if($month == '07') selected @endif>يوليو</option>
                            <option value="8" @if($month == '08') selected @endif>أغسطس</option>
                            <option value="9" @if($month == '09') selected @endif>سبتمبر</option>
                            <option value="10" @if($month == '10') selected @endif>أكتوبر</option>
                            <option value="11" @if($month == '11') selected @endif>نوفمبر</option>
                            <option value="12" @if($month == '12') selected @endif>ديسمبر</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">عرض</button>
                    </div>
                </form>
            </div>
        </div>
        <hr>

        <!-- Family Members -->
        <div class="card bg-transparent border mb-4">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">أعضاء العائلة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($students as $student)
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{$student->user_name}}</h6>
                                    <p class="card-text small">رقم الواتساب: {{$student->whatsapp_number}}</p>
                                    <p class="card-text small">العملة: {{$student->currency}}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3">
                    <form action="{{route('families.add.student', $family->id)}}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <select class="form-select" name="student_id" required>
                                <option value="">اختر طالب لإضافته</option>
                                @foreach(\App\Models\User::where('user_type', \App\Models\User::USER_TYPE['student'])->whereNull('family_id')->get() as $availableStudent)
                                    <option value="{{$availableStudent->id}}">{{$availableStudent->user_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success">إضافة طالب</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Total Family Billing -->
        @if($familyBillingLink)
        <div class="card bg-transparent border mb-4" style="background-color: rgba(168,255,153,0.65)">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">فاتورة العائلة الإجمالية</h5>
            </div>
            <div class="card-body text-center">
                <h4>إجمالي الفاتورة: 
                    @foreach($familyBillings as $fb)
                        {{$fb->total_amount}} {{$fb->currency}}
                        @if(!$loop->last) + @endif
                    @endforeach
                </h4>
                <div class="mt-3">
                    <a target="_blank" href="{{$familyBillingLink}}" class="btn btn-primary btn-lg">
                        رابط الدفع للعائلة
                    </a>
                    @if($family->whatsapp_number)
                    <a target="_blank" href="https://wa.me/{{ $family->whatsapp_number }}?text={{ urlencode('Hello From Tarteel Academy this is your family billing , please click the link to pay: ' . $familyBillingLink) }}" class="btn btn-success btn-lg">
                        <img src="{{asset('whatsapp.png')}}" style="width: 30px"> إرسال عبر واتساب
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Individual Student Billings -->
        <div class="card bg-transparent border">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">فواتير الطلاب الفردية</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">ارسال واتساب</th>
                        <th scope="col" class="text-center">تم الدفع</th>
                        <th scope="col" class="text-center">الطالب</th>
                        <th scope="col" class="text-center">المبلغ</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($individualBillings as $billing)
                        @php
                          $student = \App\Models\User::find($billing->student_id);
                        @endphp
                        @if(!is_null($student))
                        <tr>
                            <td class="text-center">
                                <a target="_blank" href="https://wa.me/{{ $student->whatsapp_number }}?text={{ urlencode('Hello From Tarteel Academy this is your billing , please click the link to pay: ' . url(route('pay', ['student_id' => $student->id, 'month' => $month, 'amount' => $billing->total_amount, 'currency' => $billing->currency], true))) }}">
                                    <img src="{{asset('whatsapp.png')}}" style="width: 40px">
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="javascript:void(0);" onclick="confirmPayment('{{route('pay.bill',['student_id'=>$billing->student_id,'month'=>$month])}}')">
                                    <img src="{{asset('accept.png')}}" style="width: 40px">
                                </a>
                            </td>
                            <td class="text-center">{{ $student->user_name }}</td>
                            <td class="text-center">{{ $billing->total_amount .' '. $billing->currency}}</td>
                        </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Courses and Lessons -->
        <div class="card bg-transparent border mt-4">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">الدورات والدروس</h5>
            </div>
            <div class="card-body">
                @foreach($students as $student)
                    <h6 class="mt-3">الطالب: {{$student->user_name}}</h6>
                    @php
                        $studentCourses = $courses->where('student_id', $student->id);
                    @endphp
                    @if($studentCourses->count() > 0)
                        @foreach($studentCourses as $course)
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6>{{$course->course_name}}</h6>
                                    <p class="small mb-0">
                                        الدروس: 
                                        @php
                                            $courseLessons = $lessons->where('course_id', $course->id);
                                        @endphp
                                        {{$courseLessons->count()}} درس
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">لا توجد دورات لهذا الطالب</p>
                    @endif
                @endforeach
            </div>
        </div>

    </div>
    <!-- Page main content END -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmPayment(url) {
            Swal.fire({
                title: 'هل أنت متأكد من تأكيد الدفع؟',
                text: "بمجرد التأكيد سوف يتم تحديد فاتورة هذا الطالب للشهر الحالي كمدفوعه",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'أنا متأكد'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        }
    </script>
@endsection

