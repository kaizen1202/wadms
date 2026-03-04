@php
    use App\Enums\UserType;
    $admin = UserType::ADMIN;
    $dean = UserType::DEAN;
    $taskForce = UserType::TASK_FORCE;
    $internalAssessor = UserType::INTERNAL_ASSESSOR;
    $accreditor = UserType::ACCREDITOR;
@endphp

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

    <title>Register - CGS</title>
    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet"
    />

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
        /* Expand auth card width for two-column layout */
        .auth-wide {
            max-width: 900px !important;
        }

        .text-gold {
            color: #D4AF37;
        }

        @media (max-width: 991.98px) {
            .auth-wide {
                max-width: 100% !important;
            }
        }

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

        /* Dark overlay — matches hero's bg-black/60 */
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

        /* Solid card so form stays readable */
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
        <div class="authentication-inner auth-wide">
            <!-- Register Card -->
            <div class="card">
                <div class="card-body">
                    <div class="row g-0">

                        <!-- LEFT COLUMN : INFO PANEL -->
                        <div class="col-lg-5 d-none d-lg-flex align-items-center border-end">
                            <div class="p-4 w-100">

                                <div class="app-brand mb-4">
                                    <a href="{{ url('/') }}" class="app-brand-link">
                                        <span class="app-brand-logo demo">
                                            <img src="{{ asset('assets/img/wdms/pit-logo-outlined.png') }}"
                                                alt="Logo"
                                                class="w-px-100 h-auto" />
                                        </span>
                                        <span class="fw-bold ms-2 text-gold">
                                            Palompon Institute of Technology
                                        </span>
                                    </a>
                                </div>

                                <h5 class="fw-bold mb-3">
                                    Accreditation Document Management System
                                </h5>

                                <p class="text-muted mb-4">
                                    This platform is used for internal accreditation processes.
                                    All registrations are subject to administrative approval.
                                </p>

                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-primary me-2"></i>
                                        Provide accurate credentials
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-primary me-2"></i>
                                        Request an appropriate role
                                    </li>
                                    <li class="mb-2">
                                        <i class="bx bx-check-circle text-primary me-2"></i>
                                        Await for approval
                                    </li>
                                </ul>

                                <div class="alert alert-info small mt-4">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Approved users will granted access via their registered email.
                                </div>

                            </div>
                        </div>

                        <!-- RIGHT COLUMN : FORM -->
                        <div class="col-lg-7">
                            <div class="p-4">

                                <h4 class="mb-2 fw-bold text-center text-lg-start">
                                    Create an Account
                                </h4>
                                <p class="mb-4 text-muted text-center text-lg-start">
                                    Your account will be reviewed before activation.
                                </p>

                                @if (session('status'))
                                    <div class="alert alert-success mb-3">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('register') }}">
                                    @csrf

                                    <!-- Name -->
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text"
                                            name="name"
                                            value="{{ old('name') }}"
                                            required
                                            class="form-control @error('name') is-invalid @enderror">
                                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            class="form-control @error('email') is-invalid @enderror">
                                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Role -->
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role"
                                                required
                                                class="form-select @error('role') is-invalid @enderror">
                                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select your role</option>
                                            <option value="{{ $taskForce }}" {{ old('role') == $taskForce ? 'selected' : '' }}>Task Force</option>
                                            <option value="{{ $internalAssessor }}" {{ old('role') == $internalAssessor ? 'selected' : '' }}>Internal Assessor</option>
                                            <option value="{{ $accreditor }}" {{ old('role') == $accreditor ? 'selected' : '' }}>Accreditor</option>
                                        </select>
                                        @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Password + Confirm (Vue-powered toggle) -->
                                    <div id="register-password-app">

                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <div class="input-group input-group-merge has-validation">
                                                <input :type="showPassword ? 'text' : 'password'"
                                                    name="password"
                                                    value="{{ old('password') }}"
                                                    required
                                                    class="form-control @error('password') is-invalid @enderror">
                                                <span class="input-group-text cursor-pointer" @click="showPassword = !showPassword">
                                                    <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                                                </span>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="text-muted">
                                                At least 8 characters long and include a letter and a number.
                                            </small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Confirm Password</label>
                                            <div class="input-group input-group-merge">
                                                <input :type="showConfirm ? 'text' : 'password'"
                                                    name="password_confirmation"
                                                    value="{{ old('password_confirmation') }}"
                                                    required
                                                    class="form-control">
                                                <span class="input-group-text cursor-pointer" @click="showConfirm = !showConfirm">
                                                    <i :class="showConfirm ? 'bx bx-show' : 'bx bx-hide'"></i>
                                                </span>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Terms -->
                                    <div class="mb-3 form-check">
                                        <input class="form-check-input" type="checkbox" required>
                                        <label class="form-check-label">
                                            I agree to the
                                            <a href="{{ route('privacy') }}">Privacy Policy</a> and
                                            <a href="{{ route('terms') }}">Terms & Conditions</a>
                                        </label>
                                    </div>

                                    <button class="btn btn-primary w-100">
                                        Submit Registration Request
                                    </button>
                                </form>

                                <p class="text-center mt-3">
                                    Already have an account?
                                    <a href="{{ route('login') }}">Sign in</a>
                                </p>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- /Register Card -->
        </div>
    </div>
</div>

<!-- Core JS -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>

<!-- Vue.js CDN -->
<script src="{{ asset('assets/js/vue.js') }}"></script>

<!-- Password toggle via Vue -->
<script>
new Vue({
    el: '#register-password-app',
    data: {
        showPassword: false,
        showConfirm: false
    }
});
</script>

</body>
</html>