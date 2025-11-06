<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Timetable;
use App\Models\Setting;
use App\Models\User;
use App\Mail\TimetableReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendTimetableReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timetable:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for upcoming timetable lessons';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $now = Carbon::now();
        
        // Get all timetable entries for today that haven't been notified yet
        $timetableEntries = Timetable::with(['student', 'teacher'])
            ->whereDate('start_date', $today)
            ->where('notification_sent', false)
            ->whereNotNull('notification_minutes')
            ->get();
        
        $sentCount = 0;
        $supportEmail = Setting::get('support_email', '');
        
        foreach ($timetableEntries as $entry) {
            // Calculate lesson start time
            $startTime = Carbon::parse($entry->start_date . ' ' . $entry->start_time);
            
            // Calculate minutes remaining until lesson
            $minutesUntilLesson = $now->diffInMinutes($startTime, false);
            
            // Send email if:
            // 1. Lesson hasn't started yet (minutesUntilLesson >= 0)
            // 2. Time remaining is less than or equal to notification_minutes
            // This means: if lesson is in 30 minutes or less, send it immediately
            if ($minutesUntilLesson >= 0 && $minutesUntilLesson <= $entry->notification_minutes) {
                // Send email to teacher
                if ($entry->teacher && $entry->teacher->email) {
                    try {
                        Mail::to($entry->teacher->email)->send(
                            new TimetableReminder($entry, $entry->notification_minutes)
                        );
                        $this->info('Sent reminder to teacher: ' . $entry->teacher->email);
                    } catch (\Exception $e) {
                        $this->error('Failed to send email to teacher: ' . $e->getMessage());
                    }
                }
                
                // Send email to support email if configured
                if ($supportEmail) {
                    try {
                        Mail::to($supportEmail)->send(
                            new TimetableReminder($entry, $entry->notification_minutes)
                        );
                        $this->info('Sent reminder to support email: ' . $supportEmail);
                    } catch (\Exception $e) {
                        $this->error('Failed to send email to support: ' . $e->getMessage());
                    }
                }
                
                // Mark as sent
                $entry->update(['notification_sent' => true]);
                $sentCount++;
            }
        }
        
        $this->info("Sent {$sentCount} reminder(s)");
        return 0;
    }
}

