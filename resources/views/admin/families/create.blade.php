@extends('layouts.index')

@section('content')
    <!-- Page main content START -->
    <div class="page-content-wrapper border">

        <!-- Title -->
        <div class="row">
            <div class="col-12 text-end">
                <h1 class="h3 mb-2 mb-sm-0">اضافة عائلة</h1>
                <a href="{{ route('families.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div><br>

        <!-- Card START -->
        <div class="card bg-transparent border">
            <div class="card-body">
                <form action="{{route('families.store')}}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="familyName" class="form-label">اسم العائلة</label>
                        <input type="text" class="form-control" id="familyName" name="family_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="whatsappNumber" class="form-label">رقم الواتساب (اختياري)</label>
                        <input type="text" class="form-control" id="whatsappNumber" name="whatsapp_number">
                    </div>
                    <div class="mb-3">
                        <label for="students" class="form-label">الطلاب (يمكنك إضافة المزيد لاحقاً)</label>
                        <select multiple class="form-select" id="students" name="students[]">
                            @foreach($students as $student)
                                <option value="{{$student->id}}">{{$student->user_name}}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">اضغط Ctrl/Cmd لاختيار عدة طلاب</small>
                    </div>
                    <button type="submit" class="btn btn-primary">اضافة</button>
                </form>
            </div>
        </div>
        <!-- Card END -->
    </div>
    <!-- Page main content END -->
@endsection

