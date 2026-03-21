<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-wide customizer-hide"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{ asset('assets/') }}"
    data-template="vertical-menu-template-free"
>
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Reset Password - WADMS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            position: relative;
            background-image: url('{{ asset('assets/img/wdms/pit-img.jpg') }}');
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            background-attachment: fixed;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.60);
            z-index: 0;
        }

        .container-xxl {
            position: relative;
            z-index: 1;
        }

        .card {
            background: rgba(255, 255, 255, 0.97) !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4) !important;
        }
    </style>

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner auth-box">
                <div class="card">
                    <div class="card-body">

                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-4">
                            <a href="{{ url('/') }}" class="app-brand-link">
                                <span class="app-brand-logo demo">
                                    <img src="{{ asset('assets/img/wdms/pit-logo-outlined.png') }}"
                                         alt="Logo"
                                         class="w-px-50 h-auto" />
                                </span>
                                <span class="app-brand-text demo menu-text fw-bold ms-2 text-uppercase">
                                    WADMS
                                </span>
                            </a>
                        </div>

                        <h4 class="mb-1 text-center fw-bold">Set New Password</h4>
                        <p class="mb-4 text-center text-muted">
                            Your new password must be at least 8 characters.
                        </p>

                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="alert alert-success mb-3">{{ session('status') }}</div>
                        @endif

                        <form method="POST" action="{{ route('password.store') }}" id="reset-pw-app">
                            @csrf

                            <!-- Hidden token + email -->
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email"
                                       type="email"
                                       name="email"
                                       value="{{ old('email', $request->email) }}"
                                       required
                                       autofocus
                                       autocomplete="username"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="Enter your email" />
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group input-group-merge">
                                    <input id="password"
                                           :type="showPassword ? 'text' : 'password'"
                                           name="password"
                                           required
                                           autocomplete="new-password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Min. 8 characters" />
                                    <span class="input-group-text cursor-pointer" @click="showPassword = !showPassword">
                                        <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                                    </span>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group input-group-merge">
                                    <input id="password_confirmation"
                                           :type="showConfirm ? 'text' : 'password'"
                                           name="password_confirmation"
                                           required
                                           autocomplete="new-password"
                                           class="form-control"
                                           placeholder="Re-enter password" />
                                    <span class="input-group-text cursor-pointer" @click="showConfirm = !showConfirm">
                                        <i :class="showConfirm ? 'bx bx-show' : 'bx bx-hide'"></i>
                                    </span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary d-grid w-100">
                                Reset Password
                            </button>
                        </form>

                        <p class="text-center mt-3">
                            <a href="{{ route('login') }}">
                                <i class="bx bx-chevron-left me-1"></i> Back to Login
                            </a>
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/vue.js') }}"></script>

    <script>
    new Vue({
        el: '#reset-pw-app',
        data: {
            showPassword: false,
            showConfirm:  false,
        }
    });
    </script>
</body>
</html>