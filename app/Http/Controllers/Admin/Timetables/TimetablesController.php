<?php

namespace App\Http\Controllers\Admin\Timetables;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\User;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimetablesController extends Controller
{
    /**
     * Timetable list view.
     */
    public function index()
    {
        $students = User::where('user_type', User::USER_TYPE['student'])
            ->orderBy('user_name')
            ->get();

        $teachers = User::where('user_type', User::USER_TYPE['teacher'])
            ->orderBy('user_name')
            ->get();

        $timezones = TimezoneService::getTimezoneOptions();

        return view('admin.timetables.index', compact('students', 'teachers', 'timezones'));
    }

    /**
     * Return grouped timetable data with filters applied.
     */
    public function list(Request $request)
    {
        $request->validate([
            'student_id' => 'nullable|exists:users,id',
            'teacher_id' => 'nullable|exists:users,id',
            'period_from' => 'nullable|date',
            'period_to' => 'nullable|date|after_or_equal:period_from',
        ]);

        $query = Timetable::with(['student', 'teacher'])
            ->when($request->filled('student_id'), function ($q) use ($request) {
                $q->where('student_id', $request->integer('student_id'));
            })
            ->when($request->filled('teacher_id'), function ($q) use ($request) {
                $q->where('teacher_id', $request->integer('teacher_id'));
            })
            ->when($request->filled('period_from'), function ($q) use ($request) {
                $q->whereDate('start_date', '>=', $request->input('period_from'));
            })
            ->when($request->filled('period_to'), function ($q) use ($request) {
                $q->whereDate('start_date', '<=', $request->input('period_to'));
            })
            ->orderBy('series_id')
            ->orderBy('start_date');

        $entries = $query->get();

        // Group by series_id, but also include student_id to ensure different students show separately
        $grouped = $entries->groupBy(function (Timetable $entry) {
            $seriesKey = $entry->series_id ?: 'single-' . $entry->id;
            // Include student_id in the key to ensure different students are grouped separately
            return $entry->student_id . '-' . $seriesKey;
        });

        $data = $grouped->map(function (Collection $group, string $seriesKey) {
            $first = $group->first();

            $days = $group->pluck('day')
                ->filter(static function ($day) {
                    return $day !== null;
                })
                ->unique()
                ->sort()
                ->values()
                ->all();

            $startDate = $group->min('start_date');
            $endDate = $group->max('end_date');

            return [
                'series_id' => $first->series_id,
                'group_key' => $seriesKey,
                'student' => [
                    'id' => $first->student_id,
                    'name' => optional($first->student)->user_name,
                ],
                'teacher' => [
                    'id' => $first->teacher_id,
                    'name' => optional($first->teacher)->user_name,
                ],
                'lesson_name' => $first->lesson_name,
                'start_time' => $first->start_time,
                'end_time' => $first->end_time,
                'color' => $first->color,
                'days' => $days,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'entries_count' => $group->count(),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * Create a new timetable series.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $seriesId = (string) Str::uuid();

        $entriesCount = DB::transaction(function () use ($validated, $seriesId) {
            return $this->persistSeries($seriesId, $validated);
        });

        return response()->json([
            'success' => true,
            'message' => __('تم إنشاء الجدول بنجاح'),
            'series_id' => $seriesId,
            'entries_count' => $entriesCount,
        ], 201);
    }

    /**
     * Show details for a series (or legacy single entry).
     */
    public function show(string $seriesId)
    {
        $entries = $this->findSeriesEntries($seriesId);

        if ($entries->isEmpty()) {
            abort(404);
        }

        $first = $entries->first();

        $payload = [
            'series_id' => $first->series_id,
            'student_id' => $first->student_id,
            'teacher_id' => $first->teacher_id,
            'lesson_name' => $first->lesson_name,
            'start_time' => $first->start_time,
            'end_time' => $first->end_time,
            'timezone' => $first->student?->timezone,
            'color' => $first->color,
            'notification_minutes' => $first->notification_minutes ?? 30,
            'start_date' => $entries->min('start_date'),
            'end_date' => $entries->max('end_date'),
            'days' => $entries->pluck('day')->unique()->sort()->values()->all(),
            'entries_count' => $entries->count(),
        ];

        return response()->json(['data' => $payload]);
    }

    /**
     * Update an existing timetable series.
     */
    public function update(Request $request, string $seriesId)
    {
        $entries = $this->findSeriesEntries($seriesId);

        if ($entries->isEmpty()) {
            abort(404);
        }

        $validated = $this->validatePayload($request, ['series_id' => $seriesId]);

        $entriesCount = DB::transaction(function () use ($seriesId, $validated) {
            Timetable::where('series_id', $seriesId)->delete();

            return $this->persistSeries($seriesId, $validated);
        });

        return response()->json([
            'success' => true,
            'message' => __('تم تحديث الجدول بنجاح'),
            'series_id' => $seriesId,
            'entries_count' => $entriesCount,
        ]);
    }

    /**
     * Delete a timetable series and all associated entries.
     */
    public function destroy(string $seriesId)
    {
        $entries = $this->findSeriesEntries($seriesId);

        if ($entries->isEmpty()) {
            abort(404);
        }

        Timetable::whereIn('id', $entries->pluck('id'))->delete();

        return response()->json([
            'success' => true,
            'message' => __('تم حذف الجدول وجميع حصصه المرتبطة به'),
        ]);
    }

    /**
     * Validate the payload for creating/updating timetables.
     */
    protected function validatePayload(Request $request, array $context = []): array
    {
        $rules = [
            'student_id' => 'required|exists:users,id',
            'teacher_id' => 'required|exists:users,id',
            'lesson_name' => 'nullable|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days' => 'required|array|min:1',
            'days.*' => 'integer|between:0,6',
            'timezone' => 'nullable|string',
            'color' => 'nullable|string|max:32',
            'notification_minutes' => 'nullable|integer|min:0|max:1440',
        ];

        $messages = [
            'days.required' => __('يجب اختيار يوم واحد على الأقل'),
        ];

        $validated = $request->validate($rules, $messages);

        if ($validated['start_time'] === $validated['end_time']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'end_time' => __('وقت النهاية يجب أن يختلف عن وقت البداية'),
            ]);
        }

        $validated['series_id'] = $context['series_id'] ?? null;

        return $validated;
    }

    /**
     * Find entries for a series id or legacy single entry.
     */
    protected function findSeriesEntries(string $seriesId): Collection
    {
        // Legacy entries may pass "single-{id}" format
        if (str_starts_with($seriesId, 'single-')) {
            $id = (int) str_replace('single-', '', $seriesId);

            return Timetable::with(['student', 'teacher'])->where('id', $id)->get();
        }

        // Try exact series id match first
        $bySeries = Timetable::with(['student', 'teacher'])
            ->where('series_id', $seriesId)
            ->get();

        if ($bySeries->isNotEmpty()) {
            return $bySeries;
        }

        // Fallback: treat numeric id as single entry
        if (is_numeric($seriesId)) {
            return Timetable::with(['student', 'teacher'])
                ->where('id', (int) $seriesId)
                ->get();
        }

        return collect();
    }

    /**
     * Persist a timetable series based on validated payload.
     */
    protected function persistSeries(string $seriesId, array $payload): int
    {
        $student = User::findOrFail($payload['student_id']);

        // Times are saved as-is (Egypt time), regardless of selected timezone
        // The timezone field is stored for reference only and does not affect the saved times
        $startTime = Carbon::createFromFormat('H:i', $payload['start_time'])->format('H:i:s');
        $endTime = Carbon::createFromFormat('H:i', $payload['end_time'])->format('H:i:s');

        $days = collect($payload['days'])
            ->map(fn ($day) => (int) $day)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $startDate = Carbon::createFromFormat('Y-m-d', $payload['start_date'])->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $payload['end_date'])->endOfDay();

        $color = $payload['color'] ?? null;
        $notificationMinutes = isset($payload['notification_minutes'])
            ? (int) $payload['notification_minutes']
            : 30;

        $entriesCount = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if (in_array($currentDate->dayOfWeek, $days, true)) {
                Timetable::create([
                    'series_id' => $seriesId,
                    'student_id' => $student->id,
                    'teacher_id' => $payload['teacher_id'],
                    'day' => $currentDate->dayOfWeek,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'start_date' => $currentDate->format('Y-m-d'),
                    'end_date' => $currentDate->format('Y-m-d'),
                    'lesson_name' => $payload['lesson_name'] ?? null,
                    'color' => $color,
                    'notification_minutes' => $notificationMinutes,
                    'notification_sent' => false,
                ]);

                $entriesCount++;
            }

            $currentDate->addDay();
        }

        return $entriesCount;
    }
}
