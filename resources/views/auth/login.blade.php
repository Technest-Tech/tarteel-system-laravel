<!DOCTYPE html>
<html lang="en">
<head>
    <title> أكاديمية ترتيل </title>

    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Webestica.com">
    <meta name="description" content="Eduport- LMS, Education and Course Theme">

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap">

    <!-- Plugins CSS -->
    <link rel="stylesheet" type="text/css" href="assets/vendor/font-awesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">

    <!-- Theme CSS -->
    <link id="style-switch" rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">

    <style>
        .almarai-bold {
            font-family: "Almarai", sans-serif;
            font-weight: 700;
            font-style: normal;
        }
    </style>
</head>

<body>

<!-- **************** MAIN CONTENT START **************** -->
<main>
    <section class="p-0 d-flex align-items-center position-relative overflow-hidden" style="direction: rtl">

        <div class="container-fluid">
            <div class="row">
                <!-- Right -->
                <div class="col-12 col-lg-4 m-auto">
                    <div class="row my-5">
                        <div class="col-sm-6 col-xl-6 m-auto">
                            <!-- Title -->
                             <div>
                                <img src="{{asset('lg.png')}}">
                            </div>
                            <h1 class="fs-2 text-center almarai-bold">تسجيل الدخول</h1>
                            @if(session('error'))
                            <div class="alert alert-danger mt-2">{{ session('error') }}</div>
                            @endif
                            <!-- Form START -->
                            <form action="{{route('login')}}" method="post">
                                @csrf
                                <!-- Email -->
                                <div class="mb-4">
                                    <label for="exampleInputEmail1" class="form-label almarai-bold">البريد الالكتروني</label>
                                    <div class="input-group input-group-lg">
                                        <input type="email" name="email" class="form-control border-0 bg-light rounded-end ps-1 almarai-bold" placeholder="البريد الالكتروني" id="exampleInputEmail1">
                                    </div>
                                    @error('email')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Password -->
                                <div class="mb-4">
                                    <label for="inputPassword5" class="form-label almarai-bold">الرقم السري</label>
                                    <div class="input-group input-group-lg">
                                        <input type="password" name="password" class="form-control border-0 bg-light rounded-end ps-1 almarai-bold" placeholder="الرقم السري" id="inputPassword5">
                                    </div>
                                    @error('password')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Button -->
                                <div class="align-items-center mt-0">
                                    <div class="d-grid">
                                        <button class="btn btn-primary mb-0 almarai-bold" type="submit">تسجيل الدخول</button>
                                    </div>
                                </div>
                            </form>
                            <!-- Form END -->

                        </div>
                    </div> <!-- Row END -->
                </div>
            </div> <!-- Row END -->
        </div>
    </section>
</main>
<!-- **************** MAIN CONTENT END **************** -->

<!-- Back to top -->
<div class="back-top"><i class="bi bi-arrow-up-short position-absolute top-50 start-50 translate-middle"></i></div>

<!-- Bootstrap JS -->
<script src="assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<!-- Template Functions -->
<script src="assets/js/functions.js"></script>

</body>
</html>
