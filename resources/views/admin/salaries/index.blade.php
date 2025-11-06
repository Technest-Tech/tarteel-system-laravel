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
                <h1 class="h3 mb-2 mb-sm-0">المرتبات - {{ $year }}</h1>
            </div>
        </div><br>
        <!-- Months -->
        <div class="row">
            <div class="col-12 text-center">
                <a href="{{route('salaries.index',[$year,1])}}" @if($month == '1') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>يناير</a>
                <a href="{{route('salaries.index',[$year,2])}}" @if($month == '2') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>فبراير</a>
                <a href="{{route('salaries.index',[$year,3])}}" @if($month == '3') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>مارس</a>
                <a href="{{route('salaries.index',[$year,4])}}" @if($month == '4') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>أبريل</a>
                <a href="{{route('salaries.index',[$year,5])}}" @if($month == '5') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>مايو</a>
                <a href="{{route('salaries.index',[$year,6])}}" @if($month == '6') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>يونيو</a>
                <a href="{{route('salaries.index',[$year,7])}}" @if($month == '7') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>يوليو</a>
                <a href="{{route('salaries.index',[$year,8])}}" @if($month == '8') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>أغسطس</a>
                <a href="{{route('salaries.index',[$year,9])}}" @if($month == '9') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>سبتمبر</a>
                <a href="{{route('salaries.index',[$year,10])}}" @if($month == '10') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>أكتوبر</a>
                <a href="{{route('salaries.index',[$year,11])}}" @if($month == '11') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>نوفمبر</a>
                <a href="{{route('salaries.index',[$year,12])}}" @if($month == '12') class="btn btn-secondary disabled" @else class="btn btn-primary" @endif>ديسمبر</a>
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

            </div>
            <form action="{{route('salaries.amount',['year'=>$year,'month'=>$month])}}">
                @csrf
                <div class="row m-3" style="direction: rtl">
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">سعر الساعة للعربي (إذا لم يكن محدد في بيانات المعلم)</label>
                            <input type="number" step="0.01" name="amount_arabic" class="form-control" placeholder=" سعر الساعة للعربي" value="{{$amountArabic ?? ''}}">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label class="form-label">سعر الساعة للإنجليزي (إذا لم يكن محدد في بيانات المعلم)</label>
                            <input type="number" step="0.01" name="amount_english" class="form-control" placeholder=" سعر الساعة للإنجليزي" value="{{$amountEnglish ?? ''}}">
                        </div>
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary">حساب المرتب</button>
                    </div>
                </div>
            </form>

            <div>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col" class="text-center">الراتب العربي</th>
                        <th scope="col" class="text-center">ساعات عربي</th>
                        <th scope="col" class="text-center">الراتب الإنجليزي</th>
                        <th scope="col" class="text-center">ساعات إنجليزي</th>
                        <th scope="col" class="text-center">المعلم</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($salaries as $salary)
                        @php
                            $teacher = \App\Models\User::find($salary->teacher_id);
                            $arabicSalaryRate = $teacher && $teacher->salary_arabic ? $teacher->salary_arabic : ($amountArabic ?? null);
                            $englishSalaryRate = $teacher && $teacher->salary_english ? $teacher->salary_english : ($amountEnglish ?? null);
                        @endphp
                        <tr>
                            <td class="text-center">
                                @if($arabicSalaryRate && $salary->arabic_hours > 0)
                                    {{ number_format($arabicSalaryRate * $salary->arabic_hours, 2) }} جنيه
                                @else
                                    {{ $salary->arabic_hours > 0 ? 'حدد سعر الساعة' : '-' }}
                                @endif
                            </td>
                            <td class="text-center">{{ $salary->arabic_hours }} ساعة</td>
                            <td class="text-center">
                                @if($englishSalaryRate && $salary->english_hours > 0)
                                    {{ number_format($englishSalaryRate * $salary->english_hours, 2) }} جنيه
                                @else
                                    {{ $salary->english_hours > 0 ? 'حدد سعر الساعة' : '-' }}
                                @endif
                            </td>
                            <td class="text-center">{{ $salary->english_hours }} ساعة</td>
                            <td class="text-center">{{$teacher->user_name}}</td>
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
