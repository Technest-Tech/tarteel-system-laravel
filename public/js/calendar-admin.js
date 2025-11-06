// Wait for FullCalendar to be loaded
(function() {
    'use strict';
    console.log('=== calendar-admin.js file loaded ===');
    console.log('Current time:', new Date().toISOString());
    console.log('Window location:', window.location.href);
})();

function initCalendarAdmin() {
    console.log('initCalendarAdmin function called');
    
    if (typeof FullCalendar === 'undefined') {
        console.log('FullCalendar not loaded yet, retrying...');
        setTimeout(initCalendarAdmin, 100);
        return;
    }

    let calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.log('Calendar element not found, retrying...');
        setTimeout(initCalendarAdmin, 100);
        return;
    }
    
    console.log('Calendar initialization starting...');

    let calendar;
    let currentEvent = null;
    
    // Filter state
    let currentFilters = {
        student_id: '',
        teacher_id: ''
    };

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
            currentEvent = info.event;
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
            
            // Update title to include time
            const studentName = info.event.extendedProps.student || info.event.title;
            const timeRange = startTime && endTime ? ` (${startTime} - ${endTime})` : '';
            
            // Set title attribute for tooltip
            info.el.setAttribute('title', `${studentName} - ${info.event.extendedProps.teacher || 'N/A'}${timeRange}`);
            
            // Ensure event has minimum height based on view
            const isTimeGrid = info.view.type.includes('timeGrid');
            if (isTimeGrid) {
                info.el.style.minHeight = 'auto';
                info.el.style.height = 'auto';
                info.el.style.display = 'flex';
                info.el.style.flexDirection = 'column';
                info.el.style.alignItems = 'flex-start';
                info.el.style.justifyContent = 'flex-start';
                info.el.style.padding = '0.5rem 0.75rem';
                info.el.style.overflow = 'visible';
                
                // Ensure proper spacing for child elements
                const titleContainer = info.el.querySelector('.fc-event-title-container');
                if (titleContainer) {
                    titleContainer.style.display = 'flex';
                    titleContainer.style.flexDirection = 'column';
                    titleContainer.style.gap = '0.25rem';
                    titleContainer.style.marginBottom = '0';
                    titleContainer.style.width = '100%';
                    titleContainer.style.overflow = 'visible';
                    titleContainer.style.whiteSpace = 'normal';
                }
                
                const titleElement = info.el.querySelector('.fc-event-title');
                if (titleElement) {
                    titleElement.style.marginBottom = '0.25rem';
                    titleElement.style.marginTop = '0';
                    titleElement.style.display = 'block';
                    titleElement.style.lineHeight = '1.4';
                    titleElement.style.height = 'auto';
                    titleElement.style.minHeight = 'auto';
                    titleElement.style.padding = '0';
                    titleElement.style.whiteSpace = 'normal';
                    titleElement.style.overflow = 'visible';
                    titleElement.style.textOverflow = 'clip';
                    titleElement.style.wordWrap = 'break-word';
                    titleElement.style.wordBreak = 'break-word';
                    titleElement.style.fontSize = '0.9rem';
                    titleElement.style.fontWeight = '600';
                }
                
                const teacherElement = info.el.querySelector('.fc-event-teacher');
                if (teacherElement) {
                    teacherElement.style.marginTop = '0.25rem';
                    teacherElement.style.marginBottom = '0.25rem';
                    teacherElement.style.display = 'block';
                    teacherElement.style.lineHeight = '1.3';
                    teacherElement.style.height = 'auto';
                    teacherElement.style.minHeight = 'auto';
                    teacherElement.style.padding = '0';
                    teacherElement.style.whiteSpace = 'normal';
                    teacherElement.style.overflow = 'visible';
                    teacherElement.style.wordWrap = 'break-word';
                    teacherElement.style.fontSize = '0.8rem';
                }
                
                const timeElement = info.el.querySelector('.fc-event-time');
                if (timeElement) {
                    timeElement.style.marginTop = '0.25rem';
                    timeElement.style.display = 'block';
                    timeElement.style.lineHeight = '1.3';
                    timeElement.style.height = 'auto';
                    timeElement.style.padding = '0.25rem 0 0 0';
                    timeElement.style.borderTop = '1px solid rgba(255,255,255,0.3)';
                    timeElement.style.whiteSpace = 'normal';
                    timeElement.style.overflow = 'visible';
                    timeElement.style.fontSize = '0.75rem';
                }
            } else {
                // Day grid view
                info.el.style.minHeight = 'auto';
                info.el.style.height = 'auto';
                info.el.style.padding = '0.5rem 0.75rem';
                info.el.style.overflow = 'visible';
                info.el.style.whiteSpace = 'normal';
                
                // Ensure title elements don't truncate
                const titleElement = info.el.querySelector('.fc-event-title');
                if (titleElement) {
                    titleElement.style.whiteSpace = 'normal';
                    titleElement.style.overflow = 'visible';
                    titleElement.style.textOverflow = 'clip';
                    titleElement.style.wordWrap = 'break-word';
                    titleElement.style.wordBreak = 'break-word';
                    titleElement.style.fontSize = '0.9rem';
                    titleElement.style.fontWeight = '600';
                    titleElement.style.lineHeight = '1.4';
                }
                
                const teacherElement = info.el.querySelector('.fc-event-teacher');
                if (teacherElement) {
                    teacherElement.style.whiteSpace = 'normal';
                    teacherElement.style.overflow = 'visible';
                    teacherElement.style.wordWrap = 'break-word';
                    teacherElement.style.fontSize = '0.8rem';
                }
                
                const timeElement = info.el.querySelector('.fc-event-time');
                if (timeElement) {
                    timeElement.style.whiteSpace = 'normal';
                    timeElement.style.overflow = 'visible';
                    timeElement.style.fontSize = '0.75rem';
                }
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

    // Dynamic Schedule Rows Management
    const scheduleRowsContainer = document.getElementById('scheduleRowsContainer');
    const lessonForm = document.getElementById('lessonForm');
    const submitBtn = document.getElementById('submitBtn');
    const createLessonModal = document.getElementById('createLessonModal');

    // Add time validation for all rows
    if (scheduleRowsContainer) {
        const rows = scheduleRowsContainer.querySelectorAll('.schedule-row');
        rows.forEach(row => {
            const startTimeInput = row.querySelector('.schedule-start-time');
            const endTimeInput = row.querySelector('.schedule-end-time');
            
            if (endTimeInput) {
                endTimeInput.addEventListener('change', function() {
                    if (startTimeInput.value && endTimeInput.value && startTimeInput.value >= endTimeInput.value) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'تحذير',
                            text: 'وقت النهاية يجب أن يكون بعد وقت البداية',
                            confirmButtonText: 'حسناً'
                        });
                        endTimeInput.value = '';
                    }
                });
            }
        });
    }

    // Form submission
    function handleFormSubmit(e) {
        if (e) e.preventDefault();
        
        // Collect data from all schedule rows
        const scheduleEntries = [];
        const rows = scheduleRowsContainer.querySelectorAll('.schedule-row');
        
        // Day names for error messages
        const dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

        // Validate each row and collect data (only filled rows)
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const student = row.querySelector('.schedule-student').value;
            const teacher = row.querySelector('.schedule-teacher').value;
            const startTime = row.querySelector('.schedule-start-time').value;
            const endTime = row.querySelector('.schedule-end-time').value;
            const day = row.querySelector('.schedule-day').value;
            const lessonName = row.querySelector('.schedule-lesson-name').value;
            const notificationMinutes = row.querySelector('.schedule-notification-minutes').value || 30;
            const dayName = dayNames[parseInt(day)] || '';

            // Skip completely empty rows
            if (!student && !teacher && !startTime && !endTime) {
                continue;
            }

            // If any field is filled, all required fields must be filled
            if (student || teacher || startTime || endTime) {
                // Validate required fields
                if (!student || !teacher || !startTime || !endTime) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'يرجى ملء جميع الحقول المطلوبة في يوم ' + dayName,
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                // Validate time range
                if (startTime >= endTime) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: 'وقت النهاية يجب أن يكون بعد وقت البداية في يوم ' + dayName,
                        confirmButtonText: 'حسناً'
                    });
                    return;
                }

                scheduleEntries.push({
                    student_id: student,
                    teacher_id: teacher,
                    start_time: startTime,
                    end_time: endTime,
                    day: parseInt(day),
                    lesson_name: lessonName || 'Lesson',
                    notification_minutes: parseInt(notificationMinutes) || 30
                });
            }
        }

        // Validate at least one entry
        if (scheduleEntries.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'يرجى إضافة صف واحد على الأقل مع بيانات صحيحة',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        // Get date range
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

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

        // Get timezone
        const timezone = document.getElementById('schedule_timezone').value || 'Africa/Cairo';

        // Prepare data
        const data = {
            start_date: startDate,
            end_date: endDate,
            schedule_entries: scheduleEntries,
            timezone: timezone
        };

        // Show loading
        const spinner = submitBtn.querySelector('.spinner-border');
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        // Send AJAX request
        fetch(window.calendarRoutes.store, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'نجح',
                    text: data.message,
                    confirmButtonText: 'حسناً'
                });
                calendar.refetchEvents();
                lessonForm.reset();
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('createLessonModal'));
                if (modal) modal.hide();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: data.message || 'حدث خطأ أثناء إنشاء الحصص',
                    confirmButtonText: 'حسناً'
                });
            }
        })
        .catch(error => {
            hideLoading();
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'حدث خطأ أثناء إنشاء الحصص',
                confirmButtonText: 'حسناً'
            });
        });
    }

    // Handle form submit event
    lessonForm.addEventListener('submit', handleFormSubmit);
    
    // Handle button click (since button is outside form in modal footer)
    submitBtn.addEventListener('click', handleFormSubmit);

    // Reset form when modal is closed
    if (createLessonModal) {
        createLessonModal.addEventListener('hidden.bs.modal', function () {
            lessonForm.reset();
        });
    }

    // Load timetable entry details
    function loadLessonDetails(eventId) {
        showLoading();
        fetch(window.calendarRoutes.show.replace(':id', eventId), {
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success && data.timetable) {
                    // Extract date from event ID if it's in format t_{timetable_id}_{date}
                    let eventDate = null;
                    if (eventId.startsWith('t_')) {
                        const parts = eventId.split('_');
                        if (parts.length >= 3) {
                            eventDate = parts[2]; // Extract date from event ID
                        }
                    } else if (eventId.startsWith('l_')) {
                        // Format: l_{lesson_id} - lesson, don't set eventDate for deletion
                        // eventDate should remain null for lessons so the correct eventId is used
                    }
                    displayLessonDetails(data.timetable, eventDate, eventId);
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error loading timetable:', error);
            });
    }

    // Display timetable details in modal
    function displayLessonDetails(timetable, eventDate, eventId) {
        const modalBody = document.getElementById('lessonModalBody');
        const modal = new bootstrap.Modal(document.getElementById('lessonModal'));
        
        // Get day name
        const dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        const dayName = dayNames[timetable.day] || 'N/A';
        
        // Calculate duration
        const startTime = new Date('2000-01-01T' + timetable.start_time);
        const endTime = new Date('2000-01-01T' + timetable.end_time);
        const duration = (endTime - startTime) / (1000 * 60 * 60);
        
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>الطالب:</strong> ${timetable.student?.user_name || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>المعلم:</strong> ${timetable.teacher?.user_name || 'N/A'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>اليوم:</strong> ${dayName}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>اسم الحصة:</strong> ${timetable.lesson_name || 'N/A'}
                </div>
                ${eventDate ? `<div class="col-md-6 mb-3"><strong>تاريخ الحصة:</strong> ${new Date(eventDate).toLocaleDateString('ar-EG')}</div>` : ''}
                <div class="col-md-6 mb-3">
                    <strong>تاريخ البداية:</strong> ${new Date(timetable.start_date).toLocaleDateString('ar-EG')}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>تاريخ النهاية:</strong> ${new Date(timetable.end_date).toLocaleDateString('ar-EG')}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>وقت البداية:</strong> ${formatTimeTo12Hour(timetable.start_time)}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>وقت النهاية:</strong> ${formatTimeTo12Hour(timetable.end_time)}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>المدة:</strong> ${duration.toFixed(2)} ساعة
                </div>
            </div>
        `;
        
        document.getElementById('editLessonBtn').classList.remove('d-none');
        document.getElementById('deleteLessonBtn').classList.remove('d-none');
        document.getElementById('editLessonBtn').onclick = () => openEditModal(timetable, eventDate, eventId);
        // Use eventId directly if it's a lesson (l_*) or timetable with date (t_*_date)
        // Otherwise use timetable.id for full timetable deletion
        const deleteId = eventId && (eventId.startsWith('l_') || eventId.startsWith('t_')) ? eventId : (timetable.id || eventId);
        document.getElementById('deleteLessonBtn').onclick = () => deleteLesson(timetable.id, eventDate, deleteId);
        
        modal.show();
    }

    // Open edit modal - only for single day edit
    function openEditModal(timetable, eventDate, eventId) {
        // Store timetable ID and event date for backend
        document.getElementById('edit_timetable_id').value = timetable.id;
        document.getElementById('edit_event_date').value = eventDate || '';
        // Store original event date to delete old lesson if date changes
        document.getElementById('edit_original_event_date').value = eventDate || '';
        
        // Populate only the fields shown in modal
        document.getElementById('edit_student_id').value = timetable.student_id;
        document.getElementById('edit_teacher_id').value = timetable.teacher_id;
        document.getElementById('edit_event_date_display').value = eventDate || '';
        document.getElementById('edit_start_time').value = timetable.start_time;
        document.getElementById('edit_end_time').value = timetable.end_time;
        document.getElementById('edit_notification_minutes').value = timetable.notification_minutes || 30;
        
        const editModal = new bootstrap.Modal(document.getElementById('editLessonModal'));
        editModal.show();
    }

    // Save edit
    document.getElementById('saveEditBtn').addEventListener('click', function() {
        const form = document.getElementById('editLessonForm');
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        const timetableId = data.timetable_id;
        let eventDate = data.event_date_display || data.event_date;
        const originalEventDate = data.original_event_date || eventDate;
        
        // Validate
        if (!data.student_id || !data.teacher_id || !eventDate || !data.start_time || !data.end_time) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'يرجى ملء جميع الحقول المطلوبة',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        if (data.start_time >= data.end_time) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'وقت النهاية يجب أن يكون بعد وقت البداية',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        // Update event_date with the selected date from the date picker
        data.event_date = eventDate;
        delete data.event_date_display; // Remove the display field, we only need event_date
        
        // Always send original event date to backend so it can delete old lesson
        data.original_event_date = originalEventDate;

        const saveBtn = this;
        const spinner = saveBtn.querySelector('.spinner-border');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');

        fetch(window.calendarRoutes.update.replace(':id', timetableId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'نجح',
                    text: data.message,
                    confirmButtonText: 'حسناً'
                });
                bootstrap.Modal.getInstance(document.getElementById('editLessonModal')).hide();
                bootstrap.Modal.getInstance(document.getElementById('lessonModal')).hide();
                calendar.refetchEvents();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: data.message || 'حدث خطأ أثناء تحديث الحصة',
                    confirmButtonText: 'حسناً'
                });
            }
        })
        .catch(error => {
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'حدث خطأ أثناء تحديث الحصة',
                confirmButtonText: 'حسناً'
            });
        });
    });

    // Delete lesson or timetable entry
    function deleteLesson(timetableId, eventDate, eventId) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: eventDate ? "سيتم حذف هذه الحصة فقط لهذا اليوم" : 'لا يمكن التراجع عن هذا الإجراء',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // If eventDate is provided, delete the lesson for that specific date
                // Otherwise, delete the entire timetable entry
                let deleteId;
                if (eventDate) {
                    deleteId = 't_' + timetableId + '_' + eventDate;
                } else if (eventId && eventId.startsWith('l_')) {
                    deleteId = eventId;
                } else {
                    deleteId = timetableId;
                }
                
                // Encode the ID for URL
                const encodedId = encodeURIComponent(deleteId);
                
                // Redirect to delete route
                window.location.href = window.calendarRoutes.delete.replace(':id', encodedId);
            }
        });
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

    // Show/hide loading
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('d-none');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('d-none');
    }

    // Filter functionality
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
    console.log('Setting up export functionality...');
    const exportTodayBtn = document.getElementById('exportTodayBtn');
    const exportWeekBtn = document.getElementById('exportWeekBtn');
    const exportMonthBtn = document.getElementById('exportMonthBtn');
    const exportRangeSubmitBtn = document.getElementById('exportRangeSubmitBtn');
    
    console.log('Export buttons found:', {
        exportTodayBtn: !!exportTodayBtn,
        exportWeekBtn: !!exportWeekBtn,
        exportMonthBtn: !!exportMonthBtn,
        exportRangeSubmitBtn: !!exportRangeSubmitBtn
    });
    
    function exportEvents(startDate, endDate) {
        console.log('exportEvents function called with dates:', startDate, endDate);
        
        // Get current filter values directly from dropdowns (refresh element references to ensure we get current values)
        const studentDropdown = document.getElementById('filter_student');
        const teacherDropdown = document.getElementById('filter_teacher');
        
        console.log('Filter dropdowns found:', {
            studentDropdown: !!studentDropdown,
            teacherDropdown: !!teacherDropdown
        });
        
        if (!studentDropdown || !teacherDropdown) {
            console.error('Filter dropdowns not found in exportEvents!');
            // Continue without filters if dropdowns not found
        }
        
        const selectedStudentId = studentDropdown && studentDropdown.value ? studentDropdown.value.trim() : '';
        const selectedTeacherId = teacherDropdown && teacherDropdown.value ? teacherDropdown.value.trim() : '';
        
        // Debug: log the selected values
        console.log('Export - Selected Student ID:', selectedStudentId, 'Type:', typeof selectedStudentId);
        console.log('Export - Selected Teacher ID:', selectedTeacherId, 'Type:', typeof selectedTeacherId);
        console.log('Student dropdown value:', studentDropdown ? studentDropdown.value : 'N/A');
        console.log('Teacher dropdown value:', teacherDropdown ? teacherDropdown.value : 'N/A');
        
        // Build URL with date range
        let url = window.calendarRoutes.export + '?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate);
        
        // Only add filters if they are selected (not empty and not '0')
        if (selectedStudentId && selectedStudentId !== '' && selectedStudentId !== '0') {
            url += '&student_id=' + encodeURIComponent(selectedStudentId);
            console.log('Added student_id to URL:', selectedStudentId);
        } else {
            console.log('Student ID not added - value:', selectedStudentId);
        }
        
        if (selectedTeacherId && selectedTeacherId !== '' && selectedTeacherId !== '0') {
            url += '&teacher_id=' + encodeURIComponent(selectedTeacherId);
            console.log('Added teacher_id to URL:', selectedTeacherId);
        } else {
            console.log('Teacher ID not added - value:', selectedTeacherId);
        }
        
        // Debug: log the URL to console
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

    // Bulk Hour Adjustment
    const adjustTimezoneSelect = document.getElementById('adjust_timezone_select');
    const adjustHours = document.getElementById('adjust_hours');
    const loadStudentsForAdjustBtn = document.getElementById('loadStudentsForAdjustBtn');
    const studentsAdjustListContainer = document.getElementById('studentsAdjustListContainer');
    const bulkAdjustBtn = document.getElementById('bulkAdjustBtn');
    const bulkHourAdjustForm = document.getElementById('bulkHourAdjustForm');

    if (loadStudentsForAdjustBtn) {
        loadStudentsForAdjustBtn.addEventListener('click', function() {
            const timezone = adjustTimezoneSelect.value;
            const hours = adjustHours.value;
            
            if (!timezone || !hours) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'يرجى اختيار المنطقة الزمنية وعدد الساعات',
                    confirmButtonText: 'حسناً'
                });
                return;
            }
            
            // Fetch students with this timezone
            fetch(`/api/students/by-timezone?timezone=${encodeURIComponent(timezone)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.students && data.students.length > 0) {
                    document.getElementById('studentsCount').textContent = data.students.length;
                    const hoursText = parseInt(hours) > 0 ? `+${hours} ساعة` : `${hours} ساعة`;
                    document.getElementById('adjustmentInfo').textContent = hoursText;
                    studentsAdjustListContainer.style.display = 'block';
                    bulkAdjustBtn.style.display = 'block';
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'لا توجد نتائج',
                        text: 'لا يوجد طلاب بهذه المنطقة الزمنية',
                        confirmButtonText: 'حسناً'
                    });
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء تحميل الطلاب',
                    confirmButtonText: 'حسناً'
                });
            });
        });
    }

    if (bulkHourAdjustForm) {
        bulkHourAdjustForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const timezone = adjustTimezoneSelect.value;
            const hours = parseInt(adjustHours.value);
            
            if (!timezone || !hours) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'يرجى اختيار المنطقة الزمنية وعدد الساعات',
                    confirmButtonText: 'حسناً'
                });
                return;
            }
            
            const hoursText = hours > 0 ? `+${hours} ساعة` : `${hours} ساعة`;
            
            Swal.fire({
                title: 'هل أنت متأكد؟',
                html: `سيتم تعديل جميع مواعيد الطلاب في المنطقة الزمنية <strong>${timezone}</strong> بمقدار <strong>${hoursText}</strong><br><br>مثال: إذا كان الموعد 12:40 سيصبح ${hours > 0 ? (12 + hours) : (12 + hours)}:40`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'نعم، قم بالتعديل',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'جاري التعديل...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch('/api/students/bulk-adjust-hours', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            timezone: timezone,
                            hours: hours
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'نجح',
                                html: `تم تعديل مواعيد <strong>${data.updated_students}</strong> طالب<br>تم تعديل <strong>${data.updated_entries}</strong> موعد`,
                                confirmButtonText: 'حسناً'
                            }).then(() => {
                                // Refresh calendar
                                if (calendar) {
                                    calendar.refetchEvents();
                                }
                                // Reset form
                                bulkHourAdjustForm.reset();
                                studentsAdjustListContainer.style.display = 'none';
                                bulkAdjustBtn.style.display = 'none';
                            });
                        } else {
                            throw new Error(data.message || 'فشل التعديل');
                        }
                    })
                    .catch(error => {
                        console.error('Error adjusting hours:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ',
                            text: 'حدث خطأ أثناء تعديل المواعيد',
                            confirmButtonText: 'حسناً'
                        });
                    });
                }
            });
        });
    }

}

// Start initialization when DOM is ready
console.log('Script execution started, readyState:', document.readyState);

if (document.readyState === 'loading') {
    console.log('DOM is still loading, waiting for DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded fired, initializing calendar...');
        initCalendarAdmin();
    });
} else {
    console.log('DOM already loaded, initializing calendar immediately...');
    initCalendarAdmin();
}
