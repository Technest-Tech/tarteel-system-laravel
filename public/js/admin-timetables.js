(function () {
    'use strict';

    const dayLabels = {
        0: 'الأحد',
        1: 'الإثنين',
        2: 'الثلاثاء',
        3: 'الأربعاء',
        4: 'الخميس',
        5: 'الجمعة',
        6: 'السبت',
    };

    const timetableModalEl = document.getElementById('timetableModal');
    if (!timetableModalEl) {
        return;
    }

    const timetableModal = new bootstrap.Modal(timetableModalEl);
    const timetableForm = document.getElementById('timetableForm');
    const saveBtn = document.getElementById('saveTimetableBtn');
    const saveSpinner = saveBtn.querySelector('.spinner-border');
    const timetableSeriesIdInput = document.getElementById('timetable_series_id');
    const weekdaySelector = document.getElementById('weekdaySelector');
    const weekdayError = document.getElementById('weekdayError');
    const timetableTableBody = document.querySelector('#timetablesTable tbody');
    const filtersForm = document.getElementById('timetableFilters');
    const openModalBtn = document.getElementById('openTimetableModalBtn');
    const deleteModal = document.getElementById('deleteSeriesModal') ? new bootstrap.Modal(document.getElementById('deleteSeriesModal')) : null;
    const confirmDeleteBtn = document.getElementById('confirmDeleteSeriesBtn');
    const deleteSpinner = confirmDeleteBtn ? confirmDeleteBtn.querySelector('.spinner-border') : null;
    let seriesIdPendingDelete = null;

    const state = {
        listLoading: false,
        saving: false,
    };

    const searchableControls = {};

    function initSearchableSelects() {
        if (typeof Choices === 'undefined') {
            return;
        }
        document.querySelectorAll('.js-searchable').forEach((select) => {
            const controlKey = select.id || select.name;
            if (!controlKey || searchableControls[controlKey]) {
                return;
            }

            const placeholderOption = select.options.length && select.options[0].value === ''
                ? select.options[0].text
                : '';

            searchableControls[controlKey] = new Choices(select, {
                searchEnabled: true,
                itemSelectText: '',
                removeItemButton: false,
                shouldSort: false,
                allowHTML: false,
                placeholder: placeholderOption !== '',
                placeholderValue: placeholderOption,
            });
        });
    }

    function setSelectValue(selectId, value) {
        const select = document.getElementById(selectId);
        if (!select) return;
        const control = searchableControls[selectId];
        if (control) {
            control.setChoiceByValue(value !== null && value !== undefined ? String(value) : '');
        } else {
            select.value = value || '';
        }
    }

    function resetSelect(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;
        const control = searchableControls[selectId];
        if (control) {
            control.removeActiveItems();
        } else {
            select.value = '';
        }
    }

    function formatTime(value) {
        if (!value) return '';
        const parts = value.split(':');
        const hour = parseInt(parts[0], 10);
        const minute = parts[1] || '00';
        const ampm = hour >= 12 ? 'م' : 'ص';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minute.padStart(2, '0')} ${ampm}`;
    }

    initSearchableSelects();

    function renderTableRows(rows) {
        timetableTableBody.innerHTML = '';

        if (!rows || rows.length === 0) {
            timetableTableBody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">لا توجد بيانات متاحة</td></tr>';
            return;
        }

        rows.forEach((row) => {
            const groupId = row.series_id || row.group_key;
            const tr = document.createElement('tr');

            const colorPreview = row.color
                ? `<span class="timetable-color-preview" style="background-color:${row.color}"></span>`
                : '';

            const daysHtml = (row.days || [])
                .map((day) => `<span class="timetable-day-chip">${dayLabels[day] ?? day}</span>`)
                .join('');

            tr.innerHTML = `
                <td>${colorPreview}${row.student?.name ?? '-'}</td>
                <td>${row.teacher?.name ?? '-'}</td>
                <td>${row.lesson_name ?? '-'}</td>
                <td>${daysHtml || '-'}</td>
                <td>${formatTime(row.start_time)}</td>
                <td>${formatTime(row.end_time)}</td>
                <td>${row.start_date ?? '-'}</td>
                <td>${row.end_date ?? '-'}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-primary btn-sm me-2 timetable-edit" data-series-id="${groupId}">
                        <i class="fas fa-edit me-1"></i>تعديل
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm timetable-delete" data-series-id="${groupId}">
                        <i class="fas fa-trash-alt me-1"></i>حذف الكل
                    </button>
                </td>
            `;

            timetableTableBody.appendChild(tr);
        });
    }

    async function fetchTimetables() {
        if (state.listLoading) return;
        state.listLoading = true;

        const params = new URLSearchParams();
        const studentId = document.getElementById('filter_student').value;
        const teacherId = document.getElementById('filter_teacher').value;
        const periodFrom = document.getElementById('filter_period_from').value;
        const periodTo = document.getElementById('filter_period_to').value;

        if (studentId) params.append('student_id', studentId);
        if (teacherId) params.append('teacher_id', teacherId);
        if (periodFrom) params.append('period_from', periodFrom);
        if (periodTo) params.append('period_to', periodTo);

        try {
            const response = await fetch(`${window.timetableRoutes.list}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('فشل في تحميل الجداول');
            }

            const data = await response.json();
            renderTableRows(data.data || []);
        } catch (error) {
            console.error(error);
            renderTableRows([]);
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'حدث خطأ أثناء تحميل الجداول. حاول مرة أخرى.',
            });
        } finally {
            state.listLoading = false;
        }
    }

    function resetForm() {
        timetableForm.reset();
        timetableSeriesIdInput.value = '';
        resetSelect('timetable_student_id');
        resetSelect('timetable_teacher_id');
        setSelectValue('timetable_timezone', 'Africa/Cairo');
        timetableForm.querySelectorAll('input[name="days[]"]').forEach((input) => {
            input.checked = false;
            input.closest('.weekday-option').classList.remove('active');
        });
        weekdayError.style.display = 'none';
        document.getElementById('timetable_color').value = '#3b82f6';
        document.getElementById('timetable_notification_minutes').value = 30;
    }

    function toggleWeekdayState(event) {
        const checkbox = event.target;
        if (checkbox.type !== 'checkbox') return;

        const wrapper = checkbox.closest('.weekday-option');
        if (!wrapper) return;

        if (checkbox.checked) {
            wrapper.classList.add('active');
        } else {
            wrapper.classList.remove('active');
        }
    }

    function prepareCreateModal() {
        resetForm();
        document.getElementById('timetableModalTitle').textContent = 'إضافة جدول جديد';
        saveBtn.textContent = 'حفظ الجدول';
        saveBtn.appendChild(saveSpinner);
        saveSpinner.classList.add('d-none');
    }

    async function prepareEditModal(seriesId) {
        resetForm();
        document.getElementById('timetableModalTitle').textContent = 'تعديل الجدول';
        saveBtn.textContent = 'تحديث الجدول';
        saveBtn.appendChild(saveSpinner);
        saveSpinner.classList.add('d-none');

        try {
            const response = await fetch(window.timetableRoutes.show.replace(':id', encodeURIComponent(seriesId)), {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('فشل في تحميل بيانات الجدول');
            }

            const payload = await response.json();
            const data = payload.data || {};

            timetableSeriesIdInput.value = seriesId;

        setSelectValue('timetable_student_id', data.student_id || '');
        setSelectValue('timetable_teacher_id', data.teacher_id || '');
        setSelectValue('timetable_timezone', data.timezone || 'Africa/Cairo');
        document.getElementById('timetable_start_time').value = formatTime(data.start_time);
            document.getElementById('timetable_end_time').value = formatTime(data.end_time);
            document.getElementById('timetable_lesson_name').value = data.lesson_name || '';
            document.getElementById('timetable_start_date').value = data.start_date || '';
            document.getElementById('timetable_end_date').value = data.end_date || '';
            document.getElementById('timetable_color').value = data.color || '#3b82f6';
            document.getElementById('timetable_notification_minutes').value = data.notification_minutes ?? 30;

            const selectedDays = new Set(data.days || []);
            timetableForm.querySelectorAll('input[name="days[]"]').forEach((input) => {
                const isChecked = selectedDays.has(Number(input.value));
                input.checked = isChecked;
                input.closest('.weekday-option').classList.toggle('active', isChecked);
            });

            timetableModal.show();
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'تعذر تحميل بيانات الجدول.'
            });
        }
    }

    function collectFormData() {
        const formData = new FormData(timetableForm);
        const checkedDays = Array.from(timetableForm.querySelectorAll('input[name="days[]"]:checked')).map((input) => Number(input.value));

        if (checkedDays.length === 0) {
            weekdayError.style.display = 'block';
            return null;
        }

        weekdayError.style.display = 'none';

        const payload = {
            student_id: formData.get('student_id'),
            teacher_id: formData.get('teacher_id'),
            lesson_name: formData.get('lesson_name') || null,
            start_time: formData.get('start_time'),
            end_time: formData.get('end_time'),
            start_date: formData.get('start_date'),
            end_date: formData.get('end_date'),
            timezone: formData.get('timezone') || 'Africa/Cairo',
            color: formData.get('color') || null,
            notification_minutes: formData.get('notification_minutes') || 30,
            days: checkedDays,
        };

        if (!payload.student_id || !payload.teacher_id || !payload.start_time || !payload.end_time || !payload.start_date || !payload.end_date) {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'يرجى ملء جميع الحقول المطلوبة.'
            });
            return null;
        }

        if (payload.start_time === payload.end_time) {
            Swal.fire({
                icon: 'warning',
                title: 'تحذير',
                text: 'وقت النهاية يجب أن يختلف عن وقت البداية.'
            });
            return null;
        }

        if (payload.start_date > payload.end_date) {
            Swal.fire({
                icon: 'warning',
                title: 'تحذير',
                text: 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.'
            });
            return null;
        }

        return payload;
    }

    async function submitTimetable() {
        if (state.saving) return;

        const payload = collectFormData();
        if (!payload) {
            return;
        }

        const seriesId = timetableSeriesIdInput.value;
        const isUpdate = Boolean(seriesId);

        state.saving = true;
        saveBtn.disabled = true;
        saveSpinner.classList.remove('d-none');

        const url = isUpdate
            ? window.timetableRoutes.update.replace(':id', encodeURIComponent(seriesId))
            : window.timetableRoutes.store;
        const method = isUpdate ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.timetableCsrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const errorBody = await response.json().catch(() => ({}));
                throw new Error(errorBody.message || 'فشل حفظ الجدول');
            }

            const result = await response.json();
            Swal.fire({
                icon: 'success',
                title: 'تم الحفظ',
                text: result.message || 'تم حفظ الجدول بنجاح.'
            });

            timetableModal.hide();
            fetchTimetables();
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: error.message || 'حدث خطأ أثناء حفظ الجدول.'
            });
        } finally {
            state.saving = false;
            saveBtn.disabled = false;
            saveSpinner.classList.add('d-none');
        }
    }

    async function deleteSeries() {
        if (!seriesIdPendingDelete) {
            return;
        }

        confirmDeleteBtn.disabled = true;
        deleteSpinner.classList.remove('d-none');

        try {
            const response = await fetch(window.timetableRoutes.destroy.replace(':id', encodeURIComponent(seriesIdPendingDelete)), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.timetableCsrfToken,
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorBody = await response.json().catch(() => ({}));
                throw new Error(errorBody.message || 'فشل حذف الجدول');
            }

            Swal.fire({
                icon: 'success',
                title: 'تم الحذف',
                text: 'تم حذف الجدول بنجاح.'
            });

            deleteModal.hide();
            fetchTimetables();
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: error.message || 'حدث خطأ أثناء حذف الجدول.'
            });
        } finally {
            seriesIdPendingDelete = null;
            confirmDeleteBtn.disabled = false;
            deleteSpinner.classList.add('d-none');
        }
    }

    weekdaySelector.addEventListener('change', toggleWeekdayState);

    timetableModalEl.addEventListener('hidden.bs.modal', resetForm);

    openModalBtn.addEventListener('click', prepareCreateModal);

    saveBtn.addEventListener('click', submitTimetable);

    filtersForm.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    document.getElementById('applyTimetableFilters').addEventListener('click', fetchTimetables);

    document.getElementById('resetTimetableFilters').addEventListener('click', () => {
        setSelectValue('filter_student', '');
        setSelectValue('filter_teacher', '');
        document.getElementById('filter_period_from').value = '';
        document.getElementById('filter_period_to').value = '';
        fetchTimetables();
    });

    timetableTableBody.addEventListener('click', (event) => {
        const editBtn = event.target.closest('.timetable-edit');
        if (editBtn) {
            const seriesId = editBtn.dataset.seriesId;
            prepareEditModal(seriesId);
            return;
        }

        const deleteBtn = event.target.closest('.timetable-delete');
        if (deleteBtn) {
            seriesIdPendingDelete = deleteBtn.dataset.seriesId;
            if (!seriesIdPendingDelete || !deleteModal) return;
            deleteModal.show();
        }
    });

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', deleteSeries);
    }

    fetchTimetables();
})();
