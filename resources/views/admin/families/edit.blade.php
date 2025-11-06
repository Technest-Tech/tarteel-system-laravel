@extends('layouts.index')

@section('content')
    <!-- Page main content START -->
    <div class="page-content-wrapper border">

        <!-- Title -->
        <div class="row">
            <div class="col-12 text-end">
                <h1 class="h3 mb-2 mb-sm-0">تعديل عائلة</h1>
                <a href="{{ route('families.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div><br>

        <!-- Card START -->
        <div class="card bg-transparent border">
            <div class="card-body">
                <form action="{{route('families.update', $family->id)}}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="familyName" class="form-label">اسم العائلة</label>
                        <input type="text" class="form-control" id="familyName" name="family_name" value="{{$family->family_name}}" required>
                    </div>
                    <div class="mb-3">
                        <label for="whatsappNumber" class="form-label">رقم الواتساب (اختياري)</label>
                        <input type="text" class="form-control" id="whatsappNumber" name="whatsapp_number" value="{{$family->whatsapp_number}}">
                    </div>
                    <button type="submit" class="btn btn-primary">تحديث</button>
                </form>
            </div>
        </div>
        <!-- Card END -->
    </div>
    <!-- Page main content END -->
@endsection

