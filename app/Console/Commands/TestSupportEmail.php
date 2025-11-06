<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Mail\TimetableReminder;
use App\Models\Timetable;
use Illuminate\Support\Facades\Mail;

class TestSupportEmail extends Command
{
    protected $signature = 'test:support-email';
    protected $description = 'Test sending email to support email address';

    public function handle()
    {
        $supportEmail = Setting::get('support_email', '');
        
        if (empty($supportEmail)) {
            $this->error('Support email is not configured. Please set it in Settings page.');
            return 1;
        }

        $this->info('Support email: ' . $supportEmail);
        
        // Get a sample timetable entry for testing
        $timetable = Timetable::with(['student', 'teacher'])->first();
        
        if (!$timetable) {
            $this->error('No timetable entries found. Please create a timetable entry first.');
            return 1;
        }

        $this->info('Using timetable entry ID: ' . $timetable->id);
        $this->info('Student: ' . ($timetable->student->user_name ?? 'N/A'));
        $this->info('Teacher: ' . ($timetable->teacher->user_name ?? 'N/A'));

        try {
            $this->info('Attempting to send email...');
            Mail::to($supportEmail)->send(
                new TimetableReminder($timetable, 30)
            );
            
            $this->info('✅ Test email sent successfully to: ' . $supportEmail);
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->error('Error details: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }
}

