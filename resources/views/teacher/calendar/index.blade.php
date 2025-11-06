@extends('layouts.index')

@section('content')
<!-- Page main content START -->
<div class="page-content-wrapper border">

    <!-- Title -->
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-2 mb-sm-0">تقويم الحصص</h1>
        </div>
    </div><br>

    <!-- Export Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-12 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success btn-sm" id="exportBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-pdf me-1"></i>تصدير PDF
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportBtn">
                                    <li><a class="dropdown-item" href="#" id="exportTodayBtn">تصدير اليوم</a></li>
                                    <li><a class="dropdown-item" href="#" id="exportWeekBtn">تصدير الأسبوع</a></li>
                                    <li><a class="dropdown-item" href="#" id="exportMonthBtn">تصدير الشهر</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" id="exportCustomBtn" data-bs-toggle="modal" data-bs-target="#exportRangeModal">تصدير نطاق مخصص</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card bg-transparent border">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

</div>
<!-- Page main content END -->

<!-- Lesson Detail Modal (Read-only) -->
<div class="modal fade" id="lessonDetailModal" tabindex="-1" aria-labelledby="lessonDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lessonDetailModalLabel">تفاصيل الحصة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="lessonDetailContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay d-none" id="loadingOverlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">جاري التحميل...</span>
    </div>
</div>

<!-- Export Date Range Modal -->
<div class="modal fade" id="exportRangeModal" tabindex="-1" aria-labelledby="exportRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportRangeModalLabel">تصدير نطاق مخصص</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportRangeForm">
                    <div class="mb-3">
                        <label for="export_start_date" class="form-label">تاريخ البداية</label>
                        <input type="date" class="form-control" id="export_start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="export_end_date" class="form-label">تاريخ النهاية</label>
                        <input type="date" class="form-control" id="export_end_date" name="end_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="exportRangeSubmitBtn">
                    <i class="fas fa-file-pdf me-1"></i>تصدير
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<link rel="stylesheet" href="{{ asset('assets/css/calendar-custom.css') }}">
@endpush

@push('scripts')
<script>
    // Define routes and CSRF token for JavaScript
    window.calendarRoutes = {
        events: '{{ route("teacher.calendar.events") }}',
        show: '{{ route("teacher.calendar.show", ":id") }}',
        export: '{{ route("teacher.calendar.export") }}'
    };
    window.csrfToken = '{{ csrf_token() }}';
</script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js' onload="console.log('FullCalendar loaded');"></script>
<script src="{{ asset('js/calendar-teacher.js') }}" defer></script>
@endpush
@endsection

