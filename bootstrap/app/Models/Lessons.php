<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lessons extends Model
{
    use HasFactory;
    protected $table = 'lessons';
    protected $fillable = ['lesson_name','course_id','teacher_id','student_id','lesson_date','lesson_duration'];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
