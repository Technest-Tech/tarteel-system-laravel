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
        
        $this->info("=== Timetable Reminder Check ===");
        $this->info("Current time: " . $now->format('Y-m-d H:i:s'));
        $this->info("Today's date: " . $today->format('Y-m-d'));
        
        // Get all timetable entries for today that haven't been notified yet
        $timetableEntries = Timetable::with(['student', 'teacher'])
            ->whereDate('start_date', $today)
            ->where('notification_sent', false)
            ->whereNotNull('notification_minutes')
            ->get();
        
        $this->info("Found " . $timetableEntries->count() . " entries to check");
        
        $sentCount = 0;
        $supportEmail = Setting::get('support_email', '');
        $this->info("Support email: " . ($supportEmail ?: 'NOT SET'));
        
        foreach ($timetableEntries as $entry) {
            // Calculate lesson start time
            $startTime = Carbon::parse($entry->start_date . ' ' . $entry->start_time);
            
            // Calculate minutes remaining until lesson
            $minutesUntilLesson = $now->diffInMinutes($startTime, false);
            
            $this->info("\n--- Entry ID: {$entry->id} ---");
            $this->info("Start time: " . $startTime->format('Y-m-d H:i:s'));
            $this->info("Minutes until lesson: {$minutesUntilLesson}");
            $this->info("Notification minutes: {$entry->notification_minutes}");
            $this->info("Teacher ID: " . ($entry->teacher_id ?? 'NULL'));
            $this->info("Teacher loaded: " . ($entry->teacher ? 'YES' : 'NO'));
            $this->info("Teacher email: " . ($entry->teacher && $entry->teacher->email ? $entry->teacher->email : 'MISSING'));
            
            // Send email if:
            // 1. Lesson hasn't started yet (minutesUntilLesson >= 0)
            // 2. Time remaining is less than or equal to notification_minutes
            // This means: if lesson is in 30 minutes or less, send it immediately
            if ($minutesUntilLesson >= 0 && $minutesUntilLesson <= $entry->notification_minutes) {
                $this->info("✓ Condition met - should send email");
                
                // Send email to teacher
                if ($entry->teacher && $entry->teacher->email) {
                    try {
                        $this->info("Sending email to teacher: " . $entry->teacher->email);
                        Mail::to($entry->teacher->email)->send(
                            new TimetableReminder($entry, $entry->notification_minutes)
                        );
                        $this->info('✓ Sent reminder to teacher: ' . $entry->teacher->email);
                    } catch (\Exception $e) {
                        $this->error('✗ Failed to send email to teacher: ' . $e->getMessage());
                    }
                } else {
                    $this->warn('⚠ Skipping teacher email - teacher or email missing');
                }
                
                // Send email to support email if configured
                if ($supportEmail) {
                    try {
                        $this->info("Sending email to support: " . $supportEmail);
                        Mail::to($supportEmail)->send(
                            new TimetableReminder($entry, $entry->notification_minutes)
                        );
                        $this->info('✓ Sent reminder to support email: ' . $supportEmail);
                    } catch (\Exception $e) {
                        $this->error('✗ Failed to send email to support: ' . $e->getMessage());
                    }
                } else {
                    $this->warn('⚠ Skipping support email - not configured');
                }
                
                // Mark as sent
                $entry->update(['notification_sent' => true]);
                $this->info('✓ Marked entry as sent');
                $sentCount++;
            } else {
                $this->info("✗ Condition NOT met - minutesUntilLesson: {$minutesUntilLesson}, notification_minutes: {$entry->notification_minutes}");
            }
        }
        
        $this->info("\n=== Summary ===");
        $this->info("Sent {$sentCount} reminder(s)");
        return 0;
    }
}

