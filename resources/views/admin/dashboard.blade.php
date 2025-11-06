@extends('layouts.index')

@section('content')
    <div class="page-content-wrapper border">

        <!-- Title -->
        <div class="row">
            <div class="col-12 mb-3">
                <h1 class="h3 mb-2 mb-sm-0 text-end">احصائيات هذا الشهر</h1>
                <div class="text-end">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="d-inline">
                        <select name="year" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                            @for($y = 2029; $y >= date('Y') - 5; $y--)
                                <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                        <label class="ms-2">اختر السنة</label>
                    </form>
                </div>
            </div>
        </div>

        @if(auth()->user()->user_type == \App\Models\User::USER_TYPE['admin'])
            <!-- Counter boxes START -->
            <div class="row g-4 mb-4">
                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-warning bg-opacity-15 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <h2 class="mb-0 fw-bold">{{\App\Models\Billings::whereMonth('created_at',\Carbon\Carbon::now()->month)->where('year',request('year',date('Y')))->where('currency','USD')->sum('amount')}}</h2>
                                <span class="mb-0 h5 fw-light">دولار</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-warning text-white mb-0"><i class="fas fa-dollar-sign"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-purple bg-opacity-10 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <h2 class=" mb-0 fw-bold">{{\App\Models\Billings::whereMonth('created_at',\Carbon\Carbon::now()->month)->where('year',request('year',date('Y')))->where('currency','GBP')->sum('amount')}}</h2>
                                <span class="mb-0 h5 fw-light">جنيه استرليني</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-purple text-white mb-0"><i class="fas fa-pound-sign"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-primary bg-opacity-10 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <h2 class="mb-0 fw-bold">{{\App\Models\Billings::whereMonth('created_at',\Carbon\Carbon::now()->month)->where('year',request('year',date('Y')))->where('currency','EUR')->sum('amount')}}</h2>
                                <span class="mb-0 h5 fw-light">يورو</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-primary text-white mb-0"><i class="fas fa-euro-sign"></i></div>
                        </div>
                    </div>
                </div>
                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-primary bg-opacity-10 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <h2 class="mb-0 fw-bold">{{\App\Models\Billings::whereMonth('created_at',\Carbon\Carbon::now()->month)->where('year',request('year',date('Y')))->where('currency','NZD')->sum('amount')}}</h2>
                                <span class="mb-0 h5 fw-light">دولار نيوزلندي</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-primary text-white mb-0"><i class="fas fa-euro-sign"></i></div>
                        </div>
                    </div>
                </div>
                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-primary bg-opacity-10 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <h2 class="mb-0 fw-bold">{{\App\Models\Billings::whereMonth('created_at',\Carbon\Carbon::now()->month)->where('year',request('year',date('Y')))->where('currency','CAD')->sum('amount')}}</h2>
                                <span class="mb-0 h5 fw-light">دولار كندي</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-primary text-white mb-0"><i class="fas fa-euro-sign"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-success bg-opacity-10 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <div class="d-flex">
                                    <h2 class=" mb-0 fw-bold">{{\App\Models\User::where('user_type',\App\Models\User::USER_TYPE['teacher'])->count()}}</h2>
                                </div>
                                <span class="mb-0 h5 fw-light">عدد المعلمين</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-success text-white mb-0"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Counter boxes END -->
            <!-- Counter item -->
            <div class="col-md-6 col-xxl-3">
                <div class="card card-body bg-success bg-opacity-10 p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <!-- Digit -->
                        <div>
                            <div class="d-flex">
                                <h2 class=" mb-0 fw-bold">{{\App\Models\User::where('user_type',\App\Models\User::USER_TYPE['student'])->count()}}</h2>
                            </div>
                            <span class="mb-0 h5 fw-light">عدد الطلاب</span>
                        </div>
                        <!-- Icon -->
                        <div class="icon-lg rounded-circle bg-success text-white mb-0"><i class="fas fa-graduation-cap"></i></div>
                    </div>
                </div>
            </div>
        @elseif(auth()->user()->user_type == \App\Models\User::USER_TYPE['teacher'])
            <!-- Counter boxes START -->
            <div class="row g-4 mb-4" style="direction: rtl">
                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-warning bg-opacity-15 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <h2 class="mb-0 fw-bold">{{\App\Models\Lessons::where('teacher_id',auth()->user()->id)->whereMonth('created_at', \Carbon\Carbon::now()->month)->whereYear('created_at', request('year', date('Y')))->sum('lesson_duration')}}</h2>                                <span class="mb-0 h5 fw-light">اجمالي ساعات هذا الشهر</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-warning text-white mb-0"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Counter boxes END -->
            <div class="row g-4 mb-4" style="direction: rtl">
                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-warning bg-opacity-15 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <h2 class="mb-0 fw-bold">{{\App\Models\TeacherStudents::where('teacher_id',auth()->user()->id)->count()}}</h2>
                                <span class="mb-0 h5 fw-light">عدد طلابك</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-warning text-white mb-0"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row g-4 mb-4" style="direction: rtl">
                <!-- Counter item -->
                <div class="col-md-6 col-xxl-3">
                    <div class="card card-body bg-warning bg-opacity-15 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Digit -->
                            <div>
                                <h2 class="mb-0 fw-bold">{{\App\Models\Courses::where('teacher_id',auth()->user()->id)->count()}}</h2>
                                <span class="mb-0 h5 fw-light">عدد دوراتك</span>
                            </div>
                            <!-- Icon -->
                            <div class="icon-lg rounded-circle bg-warning text-white mb-0"><i class="fas fa-laptop"></i></div>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>
    <!-- Counter boxes END -->



@endsection
