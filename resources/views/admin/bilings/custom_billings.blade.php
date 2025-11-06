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
            <div class="col-12 text-center">
                <h1 class="h3 mb-2 mb-sm-0">الفواتيير اليدوية</h1>
            </div>
        </div><br>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- Months -->
        <!-- Card START -->
        <div class="card bg-transparent border">
            <!-- Card header START -->
            <div class="card-header bg-light border-bottom">
                <!-- Search and select START -->
                <div class="row g-3 align-items-center justify-content-between">
                    <div class="col-12 text-center">
                        <h1 class="h5 mb-2 mb-sm-0"> انشاء فاتورة يدوية</h1>
                    </div>
                </div>
                <!-- Search and select END -->
            </div>
            <div class="card-body">
                <form action="{{route('custom_billings.store')}}" method="post">
                    @csrf
                    <div class="row justify-content-end">
                        <div class="col-md-6 offset-md-3">
                            <div class="mb-3 text-center">
                                <label for="student_id" class="form-label">الطالب</label>
                                <select class="form-select" name="student_id" id="student_id" required multiple>
                                    <option value="">اختر الطالب</option>
                                    @foreach($students as $student)
                                        <option value="{{$student->id}}">{{$student->user_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 text-center">
                                <label for="total" class="form-label">اجمالي الفاتورة</label>
                                <input type="number" class="form-control" name="total" id="total" required>
                            </div>
                            <div class="mb-3 text-center">
                                <label for="student_id" class="form-label">العملة</label>
                                <select class="form-select" name="currency" id="currency" required>
                                    <option selected disabled>اختر العملة</option>
                                    <option value="CAD">Canadian Dollar (CAD)</option>
                                    <option value="EUR">Euro (EUR)</option>
                                    <option value="NZD">New Zealand Dollar (NZD)</option>
                                    <option value="GBP">British Pound (GBP)</option>
                                    <option value="USD">United States Dollar (USD)</option>
                                    <option value="AED">United Arab Emirates Dirham (AED)</option>
                                    <option value="SAR">Saudi Riyal (SAR)</option>
                                    <option value="MAD">Moroccan Dirham (MAD)</option>
                                    <option value="EGP">Egyptian Pound (EGP)</option>
                                </select>
                            </div>
                            <div class="mb-3 text-center">
                               <button type="submit" class="btn btn-primary">انشاء الفاتورة</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Card END -->
    </div>
    <!-- Page main content END -->


@endsection
