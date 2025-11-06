<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;

class TimezoneService
{
    /**
     * Get timezone options with readable Arabic names
     */
    public static function getTimezoneOptions()
    {
        return [
            // Middle East & Africa
            'Africa/Cairo' => 'القاهرة (UTC+2)',
            'Asia/Dubai' => 'دبي (UTC+4)',
            'Asia/Riyadh' => 'الرياض (UTC+3)',
            'Asia/Kuwait' => 'الكويت (UTC+3)',
            'Asia/Baghdad' => 'بغداد (UTC+3)',
            'Asia/Beirut' => 'بيروت (UTC+2)',
            'Asia/Amman' => 'عمان (UTC+2)',
            'Asia/Damascus' => 'دمشق (UTC+2)',
            'Africa/Casablanca' => 'الدار البيضاء (UTC+1)',
            'Africa/Tunis' => 'تونس (UTC+1)',
            'Africa/Algiers' => 'الجزائر (UTC+1)',
            'Europe/Istanbul' => 'إسطنبول (UTC+3)',
            'Asia/Jerusalem' => 'القدس (UTC+2)',
            'Asia/Qatar' => 'الدوحة (UTC+3)',
            'Asia/Bahrain' => 'المنامة (UTC+3)',
            'Asia/Muscat' => 'مسقط (UTC+4)',
            
            // Europe
            'Europe/London' => 'لندن (UTC+0/+1)',
            'Europe/Paris' => 'باريس (UTC+1/+2)',
            'Europe/Berlin' => 'برلين (UTC+1/+2)',
            'Europe/Rome' => 'روما (UTC+1/+2)',
            'Europe/Madrid' => 'مدريد (UTC+1/+2)',
            'Europe/Amsterdam' => 'أمستردام (UTC+1/+2)',
            'Europe/Brussels' => 'بروكسل (UTC+1/+2)',
            'Europe/Vienna' => 'فيينا (UTC+1/+2)',
            'Europe/Prague' => 'براغ (UTC+1/+2)',
            'Europe/Warsaw' => 'وارسو (UTC+1/+2)',
            'Europe/Stockholm' => 'ستوكهولم (UTC+1/+2)',
            'Europe/Copenhagen' => 'كوبنهاغن (UTC+1/+2)',
            'Europe/Oslo' => 'أوسلو (UTC+1/+2)',
            'Europe/Helsinki' => 'هلسنكي (UTC+2/+3)',
            'Europe/Athens' => 'أثينا (UTC+2/+3)',
            'Europe/Lisbon' => 'لشبونة (UTC+0/+1)',
            'Europe/Dublin' => 'دبلن (UTC+0/+1)',
            'Europe/Budapest' => 'بودابست (UTC+1/+2)',
            'Europe/Bucharest' => 'بوخارست (UTC+2/+3)',
            'Europe/Sofia' => 'صوفيا (UTC+2/+3)',
            'Europe/Zagreb' => 'زغرب (UTC+1/+2)',
            'Europe/Belgrade' => 'بلغراد (UTC+1/+2)',
            'Europe/Kiev' => 'كييف (UTC+2/+3)',
            'Europe/Moscow' => 'موسكو (UTC+3)',
            'Europe/Zurich' => 'زيورخ (UTC+1/+2)',
            'Europe/Geneva' => 'جنيف (UTC+1/+2)',
            'Europe/Luxembourg' => 'لوكسمبورغ (UTC+1/+2)',
            'Europe/Malta' => 'مالطا (UTC+1/+2)',
            'Europe/Nicosia' => 'نيقوسيا (UTC+2/+3)',
            
            // Other
            'UTC' => 'UTC (UTC+0)',
        ];
    }

    /**
     * Convert time from one timezone to another
     */
    public static function convertTime($time, $fromTimezone, $toTimezone)
    {
        if (!$time || !$fromTimezone || !$toTimezone) {
            return $time;
        }

        try {
            // Parse the time string (format: HH:MM:SS or HH:MM)
            $timeParts = explode(':', $time);
            $hour = (int)$timeParts[0];
            $minute = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
            $second = isset($timeParts[2]) ? (int)$timeParts[2] : 0;

            // Create a Carbon instance with today's date and the time
            $dateTime = Carbon::today($fromTimezone)->setTime($hour, $minute, $second);
            
            // Convert to target timezone
            $converted = $dateTime->setTimezone($toTimezone);
            
            // Return as time string
            return $converted->format('H:i:s');
        } catch (\Exception $e) {
            \Log::error('Timezone conversion error: ' . $e->getMessage());
            return $time;
        }
    }

    /**
     * Adjust all timetable entries for a student when timezone changes
     */
    public static function adjustTimetableForTimezone($studentId, $oldTimezone, $newTimezone)
    {
        if (!$oldTimezone || !$newTimezone || $oldTimezone === $newTimezone) {
            return;
        }

        try {
            // Get all timetable entries for this student
            $timetableEntries = Timetable::where('student_id', $studentId)->get();

            foreach ($timetableEntries as $entry) {
                // Convert start_time and end_time
                $newStartTime = self::convertTime($entry->start_time, $oldTimezone, $newTimezone);
                $newEndTime = self::convertTime($entry->end_time, $oldTimezone, $newTimezone);

                // Update the entry
                $entry->update([
                    'start_time' => $newStartTime,
                    'end_time' => $newEndTime,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Timetable timezone adjustment error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get UTC offset for a timezone
     */
    public static function getUtcOffset($timezone)
    {
        try {
            $dateTime = Carbon::now($timezone);
            $utcOffset = $dateTime->utcOffset();
            $hours = floor(abs($utcOffset) / 3600);
            $minutes = (abs($utcOffset) % 3600) / 60;
            $sign = $utcOffset >= 0 ? '+' : '-';
            return $sign . str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            return '+00:00';
        }
    }
}

