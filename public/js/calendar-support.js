// Wait for FullCalendar to be loaded
(function() {
    'use strict';
    console.log('=== calendar-support.js file loaded ===');
    console.log('Current time:', new Date().toISOString());
    console.log('Window location:', window.location.href);
})();

function initCalendarSupport() {
    console.log('initCalendarSupport function called');
    
    if (typeof FullCalendar === 'undefined') {
        console.log('FullCalendar not loaded yet, retrying...');
        setTimeout(initCalendarSupport, 100);
        return;
    }

    let calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.log('Calendar element not found, retrying...');
        setTimeout(initCalendarSupport, 100);
        return;
    }
    
    console.log('Calendar initialization starting...');

    let calendar;
    
    // Filter state
    let currentFilters = {
        student_id: '',
        teacher_id: ''
    };
    
    // Helper functions
    function showLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('d-none');
        }
    }

    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.add('d-none');
        }
    }

    function formatTimeTo12Hour(timeString) {
        if (!timeString) return '';
        const [hours, minutes] = timeString.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'م' : 'ص';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }

    // Detect mobile device
    const isMobile = window.innerWidth <= 768;
    
    // Initialize FullCalendar
    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'ar',
        initialView: isMobile ? 'dayGridMonth' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: isMobile ? 'dayGridMonth' : 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        // Responsive configuration
        views: {
            dayGridMonth: {
                dayMaxEvents: isMobile ? 3 : 4,
                moreLinkClick: 'popover'
            },
            timeGridWeek: {
                slotMinTime: '06:00:00',
                slotMaxTime: '24:00:00',
                slotDuration: isMobile ? '01:00:00' : '00:30:00'
            },
            timeGridDay: {
                slotMinTime: '06:00:00',
                slotMaxTime: '24:00:00',
                slotDuration: isMobile ? '01:00:00' : '00:30:00'
            }
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            showLoading();
            // Build query string with filters
            let queryParams = 'start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr;
            if (currentFilters.student_id) {
                queryParams += '&student_id=' + currentFilters.student_id;
            }
            if (currentFilters.teacher_id) {
                queryParams += '&teacher_id=' + currentFilters.teacher_id;
            }
            
            fetch(window.calendarRoutes.events + '?' + queryParams, {
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    hideLoading();
                    // Ensure data is an array
                    if (Array.isArray(data)) {
                        successCallback(data);
                    } else {
                        console.error('Invalid response format, expected array:', data);
                        successCallback([]);
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error fetching events:', error);
                    // Pass empty array to successCallback instead of failureCallback
                    // This prevents FullCalendar from breaking
                    successCallback([]);
                });
        },
        eventClick: function(info) {
            loadLessonDetails(info.event.id);
        },
        eventDisplay: 'block',
        displayEventTime: false, // Disable automatic time display since we're using custom eventContent
        eventDidMount: function(info) {
            // Add custom styling and enhance event display
            info.el.style.cursor = 'pointer';
            info.el.style.overflow = 'visible';
            info.el.style.whiteSpace = 'normal';
            info.el.style.minHeight = 'auto';
            info.el.style.height = 'auto';
            
            // Create a more informative title with time
            const startTime = info.event.start ? new Date(info.event.start).toLocaleTimeString('ar-EG', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            }) : '';
            const endTime = info.event.end ? new Date(info.event.end).toLocaleTimeString('ar-EG', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            }) : '';
            
            const studentName = info.event.extendedProps.student_name || info.event.title || '';
            const teacherName = info.event.extendedProps.teacher_name || '';
            
            // Update event title to show student name and time
            const titleElement = info.el.querySelector('.fc-event-title');
            if (titleElement) {
                titleElement.style.whiteSpace = 'normal';
                titleElement.style.overflow = 'visible';
                titleElement.style.wordWrap = 'break-word';
                titleElement.style.wordBreak = 'break-word';
                titleElement.style.fontSize = '0.9rem';
                titleElement.style.fontWeight = '600';
                titleElement.style.lineHeight = '1.4';
            }
        },
        eventContent: function(arg) {
            // Custom event content rendering - match admin calendar format
            const studentName = arg.event.extendedProps.student_name || arg.event.student || arg.event.title || '';
            const teacherName = arg.event.extendedProps.teacher_name || arg.event.teacher || '';
            
            // Get start and end times
            let startTime = '';
            let endTime = '';
            if (arg.event.start) {
                const start = new Date(arg.event.start);
                startTime = start.toLocaleTimeString('ar-EG', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
            }
            if (arg.event.end) {
                const end = new Date(arg.event.end);
                endTime = end.toLocaleTimeString('ar-EG', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
            }
            
            const timeRange = startTime && endTime ? `${startTime} - ${endTime}` : (startTime || '');
            
            let html = '<div class="fc-event-main-frame" style="width: 100%; display: flex; flex-direction: column; gap: 0.25rem;">';
            html += '<div class="fc-event-title-container" style="display: flex; flex-direction: column; gap: 0.25rem;">';
            html += '<div class="fc-event-title" style="font-weight: 600; font-size: 0.9rem; line-height: 1.4; white-space: normal; overflow: visible; word-wrap: break-word;">' + studentName + '</div>';
            if (teacherName) {
                html += '<div class="fc-event-teacher" style="font-weight: 500; font-size: 0.8rem; line-height: 1.3; white-space: normal; overflow: visible; word-wrap: break-word; opacity: 0.95;">' + teacherName + '</div>';
            }
            html += '</div>';
            if (timeRange) {
                html += '<div class="fc-event-time" style="font-weight: 500; font-size: 0.75rem; margin-top: 0.25rem; padding-top: 0.25rem; border-top: 1px solid rgba(255,255,255,0.3); white-space: normal; overflow: visible; opacity: 0.9;">' + timeRange + '</div>';
            }
            html += '</div>';
            
            return { html: html };
        }
    });

    calendar.render();

    // Apply filters
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    const filterStudent = document.getElementById('filter_student');
    const filterTeacher = document.getElementById('filter_teacher');
    
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            currentFilters.student_id = filterStudent ? filterStudent.value : '';
            currentFilters.teacher_id = filterTeacher ? filterTeacher.value : '';
            calendar.refetchEvents();
        });
    }

    // Export functionality
    const exportTodayBtn = document.getElementById('exportTodayBtn');
    const exportWeekBtn = document.getElementById('exportWeekBtn');
    const exportMonthBtn = document.getElementById('exportMonthBtn');
    const exportRangeSubmitBtn = document.getElementById('exportRangeSubmitBtn');
    
    function exportEvents(startDate, endDate) {
        // Get current filter values
        const studentDropdown = document.getElementById('filter_student');
        const teacherDropdown = document.getElementById('filter_teacher');
        
        const selectedStudentId = studentDropdown && studentDropdown.value ? studentDropdown.value.trim() : '';
        const selectedTeacherId = teacherDropdown && teacherDropdown.value ? teacherDropdown.value.trim() : '';
        
        // Build URL with date range
        let url = window.calendarRoutes.export + '?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate);
        
        // Only add filters if they are selected
        if (selectedStudentId && selectedStudentId !== '' && selectedStudentId !== '0') {
            url += '&student_id=' + encodeURIComponent(selectedStudentId);
        }
        
        if (selectedTeacherId && selectedTeacherId !== '' && selectedTeacherId !== '0') {
            url += '&teacher_id=' + encodeURIComponent(selectedTeacherId);
        }
        
        window.open(url, '_blank');
    }

    if (exportTodayBtn) {
        exportTodayBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const today = new Date().toISOString().split('T')[0];
            exportEvents(today, today);
        });
    }

    if (exportWeekBtn) {
        exportWeekBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const view = calendar.view;
            const start = view.activeStart.toISOString().split('T')[0];
            const end = view.activeEnd.toISOString().split('T')[0];
            exportEvents(start, end);
        });
    }

    if (exportMonthBtn) {
        exportMonthBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const view = calendar.view;
            const start = view.activeStart.toISOString().split('T')[0];
            const end = view.activeEnd.toISOString().split('T')[0];
            exportEvents(start, end);
        });
    }

    if (exportRangeSubmitBtn) {
        exportRangeSubmitBtn.addEventListener('click', function() {
            const startDate = document.getElementById('export_start_date').value;
            const endDate = document.getElementById('export_end_date').value;
            
            if (!startDate || !endDate) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'يرجى اختيار تاريخ البداية والنهاية',
                        confirmButtonText: 'حسناً'
                    });
                }
                return;
            }

            if (startDate > endDate) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية',
                        confirmButtonText: 'حسناً'
                    });
                }
                return;
            }

            exportEvents(startDate, endDate);
            const modal = bootstrap.Modal.getInstance(document.getElementById('exportRangeModal'));
            if (modal) modal.hide();
        });
    }

    // Load lesson details (read-only)
    function loadLessonDetails(eventId) {
        const showRoute = window.calendarRoutes.show.replace(':id', eventId);
        
        fetch(showRoute, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('lessonDetailModal'));
            const content = document.getElementById('lessonDetailContent');
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>الطالب:</strong> ${data.student_name || 'N/A'}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>المعلم:</strong> ${data.teacher_name || 'N/A'}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>اسم الحصة:</strong> ${data.lesson_name || 'N/A'}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>التاريخ:</strong> ${data.date || 'N/A'}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>وقت البداية:</strong> ${formatTimeTo12Hour(data.start_time) || 'N/A'}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>وقت النهاية:</strong> ${formatTimeTo12Hour(data.end_time) || 'N/A'}
                    </div>
                </div>
            `;
            
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching event details:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'فشل في تحميل تفاصيل الحصة',
                    confirmButtonText: 'حسناً'
                });
            }
        });
    }

}

// Start initialization when DOM is ready
console.log('Script execution started, readyState:', document.readyState);

if (document.readyState === 'loading') {
    console.log('DOM is still loading, waiting for DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded fired, initializing calendar...');
        initCalendarSupport();
    });
} else {
    console.log('DOM already loaded, initializing calendar immediately...');
    initCalendarSupport();
}
