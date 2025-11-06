<?php

use Illuminate\Support\Facades\Route;

Route::get('login-page',[App\Http\Controllers\Controller::class,'loginPage'])->middleware('guest')->name('login.page');
Route::post('login',[App\Http\Controllers\Controller::class,'login'])->name('login');
Route::get('pay/{student_id}/{month}/{amount}/{currency}',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'pay'])->name('pay');
Route::get('pay2/{student_id}/{month}/{amount}/{currency}',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'pay2'])->name('pay2');
Route::get('success/{month}',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'success'])->name('success');

Route::get('/pay_credit/{student_id}/{amount}/{month}',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'showCredit'])->name('credit.show');
Route::get('/pay_credit_custom/{currency}/{amount}/{month}',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'showCreditCustom'])->name('credit_custom.show');
Route::get('/handle-payment',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'handlePayment'])->name('payment.handle');

Route::get('pay_bill/{student_id}/{month}',[\App\Http\Controllers\Admin\Billings\CustomBillingsController::class,'payBill'])->name('pay.bill');
Route::group(['middleware' => 'auth'], function () {
    Route::get('logout',[\App\Http\Controllers\Controller::class,'logout'])->name('logout');
    Route::get('/', [\App\Http\Controllers\Admin\Dashboard\DashboardController::class,'index'])->name('admin.dashboard');

    //courses
    Route::get('courses', [\App\Http\Controllers\Teacher\Courses\CoursesController::class,'index'])->name('courses.index');
    Route::get('courses/create', [\App\Http\Controllers\Teacher\Courses\CoursesController::class,'create'])->name('courses.create');
    Route::post('courses/store', [\App\Http\Controllers\Teacher\Courses\CoursesController::class,'store'])->name('courses.store');

    //lessons
    Route::get('course-lessons/{month}/{course_id}', [\App\Http\Controllers\Teacher\Lessons\LessonsController::class,'index'])->name('course.lessons');
    Route::get('lessons/create/{month}/{course_id}', [\App\Http\Controllers\Teacher\Lessons\LessonsController::class,'create'])->name('lessons.create');
    Route::post('course/lessons/store/{month}/{course_id}', [\App\Http\Controllers\Teacher\Lessons\LessonsController::class,'store'])->name('course.lessons.store');

    // Test email route (for testing only - remove in production)
    Route::get('admin/test-email', function() {
        try {
            $supportEmail = \App\Models\Setting::get('support_email', '');
            
            if (empty($supportEmail)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Support email is not configured. Please set it in Settings page.'
                ], 400);
            }
            
            $timetable = \App\Models\Timetable::with(['student', 'teacher'])->first();
            
            if (!$timetable) {
                return response()->json([
                    'success' => false,
                    'message' => 'No timetable entries found. Please create a timetable entry first.'
                ], 400);
            }
            
            \Illuminate\Support\Facades\Mail::to($supportEmail)->send(
                new \App\Mail\TimetableReminder($timetable, 30)
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to: ' . $supportEmail,
                'timetable_id' => $timetable->id,
                'student' => $timetable->student->user_name ?? 'N/A',
                'teacher' => $timetable->teacher->user_name ?? 'N/A'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
                'error' => $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    })->middleware('admin')->name('admin.test.email');

    Route::group(['middleware'=>'admin'],function (){

        Route::get('courses/edit/{id}', [\App\Http\Controllers\Teacher\Courses\CoursesController::class,'edit'])->name('courses.edit');
        Route::post('courses/update/{id}', [\App\Http\Controllers\Teacher\Courses\CoursesController::class,'update'])->name('courses.update');
        Route::get('courses/delete/{id}', [\App\Http\Controllers\Teacher\Courses\CoursesController::class,'delete'])->name('courses.delete');

        Route::get('lessons/edit/{id}', [\App\Http\Controllers\Teacher\Lessons\LessonsController::class,'edit'])->name('lessons.edit');
        Route::post('lessons/update/{id}', [\App\Http\Controllers\Teacher\Lessons\LessonsController::class,'update'])->name('lessons.update');
        Route::get('lessons/delete/{id}', [\App\Http\Controllers\Teacher\Lessons\LessonsController::class,'delete'])->name('lessons.delete');

        Route::resource('students', \App\Http\Controllers\Admin\Students\StudentsController::class);
        Route::put('student/update/{id}', [\App\Http\Controllers\Admin\Students\StudentsController::class,'update'])->name('student.update');
        Route::get('students/delete/{id}', [\App\Http\Controllers\Admin\Students\StudentsController::class,'delete'])->name('students.delete');

//teachers
        Route::get('teachers', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'index'])->name('teachers.index');
        Route::get('teachers/create', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'create'])->name('teachers.create');
        Route::post('teachers/store', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'store'])->name('teachers.store');
        Route::get('teachers/edit/{id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'edit'])->name('teachers.edit');
        Route::post('teachers/update/{id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'update'])->name('teachers.update');
        Route::get('teachers/delete/{id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'delete'])->name('teachers.delete');
        Route::get('teacher-remove-student/{teacher_id}/{student_id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'removeStudent'])->name('teacher.remove.student');
        Route::get('teacher-add-student/{teacher_id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'addStudent'])->name('teacher.add.student');

        //teacher courses
        Route::get('teacher-courses/{id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'teacherCourses'])->name('teacher.courses');
        Route::post('teacher-courses/store/{id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'storeTeacherCourses'])->name('teacher.courses.store');
        Route::get('teacher-course-lessons/{month}/{course_id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'teacherCourseLessons'])->name('teacher.course.lessons');
        Route::post('teacher-course-lessons/store/{month}/{course_id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'storeTeacherCourseLessons'])->name('teacher.course.lessons.store');
        Route::get('delete-teacher-course/{id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'deleteTeacherCourse'])->name('delete.teacher.course');
        Route::get('teacher-course-lessons/delete/{id}/{month}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'deleteTeacherCourseLesson'])->name('delete.teacher.course.lesson');
        Route::get('teacher-course-lessons/edit/{id}', [\App\Http\Controllers\Admin\Teachers\TeachersController::class,'editTeacherCourseLesson'])->name('edit.teacher.course.lesson');


        //billings
        Route::get('billings', [\App\Http\Controllers\Admin\Billings\BillingsController::class,'yearSelection'])->name('billings.year');
        Route::get('billings/{year}/{month}', [\App\Http\Controllers\Admin\Billings\BillingsController::class,'index'])->name('billings.index');
        Route::get('paid-billings/{year}/{month}', [\App\Http\Controllers\Admin\Billings\BillingsController::class,'paidBillings'])->name('paid.billings');
        Route::get('salaries/{year}/{month}',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'salaries'])->name('salaries.index');
        Route::get('salaries-amount/{year}/{month}',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'salariesAmount'])->name('salaries.amount');

        //custom billings
        Route::get('custom_billings', [\App\Http\Controllers\Admin\Billings\CustomBillingsController::class,'index'])->name('custom_billings.index');
        Route::post('custom_billings_store', [\App\Http\Controllers\Admin\Billings\CustomBillingsController::class,'store'])->name('custom_billings.store');

        //families
        Route::get('families', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'index'])->name('families.index');
        Route::get('families/create', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'create'])->name('families.create');
        Route::post('families/store', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'store'])->name('families.store');
        Route::get('families/edit/{id}', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'edit'])->name('families.edit');
        Route::post('families/update/{id}', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'update'])->name('families.update');
        Route::get('families/delete/{id}', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'delete'])->name('families.delete');
        Route::post('families/add-student/{family_id}', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'addStudent'])->name('families.add.student');
        Route::get('families/remove-student/{family_id}/{student_id}', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'removeStudent'])->name('families.remove.student');
        Route::get('families/{id}/{year?}/{month?}', [\App\Http\Controllers\Admin\Families\FamiliesController::class,'show'])->name('families.show');

        //calendar
        Route::get('admin/calendar', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'index'])->name('admin.calendar.index');
        Route::get('admin/calendar/delete/{id}', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'destroy'])->where('id', '.*')->name('admin.calendar.delete');
        Route::get('admin/calendar/export', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'export'])->name('admin.calendar.export');
        
        // Calendar API routes - moved from api.php to web.php for session support
        Route::get('api/calendar/events', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'events'])->name('admin.calendar.events');
        Route::post('api/calendar/lessons', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'store'])->name('admin.calendar.store');
        Route::delete('api/calendar/lessons/delete', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'destroy'])->name('admin.calendar.destroy');
        Route::put('api/calendar/lessons/{id}', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'update'])->where('id', '.*')->name('admin.calendar.update');
        Route::get('api/calendar/lessons/{id}', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'show'])->where('id', '.*')->name('admin.calendar.show');
        
        //settings
        Route::get('admin/settings', [\App\Http\Controllers\Admin\Settings\SettingsController::class,'index'])->name('admin.settings.index');
        Route::put('admin/settings', [\App\Http\Controllers\Admin\Settings\SettingsController::class,'update'])->name('admin.settings.update');
        Route::post('admin/settings/support-users', [\App\Http\Controllers\Admin\Settings\SettingsController::class,'storeSupportUser'])->name('admin.settings.storeSupportUser');
        Route::put('admin/settings/support-users/{id}', [\App\Http\Controllers\Admin\Settings\SettingsController::class,'updateSupportUser'])->name('admin.settings.updateSupportUser');
        Route::get('admin/settings/support-users/{id}/delete', [\App\Http\Controllers\Admin\Settings\SettingsController::class,'deleteSupportUser'])->name('admin.settings.deleteSupportUser');
        
        // Student timezone API routes
        Route::get('api/students/by-timezone', [\App\Http\Controllers\Admin\Students\StudentsController::class,'getStudentsByTimezone'])->name('students.byTimezone');
        Route::post('api/students/bulk-adjust-hours', [\App\Http\Controllers\Admin\Students\StudentsController::class,'bulkAdjustHours'])->name('students.bulkAdjustHours');
    });

    //teacher calendar (read-only)
    Route::get('teacher/calendar', [\App\Http\Controllers\Teacher\Calendar\CalendarController::class,'index'])->name('teacher.calendar.index');
    Route::get('api/teacher/calendar/events', [\App\Http\Controllers\Teacher\Calendar\CalendarController::class,'events'])->name('teacher.calendar.events');
    Route::get('api/teacher/calendar/lessons/{id}', [\App\Http\Controllers\Teacher\Calendar\CalendarController::class,'show'])->where('id', '.*')->name('teacher.calendar.show');
    Route::get('teacher/calendar/export', [\App\Http\Controllers\Teacher\Calendar\CalendarController::class,'export'])->name('teacher.calendar.export');
    
    //support calendar (read-only)
    Route::get('support/calendar', [\App\Http\Controllers\Support\Calendar\CalendarController::class,'index'])->name('support.calendar.index');
    Route::get('api/support/calendar/events', [\App\Http\Controllers\Support\Calendar\CalendarController::class,'events'])->name('support.calendar.events');
    Route::get('api/support/calendar/lessons/{id}', [\App\Http\Controllers\Support\Calendar\CalendarController::class,'show'])->where('id', '.*')->name('support.calendar.show');
    Route::get('support/calendar/export', [\App\Http\Controllers\Support\Calendar\CalendarController::class,'export'])->name('support.calendar.export');
});
Route::get('pay-family/{family_id}/{month}/{amount}/{currency}',[\App\Http\Controllers\Admin\Billings\BillingsController::class,'pay2'])->name('pay.family');
