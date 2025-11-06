<?php

namespace App\Http\Controllers\Support\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Lessons;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index()
    {
        $students = \App\Models\User::where('user_type', \App\Models\User::USER_TYPE['student'])->get();
        $teachers = \App\Models\User::where('user_type', \App\Models\User::USER_TYPE['teacher'])->get();
        
        return view('support.calendar.index', compact('students', 'teachers'));
    }

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
            // Same logic as admin calendar - each entry is a single date occurrence
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
                $startDateTime = $eventDate->copy()->setTime($startHour, $startMinute, 0)->format('Y-m-d\TH:i:s');
                $endDateTime = $eventDate->copy()->setTime($endHour, $endMinute, 0)->format('Y-m-d\TH:i:s');

                $events[] = [
                    'id' => 't_' . $entry->id,
                    'timetable_id' => $entry->id,
                    'title' => $entry->student->user_name ?? 'Student',
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'student' => $entry->student->user_name ?? 'N/A',
                    'teacher' => $entry->teacher->user_name ?? 'N/A',
                    'lesson_name' => $entry->lesson_name ?? 'Lesson',
                    'duration' => $duration,
                    'color' => $this->getEventColor($entry->student_id),
                    'extendedProps' => [
                        'student_id' => $entry->student_id,
                        'teacher_id' => $entry->teacher_id,
                        'lesson_name' => $entry->lesson_name,
                        'timetable_id' => $entry->id,
                        'date' => $eventDate->format('Y-m-d'),
                        'student_name' => $entry->student->user_name ?? '',
                        'teacher_name' => $entry->teacher->user_name ?? '',
                    ]
                ];
            }

            return response()->json($events);
        } catch (\Exception $e) {
            \Log::error('Support Calendar Events Error: ' . $e->getMessage());
            \Log::error('Support Calendar Events stack trace: ' . $e->getTraceAsString());
            // Return empty array instead of error object so FullCalendar doesn't break
            return response()->json([]);
        }
    }

    public function show($id)
    {
        // Handle timetable entries (format: t_123)
        if (strpos($id, 't_') === 0) {
            $timetableId = str_replace('t_', '', $id);
            $timetable = Timetable::with(['student', 'teacher'])->find($timetableId);
            
            if ($timetable) {
                $eventDate = is_string($timetable->start_date) ? $timetable->start_date : $timetable->start_date->format('Y-m-d');
                
                return response()->json([
                    'id' => $id,
                    'type' => 'timetable',
                    'student_name' => $timetable->student->user_name ?? '',
                    'teacher_name' => $timetable->teacher->user_name ?? '',
                    'lesson_name' => $timetable->lesson_name,
                    'date' => $eventDate,
                    'start_time' => $timetable->start_time,
                    'end_time' => $timetable->end_time,
                    'start_date' => $eventDate,
                    'end_date' => $eventDate,
                ]);
            }
        } elseif (strpos($id, 'lesson_') === 0) {
            $lessonId = str_replace('lesson_', '', $id);
            $lesson = Lessons::with(['student', 'teacher'])->find($lessonId);
            
            if ($lesson) {
                $lessonDate = Carbon::parse($lesson->lesson_date);
                return response()->json([
                    'id' => $id,
                    'type' => 'lesson',
                    'student_name' => $lesson->student->user_name ?? '',
                    'teacher_name' => $lesson->teacher->user_name ?? '',
                    'lesson_name' => $lesson->lesson_name,
                    'date' => $lessonDate->format('Y-m-d'),
                    'start_time' => $lesson->start_time ?: $lessonDate->format('H:i:s'),
                    'end_time' => $lesson->end_time,
                ]);
            }
        }

        return response()->json(['error' => 'Not found'], 404);
    }

    public function export(Request $request)
    {
        // Reuse admin export logic which already works
        return app(\App\Http\Controllers\Admin\Calendar\CalendarController::class)->export($request);
    }

    protected function getEventColor($studentId)
    {
        // Generate a consistent color based on student ID
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
            '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B739', '#52BE80',
            '#EC7063', '#5DADE2', '#58D68D', '#F4D03F', '#AF7AC5',
        ];
        
        return $colors[$studentId % count($colors)];
    }
}

