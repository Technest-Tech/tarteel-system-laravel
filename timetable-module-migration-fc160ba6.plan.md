<!-- fc160ba6-d256-47cd-970d-c1aece40857f 1aee51eb-4c4f-40f9-b14e-7b55651a94bb -->
# Timetable Module Migration Plan

This plan provides step-by-step instructions to migrate the complete timetable/calendar module from the current system to another copy, including all features, design, and functionality.

## Overview

The timetable module consists of:

- **Admin Calendar**: Full CRUD operations with PDF export, filters, and event management
- **Teacher Calendar**: Read-only view of assigned lessons and timetable entries
- **Database**: New `timetable` table and updates to `lessons` table
- **FullCalendar Integration**: Interactive calendar with multiple views (month, week, day)

## Step 1: Database Migrations

### 1.1 Create Timetable Table Migration

Create file: `database/migrations/YYYY_MM_DD_HHMMSS_create_timetable_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('timetable', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('teacher_id');
            $table->tinyInteger('day'); // 0=Sunday, 1=Monday, ..., 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->date('start_date'); // When the schedule starts
            $table->date('end_date'); // When the schedule ends
            $table->string('lesson_name')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('timetable');
    }
};
```

### 1.2 Add Time Fields to Lessons Table Migration

Create file: `database/migrations/YYYY_MM_DD_HHMMSS_add_time_fields_to_lessons_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('lesson_date');
            $table->time('end_time')->nullable()->after('start_time');
        });
    }

    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
```

**Note**: Replace `YYYY_MM_DD_HHMMSS` with actual timestamp. Run migrations: `php artisan migrate`

## Step 2: Model Creation

### 2.1 Create Timetable Model

Create file: `app/Models/Timetable.php`

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $table = 'timetable';
    
    protected $fillable = [
        'student_id',
        'teacher_id',
        'day',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'lesson_name',
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
```

## Step 3: Controller Creation

### 3.1 Admin Calendar Controller

Create directory: `app/Http/Controllers/Admin/Calendar/`

Create file: `app/Http/Controllers/Admin/Calendar/CalendarController.php`

Copy the entire controller from the current system (447 lines). Key methods:

- `index()` - Display calendar view with form
- `events()` - Get events for calendar (JSON API)
- `store()` - Store timetable entries
- `update()` - Update a single day event
- `destroy()` - Delete timetable entry or lesson
- `show()` - Get single timetable entry details
- `export()` - Export calendar events to PDF
- `getEventColor()` - Get color for event based on student ID

### 3.2 Teacher Calendar Controller

Create directory: `app/Http/Controllers/Teacher/Calendar/`

Create file: `app/Http/Controllers/Teacher/Calendar/CalendarController.php`

Copy the entire controller from the current system (263 lines). Key methods:

- `index()` - Display read-only calendar view
- `events()` - Get events filtered by teacher (JSON API)
- `show()` - Get single timetable entry or lesson details (read-only)
- `getEventColor()` - Get color for event based on student ID

## Step 4: View Files

### 4.1 Admin Calendar Index View

Create directory: `resources/views/admin/calendar/`

Create file: `resources/views/admin/calendar/index.blade.php`

Copy the entire view file (328 lines). This includes:

- Calendar display with FullCalendar
- Create lesson modal with 7-day schedule form
- Edit lesson modal
- Lesson detail modal
- Export date range modal
- Filter section (student/teacher)
- Export PDF dropdown

### 4.2 Admin Calendar PDF Report View

Create file: `resources/views/admin/calendar/pdf-report.blade.php`

Copy the entire PDF template (142 lines). This is used for PDF export with Arabic RTL support.

### 4.3 Teacher Calendar Index View

Create directory: `resources/views/teacher/calendar/`

Create file: `resources/views/teacher/calendar/index.blade.php`

Copy the entire view file (67 lines). This is a read-only calendar view for teachers.

## Step 5: JavaScript Files

### 5.1 Admin Calendar JavaScript

Create file: `public/js/calendar-admin.js`

Copy the entire JavaScript file (756 lines). This handles:

- FullCalendar initialization
- Event fetching with filters
- Form submission for creating schedules
- Edit/delete functionality
- Export functionality
- Modal management
- Event display customization

### 5.2 Teacher Calendar JavaScript

Create file: `public/js/calendar-teacher.js`

Copy the entire JavaScript file (348 lines). This handles:

- Read-only FullCalendar initialization
- Event fetching (teacher-filtered)
- Event detail display
- Loading states

## Step 6: CSS File

### 6.1 Calendar Custom CSS

Create file: `public/assets/css/calendar-custom.css`

Copy the entire CSS file (754 lines). This includes:

- FullCalendar customization
- Event styling
- Modal styles
- Form styles
- Responsive design
- RTL support
- Loading overlay styles

## Step 7: Routes Configuration

### 7.1 Web Routes

Edit file: `routes/web.php`

Add inside the admin middleware group (around line 79):

```php
//calendar
Route::get('admin/calendar', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'index'])->name('admin.calendar.index');
Route::get('admin/calendar/delete/{id}', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'destroy'])->where('id', '.*')->name('admin.calendar.delete');
Route::get('admin/calendar/export', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'export'])->name('admin.calendar.export');
```

Add teacher calendar routes (outside admin group, around line 85):

```php
//teacher calendar (read-only)
Route::get('teacher/calendar', [\App\Http\Controllers\Teacher\Calendar\CalendarController::class,'index'])->name('teacher.calendar.index');
Route::get('api/teacher/calendar/events', [\App\Http\Controllers\Teacher\Calendar\CalendarController::class,'events'])->name('teacher.calendar.events');
Route::get('api/teacher/calendar/lessons/{id}', [\App\Http\Controllers\Teacher\Calendar\CalendarController::class,'show'])->name('teacher.calendar.show');
```

### 7.2 API Routes

Edit file: `routes/api.php`

Add inside the authenticated API routes group (around line 25):

```php
// Calendar API routes - using web middleware for session-based auth
Route::get('calendar/events', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'events'])->name('admin.calendar.events');
Route::post('calendar/lessons', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'store'])->name('admin.calendar.store');
Route::delete('calendar/lessons/delete', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'destroy'])->name('admin.calendar.destroy');
Route::put('calendar/lessons/{id}', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'update'])->where('id', '.*')->name('admin.calendar.update');
Route::get('calendar/lessons/{id}', [\App\Http\Controllers\Admin\Calendar\CalendarController::class,'show'])->where('id', '.*')->name('admin.calendar.show');
```

**Note**: Ensure API routes use web middleware for session-based authentication if needed.

## Step 8: Dependencies

### 8.1 Composer Package

The module requires `mpdf` for PDF generation. Add to `composer.json`:

```json
"require": {
    "carlos-meneses/laravel-mpdf": "^2.1"
}
```

Run: `composer install` or `composer update`

### 8.2 Frontend Dependencies

The module uses FullCalendar v6.1.10 loaded via CDN in the views. No npm installation needed.

## Step 9: Navigation/Menu Integration

### 9.1 Admin Sidebar

Add calendar link to admin sidebar menu (typically in `resources/views/layouts/sidebar.blade.php`):

```php
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.calendar.index') }}">
        <i class="fas fa-calendar-alt"></i>
        <span>تقويم الحصص</span>
    </a>
</li>
```

### 9.2 Teacher Sidebar

Add calendar link to teacher sidebar menu:

```php
<li class="nav-item">
    <a class="nav-link" href="{{ route('teacher.calendar.index') }}">
        <i class="fas fa-calendar-alt"></i>
        <span>تقويم الحصص</span>
    </a>
</li>
```

## Step 10: Testing Checklist

After migration, test the following:

1. **Database**

   - [ ] Run migrations successfully
   - [ ] Verify `timetable` table structure
   - [ ] Verify `lessons` table has `start_time` and `end_time` columns

2. **Admin Calendar**

   - [ ] Access admin calendar page
   - [ ] Create new timetable entry with date range
   - [ ] View events in month/week/day views
   - [ ] Filter by student/teacher
   - [ ] Edit a single day event
   - [ ] Delete a timetable entry
   - [ ] Export PDF (today/week/month/custom range)
   - [ ] Verify event colors and display

3. **Teacher Calendar**

   - [ ] Access teacher calendar page
   - [ ] View only assigned lessons/timetable entries
   - [ ] Click event to view details (read-only)
   - [ ] Verify no edit/delete buttons appear

4. **Functionality**

   - [ ] Verify recurring schedule creation (7 days)
   - [ ] Verify time validation (end > start)
   - [ ] Verify date range validation
   - [ ] Verify Arabic RTL display
   - [ ] Verify PDF export formatting

## Important Notes

1. **File Paths**: Ensure all file paths match exactly as specified
2. **Namespace**: Verify namespace matches your application structure
3. **User Model**: Ensure `User` model has `USER_TYPE` constants for student/teacher
4. **Authentication**: Ensure routes are protected with appropriate middleware
5. **Permissions**: Verify admin/teacher middleware is properly configured
6. **Carbon**: The module uses Carbon for date handling (usually included in Laravel)
7. **FullCalendar**: Uses CDN version 6.1.10 - ensure internet connection or host locally
8. **SweetAlert**: Admin calendar uses SweetAlert2 for notifications (ensure it's included in layout)

## File Summary

**Total Files to Create/Copy:**

- 2 Database migrations
- 1 Model
- 2 Controllers
- 3 Views
- 2 JavaScript files
- 1 CSS file
- Route updates (2 files)

**Total Lines of Code:** ~3,500+ lines

## Design Consistency

All design elements are included:

- Arabic RTL support
- Bootstrap 5 styling
- Custom color scheme (#066ac9 primary color)
- Responsive design
- Loading overlays
- Modal animations
- Event color coding by student
- FullCalendar Arabic locale

Ensure your target system has the same base layout structure (`layouts.index`) and includes Bootstrap 5, Font Awesome, and SweetAlert2.

### To-dos

- [ ] Create and run database migrations: timetable table and lessons time fields
- [ ] Create Timetable model with relationships to User (student/teacher)
- [ ] Create Admin CalendarController with all CRUD methods, PDF export, and event management
- [ ] Create Teacher CalendarController with read-only event viewing
- [ ] Create admin calendar views: index.blade.php and pdf-report.blade.php
- [ ] Create teacher calendar view: index.blade.php (read-only)
- [ ] Copy calendar-admin.js and calendar-teacher.js to public/js/
- [ ] Copy calendar-custom.css to public/assets/css/
- [ ] Add calendar routes to web.php and api.php with proper middleware
- [ ] Install mpdf package via composer and verify FullCalendar CDN access
- [ ] Add calendar menu items to admin and teacher sidebars
- [ ] Test all functionality: create, edit, delete, filter, export PDF, and teacher view