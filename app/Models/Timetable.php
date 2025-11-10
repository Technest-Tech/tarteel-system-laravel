<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $table = 'timetable';
    
    protected $fillable = [
        'series_id',
        'student_id',
        'teacher_id',
        'day',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'lesson_name',
        'color',
        'notification_minutes',
        'notification_sent',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}

