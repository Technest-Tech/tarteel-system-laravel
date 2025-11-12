<?php

namespace App\Http\Controllers\Admin\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\Lessons;
use App\Models\User;
use App\Models\Courses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Mpdf\Mpdf;

class CalendarController extends Controller
{

    /**
     * Display the calendar view with form
     */
    public function index()
    {
        $students = User::where('user_type', User::USER_TYPE['student'])->get();
        $teachers = User::where('user_type', User::USER_TYPE['teacher'])->get();
        $courses = Courses::all();
        
        return view('admin.calendar.index', compact('students', 'teachers', 'courses'));
    }

    /**
     * Get events for calendar (JSON API)
     * Generate events dynamically from timetable entries
     */
    public function events(Request $request)
    {
        try {
            // Parse dates from FullCalendar (ISO 8601 format)
            $startInput = $request->input('start');
            $endInput = $request->input('end');
            
            // Extract date part only (YYYY-MM-DD) - handle various formats
            // Format examples: "2025-10-26T00:00:00+03:00" or "2025-10-26"
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $startInput, $matches)) {
                $startInput = $matches[1];
            }
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $endInput, $matches)) {
                $endInput = $matches[1];
            }
            
            // Validate date format
            if (empty($startInput) || empty($endInput)) {
                \Log::error('Invalid date inputs: start=' . $request->input('start') . ', end=' . $request->input('end'));
                return response()->json([]);
            }
            
            try {
                $start = Carbon::createFromFormat('Y-m-d', $startInput)->startOfDay();
                $end = Carbon::createFromFormat('Y-m-d', $endInput)->endOfDay();
            } catch (\Exception $e) {
                \Log::error('Date parsing error: ' . $e->getMessage() . ' - start=' . $startInput . ', end=' . $endInput);
                return response()->json([]);
            }

            // Get all timetable entries that overlap with the requested date range
            $timetableQuery = Timetable::with(['student', 'teacher'])
                ->where('start_date', '<=', $end->format('Y-m-d'))
                ->where('end_date', '>=', $start->format('Y-m-d'));
            
            // Apply filters if provided
            if ($request->has('student_id') && $request->student_id) {
                $timetableQuery->where('student_id', $request->student_id);
            }
            
            if ($request->has('teacher_id') && $request->teacher_id) {
                $timetableQuery->where('teacher_id', $request->teacher_id);
            }
            
            $timetableEntries = $timetableQuery->get();

            $events = [];

            // Generate events for each timetable entry
            // Since we're storing individual records (start_date = end_date = specific date), we can use them directly
            foreach ($timetableEntries as $entry) {
                // Skip if student or teacher relationship is missing
                if (!$entry->student || !$entry->teacher) {
                    continue;
                }
                
                // Get the event date (start_date and end_date are the same for individual records)
                $entryStartDate = is_string($entry->start_date) ? $entry->start_date : $entry->start_date->format('Y-m-d');
                
                try {
                    $eventDate = Carbon::createFromFormat('Y-m-d', $entryStartDate);
                } catch (\Exception $e) {
                    \Log::error('Invalid date format in timetable entry: ' . $entry->id . ' - ' . $entryStartDate);
                    continue;
                }
                
                // Parse time strings properly (format: HH:MM:SS or HH:MM)
                $startTimeStr = is_string($entry->start_time) ? $entry->start_time : $entry->start_time->format('H:i:s');
                $endTimeStr = is_string($entry->end_time) ? $entry->end_time : $entry->end_time->format('H:i:s');
                
                // Validate time strings
                if (empty($startTimeStr) || empty($endTimeStr)) {
                    continue;
                }
                
                $startTimeParts = explode(':', $startTimeStr);
                $endTimeParts = explode(':', $endTimeStr);
                
                if (count($startTimeParts) < 2 || count($endTimeParts) < 2) {
                    continue;
                }
                
                $startHour = (int)$startTimeParts[0];
                $startMinute = isset($startTimeParts[1]) ? (int)$startTimeParts[1] : 0;
                $endHour = (int)$endTimeParts[0];
                $endMinute = isset($endTimeParts[1]) ? (int)$endTimeParts[1] : 0;
                
                // Calculate duration
                $startTimeMinutes = $startHour * 60 + $startMinute;
                $endTimeMinutes = $endHour * 60 + $endMinute;
                $totalMinutes = $endTimeMinutes - $startTimeMinutes;
                if ($totalMinutes < 0) {
                    $totalMinutes += 24 * 60; // Handle next day
                }
                $duration = round($totalMinutes / 60, 2);

                // Set time on the event date
                $startDateTimeObj = $eventDate->copy()->setTime($startHour, $startMinute, 0);
                $endDateTimeObj = $eventDate->copy()->setTime($endHour, $endMinute, 0);

                if ($endTimeMinutes <= $startTimeMinutes) {
                    $endDateTimeObj->addDay();
                }

                $startDateTime = $startDateTimeObj->format('Y-m-d\TH:i:s');
                $endDateTime = $endDateTimeObj->format('Y-m-d\TH:i:s');

                $eventDateStr = $eventDate->format('Y-m-d');
                $seriesId = $entry->series_id;
                // Use timetable color if set, otherwise use teacher's color
                $color = $entry->color ?: $this->getTeacherColor($entry->teacher_id);

                $events[] = [
                    'id' => 't_' . $entry->id . '_' . $eventDateStr,
                    'timetable_id' => $entry->id,
                    'title' => $entry->student->user_name ?? 'Student',
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'student' => $entry->student->user_name ?? 'N/A',
                    'teacher' => $entry->teacher->user_name ?? 'N/A',
                    'lesson_name' => $entry->lesson_name ?? 'Lesson',
                    'duration' => $duration,
                    'color' => $color,
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'student_id' => $entry->student_id,
                        'teacher_id' => $entry->teacher_id,
                        'lesson_name' => $entry->lesson_name,
                        'timetable_id' => $entry->id,
                        'series_id' => $seriesId,
                        'date' => $eventDateStr,
                        'color' => $color,
                    ]
                ];
            }

            return response()->json($events);
        } catch (\Exception $e) {
            \Log::error('Calendar events error: ' . $e->getMessage());
            \Log::error('Calendar events stack trace: ' . $e->getTraceAsString());
            // Return empty array instead of error object so FullCalendar doesn't break
            return response()->json([]);
        }
    }

    /**
     * Store timetable entries
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'schedule_entries' => 'required|array|min:1',
            'schedule_entries.*.student_id' => 'required|exists:users,id',
            'schedule_entries.*.teacher_id' => 'required|exists:users,id',
            'schedule_entries.*.start_time' => 'required',
            'schedule_entries.*.end_time' => 'required',
            'schedule_entries.*.day' => 'required|integer|between:0,6',
            'schedule_entries.*.lesson_name' => 'nullable|string',
            'timezone' => 'nullable|string',
            'color' => 'nullable|string|max:32',
            'series_id' => 'nullable|string|max:64',
            'schedule_entries.*.series_id' => 'nullable|string|max:64',
            'schedule_entries.*.color' => 'nullable|string|max:32',
        ]);

        // Validate end_time is after start_time for each entry
        foreach ($request->schedule_entries as $index => $entry) {
            if ($entry['start_time'] >= $entry['end_time']) {
                return response()->json([
                    'success' => false,
                    'message' => 'وقت النهاية يجب أن يكون بعد وقت البداية في الصف ' . ($index + 1)
                ], 422);
            }
        }

        $savedEntries = [];
        $start = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

        // Process each schedule entry and create individual timetable records for each matching date
        foreach ($request->schedule_entries as $entry) {
            // Times are saved as-is (Egypt time), regardless of selected timezone
            // The timezone field is stored for reference only and does not affect the saved times
            $startTime = Carbon::createFromFormat('H:i', $entry['start_time'])->format('H:i:s');
            $endTime = Carbon::createFromFormat('H:i', $entry['end_time'])->format('H:i:s');

            if ($startTime === $endTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'وقت النهاية يجب أن يختلف عن وقت البداية'
                ], 422);
            }

            $seriesId = $entry['series_id'] ?? $request->input('series_id') ?? (string) Str::uuid();
            $color = $entry['color'] ?? $request->input('color');
            if (!$color) {
                // Use teacher's color if no color is provided
                $color = $this->getTeacherColor($entry['teacher_id'] ?? $request->input('teacher_id'));
            }
            
            // Loop through all dates from start_date to end_date
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                // Check if current date matches the weekday (Carbon: 0=Sunday, 1=Monday, etc.)
                if ($currentDate->dayOfWeek == $entry['day']) {
                    // Create a separate timetable record for this specific date
                    $timetableEntry = Timetable::create([
                        'series_id' => $seriesId,
                        'student_id' => $entry['student_id'],
                        'teacher_id' => $entry['teacher_id'],
                        'day' => $entry['day'],
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'start_date' => $currentDate->format('Y-m-d'), // The specific occurrence date
                        'end_date' => $currentDate->format('Y-m-d'), // Same date for individual occurrence
                        'lesson_name' => $entry['lesson_name'] ?? 'Lesson',
                        'color' => $color,
                        'notification_minutes' => isset($entry['notification_minutes']) ? (int)$entry['notification_minutes'] : 30,
                        'notification_sent' => false,
                    ]);

                    $savedEntries[] = $timetableEntry;
                }
                // Move to next day
                $currentDate->addDay();
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($savedEntries) . ' جدول تم إنشاؤه بنجاح',
            'entries_count' => count($savedEntries)
        ]);
    }

    /**
     * Update a single day event (creates a lesson record for that specific date)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'teacher_id' => 'required|exists:users,id',
            'start_time' => 'required',
            'end_time' => 'required',
            'event_date' => 'required|date',
            'original_event_date' => 'nullable|date', // Original date before update
            'notification_minutes' => 'nullable|integer|min:0',
        ]);

        // Validate end_time is after start_time
        if ($request->start_time === $request->end_time) {
            return response()->json([
                'success' => false,
                'message' => 'وقت النهاية يجب أن يختلف عن وقت البداية'
            ], 422);
        }

        // Get the timetable entry to update
        $timetable = Timetable::findOrFail($id);
        
        // Get the event date (the specific date for this timetable record)
        $eventDate = $request->event_date;
        
        // Get the day of week for the new date
        $dayOfWeek = Carbon::createFromFormat('Y-m-d', $eventDate)->dayOfWeek;

        // Update the timetable record
        $updateData = [
            'student_id' => $request->student_id,
            'teacher_id' => $request->teacher_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'start_date' => $eventDate, // Update to the new date
            'end_date' => $eventDate,   // Update to the new date (same for individual records)
            'day' => $dayOfWeek,        // Update day of week based on new date
        ];

        // Add notification_minutes if provided
        if ($request->has('notification_minutes')) {
            $updateData['notification_minutes'] = (int)$request->notification_minutes;
            $updateData['notification_sent'] = false; // Reset notification sent flag when minutes change
        }

        $timetable->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحصة بنجاح'
        ]);
    }

    /**
     * Delete a timetable entry or single day lesson
     */
    public function destroy(Request $request, $id = null)
    {
        if ($request->isMethod('delete') || $request->expectsJson()) {
            return $this->destroyViaApi($request);
        }

        $identifier = $id ?? $request->route('id');

        try {
            $result = $this->executeDeletion([
                'scope' => 'single',
                'event_id' => $identifier,
            ]);

            return redirect()
                ->route('admin.calendar.index')
                ->with('success', $result['message']);
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('admin.calendar.index')
                ->with('error', $e->getMessage());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('admin.calendar.index')
                ->with('error', 'لم يتم العثور على الحصة أو الجدول المطلوب');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.calendar.index')
                ->with('error', 'حدث خطأ أثناء حذف الحصة: ' . $e->getMessage());
        }
    }

    protected function destroyViaApi(Request $request)
    {
        try {
            $data = $request->validate([
                'scope' => 'nullable|in:single,future,series',
                'timetable_id' => 'nullable|integer|exists:timetable,id',
                'event_id' => 'nullable|string',
                'event_date' => 'nullable|date',
                'series_id' => 'nullable|string|max:64',
            ]);

            $result = $this->executeDeletion($data);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'entries_deleted' => $result['entries_deleted'],
                'lessons_deleted' => $result['lessons_deleted'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على الحصة أو الجدول المطلوب',
            ], 404);
        } catch (\Throwable $e) {
            \Log::error('Calendar delete error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الحصة.',
            ], 500);
        }
    }

    protected function executeDeletion(array $payload): array
    {
        $scope = $payload['scope'] ?? 'single';
        $eventId = $payload['event_id'] ?? null;
        $timetableId = $payload['timetable_id'] ?? null;
        $eventDate = $payload['event_date'] ?? null;
        $seriesId = $payload['series_id'] ?? null;

        if (!$timetableId && $eventId) {
            if (str_starts_with($eventId, 't_')) {
                $parts = explode('_', $eventId);
                if (isset($parts[1])) {
                    $timetableId = (int) $parts[1];
                }
                if (!$eventDate && isset($parts[2])) {
                    $eventDate = $parts[2];
                }
            } elseif (str_starts_with($eventId, 'l_')) {
                $lessonId = (int) substr($eventId, 2);
                $lesson = Lessons::findOrFail($lessonId);
                $lesson->delete();
                return [
                    'message' => __('تم حذف الحصة بنجاح'),
                    'entries_deleted' => 0,
                    'lessons_deleted' => 1,
                ];
            } elseif (is_numeric($eventId)) {
                $timetableId = (int) $eventId;
            }
        }

        $timetable = null;
        if ($timetableId) {
            $timetable = Timetable::findOrFail($timetableId);
            $seriesId = $seriesId ?? $timetable->series_id;
            $eventDate = $eventDate ?? ($timetable->start_date ? Carbon::parse($timetable->start_date)->format('Y-m-d') : null);
        }

        if (in_array($scope, ['future', 'series'], true) && empty($seriesId)) {
            throw new \InvalidArgumentException('لا يمكن تطبيق الحذف المتسلسل على هذا الجدول.');
        }

        $query = Timetable::query();

        if ($scope === 'single') {
            if (!$timetableId) {
                throw new \InvalidArgumentException('لم يتم العثور على الحصة المطلوبة.');
            }
            $query->where('id', $timetableId);
        } elseif ($scope === 'future') {
            $query->where('series_id', $seriesId);
            if ($eventDate) {
                $query->whereDate('start_date', '>=', $eventDate);
            }
        } else { // series
            $query->where('series_id', $seriesId);
        }

        $entries = $query->get();

        if ($entries->isEmpty()) {
            throw new \InvalidArgumentException('لم يتم العثور على الحصص المطلوبة للحذف.');
        }

        $lessonsDeleted = 0;

        DB::transaction(function () use ($entries, &$lessonsDeleted) {
            foreach ($entries as $entry) {
                $lessonsDeleted += Lessons::whereDate('lesson_date', $entry->start_date)
                    ->where('student_id', $entry->student_id)
                    ->where('teacher_id', $entry->teacher_id)
                    ->delete();

                $entry->delete();
            }
        });

        $message = match ($scope) {
            'future' => __('تم حذف الحصص القادمة بنجاح'),
            'series' => __('تم حذف الجدول وجميع حصصه بنجاح'),
            default => __('تم حذف الحصة بنجاح'),
        };

        return [
            'message' => $message,
            'entries_deleted' => $entries->count(),
            'lessons_deleted' => $lessonsDeleted,
        ];
    }

    /**
     * Get single timetable entry or lesson details
     */
    public function show($id)
    {
        // Handle lesson ID format: l_{lesson_id}
        if (strpos($id, 'l_') === 0) {
            $parts = explode('_', $id);
            if (count($parts) >= 2) {
                $lessonId = $parts[1];
                $lesson = Lessons::with(['student', 'teacher'])->findOrFail($lessonId);
                return response()->json([
                    'success' => true,
                    'timetable' => [
                        'id' => $lesson->id,
                        'student_id' => $lesson->student_id,
                        'teacher_id' => $lesson->teacher_id,
                        'start_time' => $lesson->start_time,
                        'end_time' => $lesson->end_time,
                        'lesson_name' => $lesson->lesson_name,
                        'student' => $lesson->student,
                        'teacher' => $lesson->teacher,
                        'day' => Carbon::parse($lesson->lesson_date)->dayOfWeek,
                        'start_date' => Carbon::parse($lesson->lesson_date)->format('Y-m-d'),
                        'end_date' => Carbon::parse($lesson->lesson_date)->format('Y-m-d'),
                    ]
                ]);
            }
        }
        
        // Handle timetable ID and event ID format
        if (strpos($id, 't_') === 0) {
            // Extract timetable_id from event ID format: t_{timetable_id}_{date}
            $parts = explode('_', $id);
            if (count($parts) >= 2) {
                $timetableId = $parts[1];
                $timetable = Timetable::with(['student', 'teacher'])->findOrFail($timetableId);
            } else {
                abort(404);
            }
        } else {
            $timetable = Timetable::with(['student', 'teacher'])->findOrFail($id);
        }

        return response()->json([
            'success' => true,
            'timetable' => $timetable
        ]);
    }

    /**
     * Export calendar events to PDF
     */
    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'student_id' => 'nullable|integer|exists:users,id',
            'teacher_id' => 'nullable|integer|exists:users,id',
        ]);

        $start = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

        // Debug: log received parameters
        \Log::info('Export request parameters:', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'student_id' => $request->input('student_id'),
            'teacher_id' => $request->input('teacher_id'),
        ]);

        // Get timetable entries - if filters are selected, filter by them; otherwise get all
        // Since start_date = end_date (specific occurrence date), we check if the date falls within range
        $timetableQuery = Timetable::with(['student', 'teacher'])
            ->where('start_date', '>=', $start->format('Y-m-d'))
            ->where('start_date', '<=', $end->format('Y-m-d'))
            ->orderBy('start_date')
            ->orderBy('start_time');

        // Apply student filter only if provided and not empty
        $studentId = $request->input('student_id');
        \Log::info('Received student_id parameter:', [
            'raw' => $request->input('student_id'),
            'processed' => $studentId,
            'type' => gettype($studentId),
            'empty_check' => empty($studentId),
            'not_empty' => !empty($studentId)
        ]);
        
        if (!empty($studentId) && $studentId !== '' && $studentId !== null && $studentId !== '0') {
            // Convert to integer to ensure proper comparison
            $studentId = (int)$studentId;
            $timetableQuery->where('student_id', $studentId);
            \Log::info('Applied student filter:', ['student_id' => $studentId, 'type' => gettype($studentId)]);
        } else {
            \Log::info('Student filter NOT applied - value was empty or invalid:', ['value' => $request->input('student_id')]);
        }

        // Apply teacher filter only if provided and not empty
        $teacherId = $request->input('teacher_id');
        \Log::info('Received teacher_id parameter:', [
            'raw' => $request->input('teacher_id'),
            'processed' => $teacherId,
            'type' => gettype($teacherId),
            'empty_check' => empty($teacherId),
            'not_empty' => !empty($teacherId)
        ]);
        
        if (!empty($teacherId) && $teacherId !== '' && $teacherId !== null && $teacherId !== '0') {
            // Convert to integer to ensure proper comparison
            $teacherId = (int)$teacherId;
            $timetableQuery->where('teacher_id', $teacherId);
            \Log::info('Applied teacher filter:', ['teacher_id' => $teacherId, 'type' => gettype($teacherId)]);
        } else {
            \Log::info('Teacher filter NOT applied - value was empty or invalid:', ['value' => $request->input('teacher_id')]);
        }

        // If no filters are selected, this will return all events in the date range
        $timetableEntries = $timetableQuery->get();
        
        \Log::info('Export query result count:', ['count' => $timetableEntries->count()]);

        // Format events for PDF template
        $events = [];
        foreach ($timetableEntries as $entry) {
            if (!$entry->student || !$entry->teacher) {
                continue;
            }
            
            $eventDate = is_string($entry->start_date) ? $entry->start_date : $entry->start_date->format('Y-m-d');
            
            // Format time from 24-hour to 12-hour format
            $startTimeStr = is_string($entry->start_time) ? $entry->start_time : $entry->start_time->format('H:i:s');
            $endTimeStr = is_string($entry->end_time) ? $entry->end_time : $entry->end_time->format('H:i:s');
            
            // Convert to 12-hour format
            $startTime = $this->formatTimeTo12Hour($startTimeStr);
            $endTime = $this->formatTimeTo12Hour($endTimeStr);
            
            $events[] = [
                'date' => $eventDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'student_name' => $entry->student->user_name ?? '',
                'teacher_name' => $entry->teacher->user_name ?? '',
                'lesson_name' => $entry->lesson_name ?? '',
                'type' => 'timetable',
            ];
        }

        // Get filter names for display
        $studentName = null;
        $teacherName = null;
        if (!empty($studentId)) {
            $student = User::find($studentId);
            $studentName = $student ? $student->user_name : null;
        }
        if (!empty($teacherId)) {
            $teacher = User::find($teacherId);
            $teacherName = $teacher ? $teacher->user_name : null;
        }

        // Format dates for the PDF template
        $startDate = $start->format('Y-m-d');
        $endDate = $end->format('Y-m-d');
        
        $html = view('admin.calendar.pdf-report', compact('events', 'startDate', 'endDate', 'studentName', 'teacherName'))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);
        
        $fileName = 'calendar_report_' . $start->format('Y-m-d') . '_' . $end->format('Y-m-d') . '.pdf';
        $mpdf->Output($fileName, 'D');
    }

    /**
     * Format time from 24-hour to 12-hour format
     */
    private function formatTimeTo12Hour($timeString)
    {
        if (empty($timeString)) {
            return '';
        }
        
        // Handle format like "14:30:00" or "14:30"
        $parts = explode(':', $timeString);
        if (count($parts) < 2) {
            return $timeString;
        }
        
        $hour = (int)$parts[0];
        $minute = isset($parts[1]) ? (int)$parts[1] : 0;
        
        $ampm = $hour >= 12 ? 'م' : 'ص';
        $hour12 = $hour % 12;
        if ($hour12 == 0) {
            $hour12 = 12;
        }
        
        return sprintf('%d:%02d %s', $hour12, $minute, $ampm);
    }

    /**
     * Get color for event based on student ID
     */
    private function getEventColor($studentId)
    {
        $colors = [
            '#3b82f6', // blue
            '#10b981', // green
            '#f59e0b', // amber
            '#ef4444', // red
            '#8b5cf6', // purple
            '#ec4899', // pink
            '#06b6d4', // cyan
            '#84cc16', // lime
        ];

        return $colors[$studentId % count($colors)];
    }

    private function getTeacherColor($teacherId)
    {
        if (!$teacherId) {
            return '#3b82f6'; // default blue
        }
        
        $teacher = \App\Models\User::find($teacherId);
        if ($teacher && $teacher->color) {
            return $teacher->color;
        }
        
        // Fallback to default color
        return '#3b82f6';
    }
}
