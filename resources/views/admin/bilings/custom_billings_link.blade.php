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
        <!-- Months -->
        <!-- Card START -->
        <div class="card bg-transparent border">
            <!-- Card header START -->
            <div class="card-header bg-light border-bottom">
                <!-- Search and select START -->
                <div class="row g-3 align-items-center justify-content-center">
                    <div class="col-6 text-center">
                        <h1 class="h5 mb-2 mb-sm-0">فاتورتك جاهزة , قم بنسخها من الاسفل 👇 </h1>
                        <div class="input-group" style="margin-top: 30px">
                            <input type="text" value="{{$url}}" class="form-control" id="copyInput">
                            <div class="input-group-append">
                                <button class="btn btn-outline-success" type="button" id="copyButton">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <a href="{{route('custom_billings.index')}}" class="btn btn-info" style="margin-top: 30px">انشاء فاتورة اخري</a>

                    </div>

                    <script>
                        document.getElementById("copyButton").addEventListener("click", function() {
                            var copyText = document.getElementById("copyInput");
                            copyText.select();
                            document.execCommand("copy");
                        });
                    </script>
                </div>
                <!-- Search and select END -->
            </div>
            <div class="card-body">
            </div>
        </div>
        <!-- Card END -->
    </div>
    <!-- Page main content END -->


@endsection
