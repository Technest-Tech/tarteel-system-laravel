<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    use HasFactory;
    protected $table = 'courses';
    protected $fillable = ['course_name','teacher_id','student_id'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
