@extends('layouts.index')

@section('content')
<div class="page-content-wrapper border">
    <!-- Title and Add Button -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-2 mb-sm-0 text-end">تقويم الحصص</h1>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters and Export Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
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
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary btn-sm w-100" id="applyFiltersBtn">
                                <i class="fas fa-filter me-1"></i>تطبيق الفلاتر
                            </button>
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group w-100" role="group">
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

    <!-- Bulk Hour Adjustment Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>تعديل المواعيد بسبب تغيير التوقيت (التوقيت الصيفي/الشتوي)</h6>
                    <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#bulkHourAdjustCollapse" aria-expanded="false" aria-controls="bulkHourAdjustCollapse">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div id="bulkHourAdjustCollapse" class="collapse">
                    <div class="card-body">
                    <form id="bulkHourAdjustForm">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="adjust_timezone_select" class="form-label">المنطقة الزمنية</label>
                                <select class="form-select" id="adjust_timezone_select" name="timezone" required>
                                    <option value="">-- اختر المنطقة الزمنية --</option>
                                    @foreach(\App\Services\TimezoneService::getTimezoneOptions() as $tzValue => $tzLabel)
                                        <option value="{{ $tzValue }}">{{ $tzLabel }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">الطلاب الذين لديهم هذه المنطقة الزمنية</small>
                            </div>
                            <div class="col-md-3">
                                <label for="adjust_hours" class="form-label">عدد الساعات</label>
                                <select class="form-select" id="adjust_hours" name="hours" required>
                                    <option value="1">+1 ساعة (زيادة)</option>
                                    <option value="-1">-1 ساعة (نقصان)</option>
                                    <option value="2">+2 ساعة (زيادة)</option>
                                    <option value="-2">-2 ساعة (نقصان)</option>
                                </select>
                                <small class="form-text text-muted">مثال: إذا كان الظهر 12:40 وأصبح 11:40، اختر -1</small>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-info w-100" id="loadStudentsForAdjustBtn">
                                    <i class="fas fa-search me-1"></i>عرض الطلاب
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-warning w-100" id="bulkAdjustBtn" style="display: none;">
                                    <i class="fas fa-save me-1"></i>تحديث جميع المواعيد
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3" id="studentsAdjustListContainer" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>سيتم تحديث مواعيد:</strong> <span id="studentsCount">0</span> طالب
                                    <br>
                                    <strong>التعديل:</strong> <span id="adjustmentInfo"></span>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="row">
        <div class="col-lg-3 mb-3 mb-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>قائمة المعلمين</h6>
                    <button class="btn btn-sm btn-outline-secondary" id="resetTeacherFilter">
                        إعادة تعيين
                    </button>
                </div>
                <div class="card-body p-0 teacher-list-container">
                    <div class="list-group list-group-flush teacher-list-scroll" id="calendarTeacherList">
                        <button type="button" class="list-group-item list-group-item-action active" data-teacher-id="">
                            جميع المعلمين
                        </button>
                        @foreach($teachers as $teacher)
                            <button type="button" class="list-group-item list-group-item-action" data-teacher-id="{{ $teacher->id }}">
                                {{ $teacher->user_name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Lesson Modal -->
<div class="modal fade" id="createLessonModal" tabindex="-1" aria-labelledby="createLessonModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 90%; width: 90%;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createLessonModalLabel">إضافة حصة متكررة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="lessonForm">
                    @csrf
                    <!-- Schedule Entry Rows - 7 Fixed Days -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary"><i class="fas fa-list me-2"></i>جدول الحصص (يمكنك ملء أيام معينة وترك البقية فارغة)</h6>
                        <div id="scheduleRowsContainer">
                            @php
                                $days = [
                                    ['value' => 0, 'name' => 'الأحد'],
                                    ['value' => 1, 'name' => 'الإثنين'],
                                    ['value' => 2, 'name' => 'الثلاثاء'],
                                    ['value' => 3, 'name' => 'الأربعاء'],
                                    ['value' => 4, 'name' => 'الخميس'],
                                    ['value' => 5, 'name' => 'الجمعة'],
                                    ['value' => 6, 'name' => 'السبت'],
                                ];
                            @endphp
                            @foreach($days as $day)
                                <div class="schedule-row mb-3 p-3 border rounded" data-day="{{ $day['value'] }}">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-1">
                                            <label class="form-label small fw-bold text-primary">{{ $day['name'] }}</label>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small">الطالب</label>
                                            <select class="form-select form-select-sm schedule-student">
                                                <option value="">اختر الطالب</option>
                                                @foreach($students as $student)
                                                    <option value="{{ $student->id }}">{{ $student->user_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small">المعلم</label>
                                            <select class="form-select form-select-sm schedule-teacher">
                                                <option value="">اختر المعلم</option>
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}">{{ $teacher->user_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label small">وقت البداية</label>
                                            <input type="time" class="form-control form-control-sm schedule-start-time">
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label small">وقت النهاية</label>
                                            <input type="time" class="form-control form-control-sm schedule-end-time">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small">اسم الحصة</label>
                                            <input type="text" class="form-control form-control-sm schedule-lesson-name" placeholder="اسم الحصة (اختياري)">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small">دقائق التنبيه</label>
                                            <input type="number" class="form-control form-control-sm schedule-notification-minutes" placeholder="30" value="30" min="0">
                                            <small class="form-text text-muted" style="font-size: 0.7rem;">دقائق قبل الحصة</small>
                                        </div>
                                        <input type="hidden" class="schedule-day" value="{{ $day['value'] }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Date Range Section -->
                    <div class="mb-3 border-top pt-3">
                        <h6 class="mb-3 text-primary"><i class="fas fa-calendar me-2"></i>فترة الجدول</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">تاريخ البداية</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">تاريخ النهاية</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>

                    <!-- Timezone Section -->
                    <div class="mb-3 border-top pt-3">
                        <h6 class="mb-3 text-primary"><i class="fas fa-globe me-2"></i>المنطقة الزمنية</h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="schedule_timezone" class="form-label">اختر المنطقة الزمنية</label>
                                <select class="form-select" id="schedule_timezone" name="timezone">
                                    @foreach(\App\Services\TimezoneService::getTimezoneOptions() as $tzValue => $tzLabel)
                                        <option value="{{ $tzValue }}" {{ $tzValue == 'Africa/Cairo' ? 'selected' : '' }}>{{ $tzLabel }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">سيتم تطبيق المنطقة الزمنية على جميع الحصص في هذا الجدول</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="submitBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    إضافة الحصص
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lesson Detail Modal -->
<div class="modal fade" id="lessonModal" tabindex="-1" aria-labelledby="lessonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lessonModalLabel">تفاصيل الحصة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="lessonModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-primary d-none" id="editLessonBtn">تعديل</button>
                <button type="button" class="btn btn-danger d-none" id="deleteLessonBtn">حذف</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Lesson Modal -->
<div class="modal fade" id="editLessonModal" tabindex="-1" aria-labelledby="editLessonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLessonModalLabel">تعديل الحصة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLessonForm">
                    @csrf
                    <input type="hidden" id="edit_lesson_id" name="lesson_id">
                    
                    <div class="mb-3">
                        <label for="edit_student_id" class="form-label">الطالب</label>
                        <select class="form-select js-searchable" id="edit_student_id" name="student_id" required>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->user_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_teacher_id" class="form-label">المعلم</label>
                        <select class="form-select js-searchable" id="edit_teacher_id" name="teacher_id" required>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_event_date_display" class="form-label">تاريخ الحصة</label>
                        <input type="date" class="form-control" id="edit_event_date_display" name="event_date_display" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_start_time" class="form-label">وقت البداية</label>
                        <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_end_time" class="form-label">وقت النهاية</label>
                        <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_notification_minutes" class="form-label">دقائق التنبيه</label>
                        <input type="number" class="form-control" id="edit_notification_minutes" name="notification_minutes" placeholder="30" value="30" min="0">
                        <small class="form-text text-muted">عدد الدقائق قبل الحصة لإرسال البريد الإلكتروني للمعلم والدعم</small>
                    </div>
                    
                    <input type="hidden" id="edit_timetable_id" name="timetable_id">
                    <input type="hidden" id="edit_event_date" name="event_date">
                    <input type="hidden" id="edit_original_event_date" name="original_event_date">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveEditBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    حفظ
                </button>
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

<!-- Create Single Event Modal -->
<div class="modal fade" id="singleEventModal" tabindex="-1" aria-labelledby="singleEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="singleEventModalLabel">إضافة حصة في هذا اليوم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="singleEventForm">
                    @csrf
                    <input type="hidden" id="single_event_date" name="event_date">
                    <div class="mb-3">
                        <label for="single_event_student" class="form-label">الطالب</label>
                        <select class="form-select js-searchable" id="single_event_student" name="student_id" required>
                            <option value="" disabled selected>اختر الطالب</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->user_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="single_event_teacher" class="form-label">المعلم</label>
                        <select class="form-select js-searchable" id="single_event_teacher" name="teacher_id" required>
                            <option value="" disabled selected>اختر المعلم</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" data-color="{{ $teacher->color ?? '#3b82f6' }}">{{ $teacher->user_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="single_event_start_time" class="form-label">وقت البداية</label>
                            <input type="time" class="form-control" id="single_event_start_time" name="start_time" required>
                        </div>
                        <div class="col-md-6">
                            <label for="single_event_end_time" class="form-label">وقت النهاية</label>
                            <input type="time" class="form-control" id="single_event_end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="single_event_lesson_name" class="form-label">اسم الحصة</label>
                        <input type="text" class="form-control" id="single_event_lesson_name" name="lesson_name" placeholder="مثال: حصة مراجعة" maxlength="255">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="saveSingleEventBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    حفظ الحصة
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Event Options Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteEventModalLabel">حذف الحصة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="delete_event_scope" class="form-label">اختر نطاق الحذف</label>
                    <select class="form-select" id="delete_event_scope">
                        <option value="single">هذه الحصة فقط</option>
                        <option value="future">هذه الحصة وما يليها</option>
                        <option value="series">جميع حصص هذا الجدول</option>
                    </select>
                </div>
                <p class="mb-0 text-muted" id="delete_event_description"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEventBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    تأكيد الحذف
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="{{ asset('assets/css/calendar-custom.css') }}">
@endpush

@push('scripts')
<script>
    // Define routes and CSRF token for JavaScript
    window.calendarRoutes = {
        events: '{{ route("admin.calendar.events") }}',
        store: '{{ route("admin.calendar.store") }}',
        show: '{{ route("admin.calendar.show", ":id") }}',
        update: '{{ route("admin.calendar.update", ":id") }}',
        deleteRedirect: '{{ route("admin.calendar.delete", ":id") }}',
        destroy: '{{ route("admin.calendar.destroy") }}',
        export: '{{ route("admin.calendar.export") }}'
    };
    window.csrfToken = '{{ csrf_token() }}';
</script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js' onload="console.log('FullCalendar loaded');"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js" defer></script>
<script>
    console.log('About to load calendar-admin.js...');
    console.log('Script URL:', '{{ asset('js/calendar-admin.js') }}');
</script>
<script src="{{ asset('js/calendar-admin.js') }}" defer onload="console.log('calendar-admin.js script tag loaded');" onerror="console.error('Failed to load calendar-admin.js');"></script>
@endpush
@endsection
