@extends('layouts.index')

@section('content')
    <style>
        .fancy-card {
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            padding: 16px;
            margin: 16px 0;
            background-color: #fff;
            transition: transform 0.3s ease;
        }
        .fancy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.3), 0 12px 30px 0 rgba(0, 0, 0, 0.25);
        }
        .year-card {
            text-align: center;
            cursor: pointer;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin: 15px;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .year-card:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: scale(1.05);
        }
    </style>
    
    <!-- Page main content START -->
    <div class="page-content-wrapper border">
        <!-- Title -->
        <div class="row">
            <div class="col-12 text-end">
                <h1 class="h3 mb-2 mb-sm-0">اختر السنة لعرض الفواتير</h1>
            </div>
        </div><br>
        
        <!-- Years Selection -->
        <div class="row justify-content-center">
            @if($years->count() > 0)
                @foreach($years as $year)
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('billings.index', ['year' => $year, 'month' => 1]) }}" class="text-decoration-none">
                            <div class="year-card">
                                <h2 class="mb-0">{{ $year }}</h2>
                                <p class="mb-0 mt-2">عرض فواتير السنة</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <h4>لا توجد سنوات متاحة</h4>
                        <p>لم يتم العثور على أي بيانات فواتير في النظام</p>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Add Current Year if not exists -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-6 text-center">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">إضافة سنة جديدة</h5>
                        <p class="card-text">إذا كنت تريد إضافة فواتير لسنة {{ date('Y') }}</p>
                        <a href="{{ route('billings.index', ['year' => date('Y'), 'month' => 1]) }}" class="btn btn-primary">
                            إضافة فواتير {{ date('Y') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page main content END -->
@endsection 