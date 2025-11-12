@extends('layouts.index')

@section('content')
<div class="page-content-wrapper border">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-end">جداول الطلاب</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#timetableModal" id="openTimetableModalBtn">
                <i class="fas fa-plus me-2"></i>إضافة جدول
            </button>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm filters-card">
                <div class="card-body">
                    <form id="timetableFilters" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="filter_student" class="form-label small">الطالب</label>
                            <select class="form-select form-select-sm js-searchable" id="filter_student">
                                <option value="">جميع الطلاب</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->user_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_teacher" class="form-label small">المعلم</label>
                            <select class="form-select form-select-sm js-searchable" id="filter_teacher">
                                <option value="">جميع المعلمين</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->user_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_period_from" class="form-label small">من تاريخ</label>
                            <input type="date" class="form-control form-control-sm" id="filter_period_from">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_period_to" class="form-label small">إلى تاريخ</label>
                            <input type="date" class="form-control form-control-sm" id="filter_period_to">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm w-100" id="applyTimetableFilters">
                                <i class="fas fa-filter me-1"></i>تطبيق
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetTimetableFilters">
                                إعادة تعيين
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm timetable-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle" id="timetablesTable">
                            <thead>
                                <tr>
                                    <th class="text-nowrap">الطالب</th>
                                    <th class="text-nowrap">المعلم</th>
                                    <th class="text-nowrap">اسم الحصة</th>
                                    <th class="text-nowrap">الأيام</th>
                                    <th class="text-nowrap">وقت البداية</th>
                                    <th class="text-nowrap">وقت النهاية</th>
                                    <th class="text-nowrap">تاريخ البدء</th>
                                    <th class="text-nowrap">تاريخ الانتهاء</th>
                                    <th class="text-nowrap text-center">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted" id="timetablesEmptyState">لا توجد بيانات متاحة</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="timetablePagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="timetableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="timetableModalTitle">إضافة جدول جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="timetableForm">
                    @csrf
                    <input type="hidden" id="timetable_series_id" name="series_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="timetable_student_id" class="form-label">اختر الطالب</label>
                            <select class="form-select js-searchable" id="timetable_student_id" name="student_id" required>
                                <option value="" disabled selected>اختر الطالب</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->user_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="timetable_teacher_id" class="form-label">اختر المعلم</label>
                            <select class="form-select js-searchable" id="timetable_teacher_id" name="teacher_id" required>
                                <option value="" disabled selected>اختر المعلم</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" data-color="{{ $teacher->color ?? '#3b82f6' }}">{{ $teacher->user_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="timetable_timezone" class="form-label">المنطقة الزمنية</label>
                            <select class="form-select js-searchable" id="timetable_timezone" name="timezone">
                                @foreach(\App\Services\TimezoneService::getTimezoneOptions() as $tzValue => $tzLabel)
                                    <option value="{{ $tzValue }}" {{ $tzValue === 'Africa/Cairo' ? 'selected' : '' }}>{{ $tzLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="timetable_start_time" class="form-label">وقت البداية</label>
                            <input type="time" class="form-control" id="timetable_start_time" name="start_time" required>
                        </div>
                        <div class="col-md-3">
                            <label for="timetable_end_time" class="form-label">وقت النهاية</label>
                            <input type="time" class="form-control" id="timetable_end_time" name="end_time" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="timetable_lesson_name" class="form-label">اسم الحصة / الدورة</label>
                            <input type="text" class="form-control" id="timetable_lesson_name" name="lesson_name" placeholder="مثال: دورة تجويد" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label for="timetable_color" class="form-label">لون البطاقات</label>
                            <input type="color" class="form-control form-control-color" id="timetable_color" name="color" value="#3b82f6" title="اختر لون الحصص">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label d-block">الأيام المتكررة</label>
                        <div class="weekday-selector" id="weekdaySelector">
                            @php
                                $weekdayLabels = [
                                    0 => 'الأحد',
                                    1 => 'الإثنين',
                                    2 => 'الثلاثاء',
                                    3 => 'الأربعاء',
                                    4 => 'الخميس',
                                    5 => 'الجمعة',
                                    6 => 'السبت',
                                ];
                            @endphp
                            @foreach($weekdayLabels as $value => $label)
                                <label class="weekday-option form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="days[]" value="{{ $value }}">
                                    <span class="form-check-label">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="invalid-feedback d-block" id="weekdayError" style="display:none;">
                            يرجى اختيار يوم واحد على الأقل
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="timetable_start_date" class="form-label">تاريخ البداية</label>
                            <input type="date" class="form-control" id="timetable_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="timetable_end_date" class="form-label">تاريخ النهاية</label>
                            <input type="date" class="form-control" id="timetable_end_date" name="end_date" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="timetable_notification_minutes" class="form-label">دقائق التنبيه قبل الحصة</label>
                            <input type="number" class="form-control" id="timetable_notification_minutes" name="notification_minutes" value="30" min="0" max="1440">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveTimetableBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    حفظ الجدول
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSeriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">حذف الجدول</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">هل أنت متأكد من حذف هذا الجدول وجميع الحصص المرتبطة به؟</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSeriesBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    حذف الجدول
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/timetables.css') }}">
@endpush

@push('scripts')
<script>
    window.timetableRoutes = {
        list: '{{ route("admin.timetables.list") }}',
        store: '{{ route("admin.timetables.store") }}',
        show: '{{ route("admin.timetables.show", ':id') }}',
        update: '{{ route("admin.timetables.update", ':id') }}',
        destroy: '{{ route("admin.timetables.destroy", ':id') }}',
    };
    window.timetableCsrfToken = '{{ csrf_token() }}';
</script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js" defer></script>
<script src="{{ asset('js/admin-timetables.js') }}" defer></script>
@endpush
