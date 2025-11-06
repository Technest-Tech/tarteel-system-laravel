<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Timetable;

class TimetableReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $timetable;
    public $minutesBefore;

    /**
     * Create a new message instance.
     */
    public function __construct(Timetable $timetable, $minutesBefore = 30)
    {
        $this->timetable = $timetable;
        $this->minutesBefore = $minutesBefore;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $studentName = $this->timetable->student->user_name ?? 'طالب';
        $teacherName = $this->timetable->teacher->user_name ?? 'معلم';
        $lessonName = $this->timetable->lesson_name ?? 'حصة';
        
        // Format time to 12-hour format with Arabic AM/PM
        $startTime = $this->formatTimeTo12Hour($this->timetable->start_time);
        $endTime = $this->formatTimeTo12Hour($this->timetable->end_time);
        
        // Format date
        $lessonDate = \Carbon\Carbon::parse($this->timetable->start_date)->format('Y-m-d');
        
        return $this->subject('تذكير: حصة قادمة خلال ' . $this->minutesBefore . ' دقيقة')
                    ->view('emails.timetable-reminder')
                    ->with([
                        'studentName' => $studentName,
                        'teacherName' => $teacherName,
                        'lessonName' => $lessonName,
                        'lessonDate' => $lessonDate,
                        'startTime' => $startTime,
                        'endTime' => $endTime,
                        'minutesBefore' => $this->minutesBefore,
                    ]);
    }

    /**
     * Convert 24-hour time format to 12-hour format with Arabic AM/PM
     */
    private function formatTimeTo12Hour($time)
    {
        if (empty($time)) {
            return '';
        }

        try {
            // Parse the time (format: HH:MM:SS or HH:MM)
            $timeParts = explode(':', $time);
            $hour = (int) $timeParts[0];
            $minute = isset($timeParts[1]) ? (int) $timeParts[1] : 0;

            // Determine AM/PM
            $period = ($hour >= 12) ? 'م' : 'ص';
            
            // Convert to 12-hour format
            if ($hour == 0) {
                $hour = 12;
            } elseif ($hour > 12) {
                $hour = $hour - 12;
            }

            // Format with leading zeros
            return sprintf('%02d:%02d %s', $hour, $minute, $period);
        } catch (\Exception $e) {
            return $time; // Return original if parsing fails
        }
    }
}

