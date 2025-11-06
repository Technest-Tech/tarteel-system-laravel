// Wait for FullCalendar to be loaded
console.log('calendar-teacher.js loaded');
function initCalendarTeacher() {
    console.log('initCalendarTeacher called, FullCalendar defined:', typeof FullCalendar !== 'undefined');
    if (typeof FullCalendar === 'undefined') {
        console.log('FullCalendar not yet loaded, retrying...');
        setTimeout(initCalendarTeacher, 100);
        return;
    }

    let calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Calendar element not found');
        setTimeout(initCalendarTeacher, 100);
        return;
    }

    console.log('Initializing teacher calendar...');
    let calendar;
    let currentEvent = null;

    // Detect mobile device
    const isMobile = window.innerWidth <= 768;
    
    // Initialize FullCalendar (read-only)
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
        editable: false,
        selectable: false,
        events: function(fetchInfo, successCallback, failureCallback) {
            console.log('Fetching events from:', window.calendarRoutes.events);
            console.log('Date range:', fetchInfo.startStr, 'to', fetchInfo.endStr);
            showLoading();
            fetch(window.calendarRoutes.events + '?start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr, {
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Events received:', data);
                    hideLoading();
                    successCallback(data);
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            loadLessonDetails(info.event.id);
        },
        eventDisplay: 'block',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false
        },
        eventDidMount: function(info) {
            // Add custom styling and enhance event display
            info.el.style.cursor = 'pointer';
            
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
            
            // Update title to include time
            const studentName = info.event.extendedProps.student || info.event.title;
            const timeRange = startTime && endTime ? ` (${startTime} - ${endTime})` : '';
            
            // Set title attribute for tooltip
            info.el.setAttribute('title', `${studentName} - ${info.event.extendedProps.teacher || 'N/A'}${timeRange}`);
            
            // Enhance event content display
            const titleElement = info.el.querySelector('.fc-event-title');
            if (titleElement) {
                titleElement.style.fontSize = '0.85rem';
                titleElement.style.fontWeight = '700';
                titleElement.style.lineHeight = '1.3';
            }
            
            // Ensure event has minimum height based on view
            const isTimeGrid = info.view.type.includes('timeGrid');
            if (isTimeGrid) {
                info.el.style.minHeight = '90px';
                info.el.style.display = 'flex';
                info.el.style.flexDirection = 'column';
                info.el.style.alignItems = 'flex-start';
                info.el.style.justifyContent = 'flex-start';
                info.el.style.padding = '8px 10px';
                info.el.style.overflow = 'visible';
                
                // Ensure proper spacing for child elements
                const titleContainer = info.el.querySelector('.fc-event-title-container');
                if (titleContainer) {
                    titleContainer.style.display = 'flex';
                    titleContainer.style.flexDirection = 'column';
                    titleContainer.style.gap = '4px';
                    titleContainer.style.marginBottom = '2px';
                    titleContainer.style.width = '100%';
                    titleContainer.style.flexShrink = '1';
                }
                
                const titleElement = info.el.querySelector('.fc-event-title');
                if (titleElement) {
                    titleElement.style.marginBottom = '4px';
                    titleElement.style.marginTop = '0';
                    titleElement.style.display = 'block';
                    titleElement.style.lineHeight = '1.4';
                    titleElement.style.height = 'auto';
                    titleElement.style.minHeight = 'auto';
                    titleElement.style.padding = '0';
                }
                
                const teacherElement = info.el.querySelector('.fc-event-teacher');
                if (teacherElement) {
                    teacherElement.style.marginTop = '0';
                    teacherElement.style.marginBottom = '4px';
                    teacherElement.style.display = 'block';
                    teacherElement.style.lineHeight = '1.3';
                    teacherElement.style.height = 'auto';
                    teacherElement.style.minHeight = 'auto';
                    teacherElement.style.padding = '0';
                }
                
                const timeElement = info.el.querySelector('.fc-event-time');
                if (timeElement) {
                    timeElement.style.marginTop = '4px';
                    timeElement.style.display = 'block';
                    timeElement.style.lineHeight = '1.3';
                    timeElement.style.height = 'auto';
                    timeElement.style.padding = '0';
                    timeElement.style.visibility = 'visible';
                    timeElement.style.opacity = '1';
                    timeElement.style.color = 'inherit';
                }
            } else {
                info.el.style.minHeight = '40px';
                info.el.style.padding = '8px 12px';
            }
        },
        eventContent: function(arg) {
            // Custom event content rendering
            const studentName = arg.event.extendedProps.student || arg.event.title;
            const teacherName = arg.event.extendedProps.teacher || '';
            
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
            
            const timeRange = startTime && endTime ? `${startTime} - ${endTime}` : (startTime || arg.timeText || '');
            
            let html = '<div class="fc-event-main-frame">';
            html += '<div class="fc-event-title-container">';
            html += '<div class="fc-event-title">' + studentName + '</div>';
            if (teacherName) {
                html += '<div class="fc-event-teacher">' + teacherName + '</div>';
            }
            html += '</div>';
            if (timeRange) {
                html += '<div class="fc-event-time">' + timeRange + '</div>';
            }
            html += '</div>';
            
            return { html: html };
        }
    });

    calendar.render();
    console.log('Teacher calendar rendered successfully');

    // Make calendar accessible globally for export functions
    window.teacherCalendar = calendar;

    // Load lesson details (read-only)
    function loadLessonDetails(eventId) {
        showLoading();
        fetch(window.calendarRoutes.show.replace(':id', eventId), {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.student_name || data.teacher_name) {
                    displayLessonDetails(data, eventId);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'لم يتم العثور على تفاصيل الحصة',
                        confirmButtonText: 'حسناً'
                    });
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error loading lesson:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء تحميل تفاصيل الحصة',
                    confirmButtonText: 'حسناً'
                });
            });
    }

    // Display lesson details in modal (read-only)
    function displayLessonDetails(data, eventId) {
        const modalBody = document.getElementById('lessonDetailContent');
        const modal = new bootstrap.Modal(document.getElementById('lessonDetailModal'));
        
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>الطالب:</strong> ${data.student_name || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>المعلم:</strong> ${data.teacher_name || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>التاريخ:</strong> ${data.date || data.start_date || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>اسم الحصة:</strong> ${data.lesson_name || 'N/A'}
                </div>
                ${data.start_date ? `<div class="col-md-6 mb-3"><strong>تاريخ البداية:</strong> ${new Date(data.start_date).toLocaleDateString('ar-EG')}</div>` : ''}
                ${data.end_date ? `<div class="col-md-6 mb-3"><strong>تاريخ النهاية:</strong> ${new Date(data.end_date).toLocaleDateString('ar-EG')}</div>` : ''}
                <div class="col-md-6 mb-3">
                    <strong>وقت البداية:</strong> ${formatTimeTo12Hour(data.start_time)}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>وقت النهاية:</strong> ${formatTimeTo12Hour(data.end_time)}
                </div>
            </div>
        `;
        
        modal.show();
    }

    // Helper function to format time from 24-hour to 12-hour format
    function formatTimeTo12Hour(timeString) {
        if (!timeString) return '';
        const [hours, minutes] = timeString.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'م' : 'ص';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }

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

    // Export functionality
    const exportTodayBtn = document.getElementById('exportTodayBtn');
    const exportWeekBtn = document.getElementById('exportWeekBtn');
    const exportMonthBtn = document.getElementById('exportMonthBtn');
    const exportRangeSubmitBtn = document.getElementById('exportRangeSubmitBtn');
    
    function exportEvents(startDate, endDate) {
        // Build URL with date range (teacher only sees their own events)
        let url = window.calendarRoutes.export + '?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate);
        
        console.log('Export URL:', url);
        console.log('Full URL with base:', window.location.origin + url);
        
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
            const cal = window.teacherCalendar || calendar;
            if (!cal) {
                console.error('Calendar not available');
                return;
            }
            const view = cal.view;
            const start = view.activeStart.toISOString().split('T')[0];
            const end = view.activeEnd.toISOString().split('T')[0];
            exportEvents(start, end);
        });
    }

    if (exportMonthBtn) {
        exportMonthBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const cal = window.teacherCalendar || calendar;
            if (!cal) {
                console.error('Calendar not available');
                return;
            }
            const view = cal.view;
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
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'يرجى اختيار تاريخ البداية والنهاية',
                    confirmButtonText: 'حسناً'
                });
                return;
            }

            if (startDate > endDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية',
                    confirmButtonText: 'حسناً'
                });
                return;
            }

            exportEvents(startDate, endDate);
            const modal = bootstrap.Modal.getInstance(document.getElementById('exportRangeModal'));
            if (modal) modal.hide();
        });
    }
}

// Start initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCalendarTeacher);
} else {
    initCalendarTeacher();
}
