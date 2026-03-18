@php
    use App\Enums\UserType;
    use App\Enums\UserStatus;

    $user = auth()->user();
    $currentRole = $user->currentRole?->name;
    $isActive = $user->status === UserStatus::ACTIVE->value;

    // Map roles to sidebar colors
    $roleColors = [
        UserType::ADMIN->value => '#eb9c00ff',
        UserType::DEAN->value => '#50C878',
        UserType::TASK_FORCE->value => '#006241',
        UserType::INTERNAL_ASSESSOR->value => '#679267',
        UserType::ACCREDITOR->value => '#004953'
    ];

    $sidebarColor = $roleColors[$currentRole] ?? '#343a40';
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu" ref="sidebar"

       style="background-color: {{ $sidebarColor }}; color: #fff;">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/wdms/pit-logo-outlined.png') }}"
                     alt="Pit Logo"
                     class="w-px-50 h-auto" />
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2 text-uppercase" style="color:#fff;">WADMS</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none" @click.prevent="toggleSidebar">
            <i class="bx bx-chevron-left bx-sm align-middle" style="color:#fff;"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        {{-- ================= NOT ACTIVE ================= --}}
        @if (!$isActive)
            <li class="menu-item disabled">
                <span class="menu-link text-muted">
                    <i class="menu-icon tf-icons bx bx-lock"></i>
                    <div>
                        @switch($user->status)
                            @case('Pending') Account Under Review @break
                            @case('Inactive') Account Inactive @break
                            @case('Suspended') Account Suspended @break
                            @default Account Not Active
                        @endswitch
                    </div>
                </span>
            </li>

        {{-- ================= ADMIN ================= --}}
        @elseif ($isActive && $currentRole === UserType::ADMIN->value)
            <li class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('users.*') || Route::is('role-requests.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-user-check"></i>
                    <div>Internal Assessors & Accreditors</div>
                    @if ($unverifiedCount > 0 || $pendingRoleRequestCount > 0)
                        <span class="badge bg-warning rounded-pill ms-auto">!</span>
                    @endif
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('users.index') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="menu-link" style="color:#fff;">Pending Accounts
                            @if ($unverifiedCount > 0)
                                <span class="badge bg-warning rounded-pill ms-auto">{{ $unverifiedCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('users.taskforce.index') ? 'active' : '' }}">
                        <a href="{{ route('users.taskforce.index') }}" class="menu-link" style="color:#fff;">Active Accounts</a>
                    </li>
                    <li class="menu-item {{ Route::is('role-requests.*') ? 'active' : '' }}">
                        <a href="{{ route('role-requests.index') }}" class="menu-link" style="color:#fff;">Role Requests
                            @if ($pendingRoleRequestCount > 0)
                                <span class="badge bg-warning rounded-pill ms-auto">{{ $pendingRoleRequestCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item {{ Route::is('admin.accreditation.*') ? 'active' : '' }}">
                <a href="{{ route('admin.accreditation.index') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-badge-check"></i>
                    <div>Accreditation</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('program.areas.*') ? 'active' : '' }}">
                <a href="{{ route('program.areas.evaluations') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div>Evaluations</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('archive.*') ? 'active' : '' }}">
                <a href="{{ route('archive.index') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-folder"></i>
                    <div>Archive</div>
                </a>
            </li>

        {{-- ================= DEAN ================= --}}
        @elseif ($isActive && $currentRole === UserType::DEAN->value)
            <li class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('users.*') || Route::is('role-requests.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-user-check"></i>
                    <div>Task Forces</div>
                    @if ($unverifiedCount > 0 || $pendingRoleRequestCount > 0)
                        <span class="badge bg-warning rounded-pill ms-auto">!</span>
                    @endif
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Route::is('users.index') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="menu-link" style="color:#fff;">
                            Pending Accounts
                            @if ($unverifiedCount > 0)
                                <span class="badge bg-warning rounded-pill ms-auto">{{ $unverifiedCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="menu-item {{ Route::is('users.taskforce.index') ? 'active' : '' }}">
                        <a href="{{ route('users.taskforce.index') }}" class="menu-link" style="color:#fff;">Active Accounts</a>
                    </li>
                    <li class="menu-item {{ Route::is('role-requests.*') ? 'active' : '' }}">
                        <a href="{{ route('role-requests.index') }}" class="menu-link" style="color:#fff;">
                            Role Requests
                            @if ($pendingRoleRequestCount > 0)
                                <span class="badge bg-warning rounded-pill ms-auto">{{ $pendingRoleRequestCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item {{ Route::is('admin.accreditation.*') ? 'active' : '' }}">
                <a href="{{ route('admin.accreditation.index') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-badge-check"></i>
                    <div>Accreditation</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('program.areas.*') ? 'active' : '' }}">
                <a href="{{ route('program.areas.evaluations') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div>Evaluations</div>
                </a>
            </li>

        {{-- ================= TASK FORCE ================= --}}
        @elseif ($isActive && $currentRole === UserType::TASK_FORCE->value)
            <li class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('admin.accreditation.*') ? 'active' : '' }}">
                <a href="{{ route('admin.accreditation.index') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-badge-check"></i>
                    <div>Accreditation</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('program.areas.*') ? 'active' : '' }}">
                <a href="{{ route('program.areas.evaluations') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div>Evaluations</div>
                </a>
            </li>

        {{-- ================= INTERNAL ASSESSOR ================= --}}
        @elseif ($isActive && $currentRole === UserType::INTERNAL_ASSESSOR->value)
            <li class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('internal-accessor.*') ? 'active' : '' }}">
                <a href="{{ route('internal-accessor.index') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-badge-check"></i>
                    <div>Accreditation</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('program.areas.*') ? 'active' : '' }}">
                <a href="{{ route('program.areas.evaluations') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div>Evaluations</div>
                </a>
            </li>

        {{-- ================= ACCREDITOR ================= --}}
        @elseif ($isActive && $currentRole === UserType::ACCREDITOR->value)
            <li class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div>Dashboard</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('internal-accessor.*') ? 'active' : '' }}">
                <a href="{{ route('internal-accessor.index') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-badge-check"></i>
                    <div>Accreditation</div>
                </a>
            </li>

            <li class="menu-item {{ Route::is('program.areas.*') ? 'active' : '' }}">
                <a href="{{ route('program.areas.evaluations') }}" class="menu-link" style="color:#fff;">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div>Evaluations</div>
                </a>
            </li>

        @endif
    </ul>

    {{-- Profile Footer --}}
    <div class="menu-footer mt-auto p-3" style="border-top: 1px solid rgba(255,255,255,0.2);">
        <div class="d-flex align-items-center gap-3">
            {{-- Avatar --}}
            <x-initials-avatar :user="$user">

            </x-initials-avatar>

            {{-- Name and role --}}
            <div class="flex-grow-1 text-truncate">
                <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                <small class="opacity-75">{{ $currentRole }}</small>
            </div>

            {{-- Dropdown for profile, role switch, logout --}}
            <div class="dropdown">
                <button class="btn btn-sm btn-link text-white p-0" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.index') }}">
                            <i class="bx bx-user me-2"></i> My Profile
                        </a>
                    </li>

                    @if($switchableRoles->count() > 0)
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header">Switch Role</li>
                        @foreach($switchableRoles as $role)
                            <li>
                                <form method="POST" action="{{ route('switch.role') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="role_id" value="{{ $role->id }}">
                                    <button type="submit" class="dropdown-item">
                                        <i class="bx bx-refresh me-2"></i> {{ $role->name }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    @endif

                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bx bx-power-off me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</aside>