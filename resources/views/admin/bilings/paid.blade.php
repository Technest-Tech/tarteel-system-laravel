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
                <h1 class="h3 mb-2 mb-sm-0">الفواتير المدفوعة - {{ $year }}</h1>
                <a href="{{ route('billings.year') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right"></i> العودة لاختيار السنة
                </a>
            </div>
        </div><br>
        <!-- Months -->
        <div class="row">
            <div class="col-12 text-center">
                <a href="{{route('paid.billings',[$year,1])}}" @if($month == '1') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>يناير</a>
                <a href="{{route('paid.billings',[$year,2])}}" @if($month == '2') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>فبراير</a>
                <a href="{{route('paid.billings',[$year,3])}}" @if($month == '3') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>مارس</a>
                <a href="{{route('paid.billings',[$year,4])}}" @if($month == '4') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>أبريل</a>
                <a href="{{route('paid.billings',[$year,5])}}" @if($month == '5') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>مايو</a>
                <a href="{{route('paid.billings',[$year,6])}}" @if($month == '6') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>يونيو</a>
                <a href="{{route('paid.billings',[$year,7])}}" @if($month == '7') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>يوليو</a>
                <a href="{{route('paid.billings',[$year,8])}}" @if($month == '8') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>أغسطس</a>
                <a href="{{route('paid.billings',[$year,9])}}" @if($month == '9') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>سبتمبر</a>
                <a href="{{route('paid.billings',[$year,10])}}" @if($month == '10') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>أكتوبر</a>
                <a href="{{route('paid.billings',[$year,11])}}" @if($month == '11') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>نوفمبر</a>
                <a href="{{route('paid.billings',[$year,12])}}" @if($month == '12') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>ديسمبر</a>
            </div>
        </div>
        <hr>
        <!-- Card START -->
        <div class="card bg-transparent border">

            <!-- Card header START -->
            <div class="card-header bg-light border-bottom">
                <!-- Search and select START -->
                <div class="row g-3 align-items-center justify-content-between">


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
            <div class="row justify-content-center">
                @php
                    $currentMonthBillings_notpaid = \App\Models\Billings::where('is_paid',0)
                                                                 ->whereMonth('created_at', $month)
                                                                 ->where('year', $year)
                                                                 ->distinct('student_id')->count('student_id');
                    $uniqueStudentBillings_paid = \App\Models\Billings::where('is_paid',1)
                                                                 ->whereMonth('created_at', $month)
                                                                 ->where('year', $year)
                                                                 ->distinct('student_id')->count('student_id');
                @endphp
                <div class="col-5">
                    <div class="card fancy-card text-center" style="background-color: rgba(168,255,153,0.65)">
                        <p> المدفوعة</p>
                        <h4>{{$uniqueStudentBillings_paid}}</h4>
                    </div>
                </div>
                <div class="col-5">
                    <a href="{{route('billings.index',[$year,$month])}}">
                        <div class="card fancy-card text-center" >
                            <p>الغير مدفوعة</p>
                            <h4>{{$currentMonthBillings_notpaid}}</h4>

                        </div>
                    </a>
                </div>
            </div>

            <div>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">تم الدفع</th>
                        <th scope="col" class="text-center">الطالب</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($billings as $billing)
                        @php
                          $student = \App\Models\User::find($billing->student_id);
                        @endphp
                        <tr>
                            <td class="text-center">
                                    <img src="{{asset('checked.png')}}" style="width: 40px">
                            </td>
                            <td class="text-center">{{ $billing->student->user_name }}<br>{{ $billing->total_amount .' '. $billing->student->currency}} </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Card END -->
    </div>
    <!-- Page main content END -->


@endsection
