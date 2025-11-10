<!-- Sidebar START -->
<style>
    .bg-red {
        background-color: #05303f !important;
    }
</style>
<nav class="navbar sidebar navbar-expand-xl navbar-dark bg-red">

    <!-- Navbar brand for xl START -->
    <div class="d-flex align-items-center">
        <a class="navbar-brand" href="{{url('/')}}">
            <h4 style="font-family: Calibri;font-weight: bold;color: whitesmoke">أكاديمية ترتيل </h4>
        </a>
    </div>
    <!-- Navbar brand for xl END -->

    <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-body sidebar-content d-flex flex-column bg-red">

            @if(auth()->user()->user_type == \App\Models\User::USER_TYPE['admin'])
                <!-- Sidebar menu START -->
                <ul class="navbar-nav flex-column" id="navbar-sidebar">

                    <!-- Menu item 1 -->
                    <li class="nav-item"><a href="{{url('/')}}" class="nav-link @if(request()->is('/')) active @endif"><i class="bi bi-house fa-fw me-2"></i>صفحة الاحصائيات</a></li>

                    <!-- Title -->
                    <li class="nav-item ms-2 my-2">الصفحات</li>

                    <!-- Menu item 3 -->
                    <li class="nav-item"> <a class="nav-link @if(request()->is('students')) active @endif" href="{{route('students.index')}}"><i class="fas fa-user-graduate fa-fw me-2"></i>الطلاب</a></li>

                    <!-- Menu item 5 -->
                    <li class="nav-item"> <a class="nav-link @if(request()->is('teachers')) active @endif" href="{{route('teachers.index')}}"><i class="fas fa-user-tie fa-fw me-2"></i>المعلمين</a></li>

                    <li class="nav-item"> <a class="nav-link @if(request()->is('families*')) active @endif" href="{{route('families.index')}}"><i class="fas fa-users fa-fw me-2"></i>العائلات</a></li>

                    <li class="nav-item"> <a class="nav-link @if(request()->is('billings')) active @endif" href="{{route('billings.year')}}"><i class="fas fa-dollar-sign fa-fw me-2"></i>الفواتير </a></li>
                    <li class="nav-item"> <a class="nav-link @if(request()->is('salaries')) active @endif" href="{{route('salaries.index',[date('Y'),1])}}"><i class="fas fa-file fa-fw me-2"></i>المرتبات </a></li>

                    <li class="nav-item"> <a class="nav-link @if(request()->is('custom_billings')) active @endif" href="{{route('custom_billings.index')}}"><i class="fas fa-file fa-fw me-2"></i>الفواتيير اليدوية </a></li>

                    <li class="nav-item"> <a class="nav-link @if(request()->is('admin/timetables*')) active @endif" href="{{route('admin.timetables.index')}}"><i class="fas fa-calendar-week fa-fw me-2"></i>جداول الطلاب</a></li>

                    <li class="nav-item"> <a class="nav-link @if(request()->is('admin/calendar*')) active @endif" href="{{route('admin.calendar.index')}}"><i class="fas fa-calendar-alt fa-fw me-2"></i>تقويم الحصص</a></li>

                    <li class="nav-item"> <a class="nav-link @if(request()->is('admin/settings*')) active @endif" href="{{route('admin.settings.index')}}"><i class="fas fa-cog fa-fw me-2"></i>الإعدادات</a></li>

                    <li class="nav-item"> <a class="nav-link" href="{{route('logout')}}"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a></li>

                </ul>
                <!-- Sidebar menu end -->
            @elseif(auth()->user()->user_type == \App\Models\User::USER_TYPE['teacher'])
                <ul class="navbar-nav flex-column" id="navbar-sidebar">

                    <!-- Menu item 1 -->
                    <li class="nav-item"><a href="{{url('/')}}" class="nav-link @if(request()->is('/')) active @endif"><i class="bi bi-house fa-fw me-2"></i>صفحة الاحصائيات</a></li>

                    <!-- Title -->
                    <li class="nav-item ms-2 my-2">الصفحات</li>

                    <!-- Menu item 3 -->
                    <li class="nav-item"> <a class="nav-link @if(request()->is('courses')) active @endif" href="{{route('courses.index')}}"><i class="fas fa-laptop fa-fw me-2"></i>الدورات</a></li>

                    <li class="nav-item"> <a class="nav-link @if(request()->is('teacher/calendar*')) active @endif" href="{{route('teacher.calendar.index')}}"><i class="fas fa-calendar-alt fa-fw me-2"></i>تقويم الحصص</a></li>

                    <li class="nav-item"> <a class="nav-link" href="{{route('logout')}}"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a></li>


                </ul>
            @elseif(auth()->user()->user_type == \App\Models\User::USER_TYPE['support'])
                <ul class="navbar-nav flex-column" id="navbar-sidebar">

                    <!-- Menu item 1 -->
                    <li class="nav-item"><a href="{{url('/')}}" class="nav-link @if(request()->is('/')) active @endif"><i class="bi bi-house fa-fw me-2"></i>صفحة الاحصائيات</a></li>

                    <!-- Title -->
                    <li class="nav-item ms-2 my-2">الصفحات</li>

                    <!-- Menu item 3 -->
                    <li class="nav-item"> <a class="nav-link @if(request()->is('support/calendar*')) active @endif" href="{{route('support.calendar.index')}}"><i class="fas fa-calendar-alt fa-fw me-2"></i>تقويم الحصص</a></li>

                    <li class="nav-item"> <a class="nav-link" href="{{route('logout')}}"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a></li>

                </ul>
            @endif


        </div>
    </div>
</nav>
<!-- Sidebar END -->
