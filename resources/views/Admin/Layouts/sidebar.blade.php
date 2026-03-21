@php
    use App\Enums\UserType;
    use App\Enums\UserStatus;

    $user        = auth()->user();
    $currentRole = $user->currentRole?->name;
    $isActive    = $user->status === UserStatus::ACTIVE->value;

    $roleColors = [
        UserType::ADMIN->value             => '#eb9c00ff',
        UserType::DEAN->value              => '#50C878',
        UserType::TASK_FORCE->value        => '#006241',
        UserType::INTERNAL_ASSESSOR->value => '#679267',
        UserType::ACCREDITOR->value        => '#004953',
    ];

    $sidebarColor = $roleColors[$currentRole] ?? '#343a40';

    // ── Shared menu item definitions ──────────────────────────────────────────
    $dashboard = [
        'label'  => 'Dashboard',
        'icon'   => 'bx-collection',
        'route'  => 'dashboard',
        'active' => ['dashboard'],
    ];

    $accreditation = [
        'label'  => 'Accreditation Event',
        'icon'   => 'bx-badge-check',
        'route'  => 'admin.accreditation.index',
        'active' => ['admin.accreditation.*'],
    ];

    $accreditatorAccreditation = [
        'label'  => 'Accreditation',
        'icon'   => 'bx-badge-check',
        'route'  => 'internal-accessor.index',
        'active' => ['internal-accessor.*'],
    ];

    $evaluations = [
        'label'  => 'Evaluations',
        'icon'   => 'bx-clipboard',
        'route'  => 'program.areas.evaluations',
        'active' => ['program.areas.*'],
    ];

    $archive = [
        'label'  => 'Archive',
        'icon'   => 'bx-folder',
        'route'  => 'archive.index',
        'active' => ['archive.*'],
    ];

    // ── Users submenu (shared by Admin & Dean, label differs) ─────────────────
    $usersSubmenu = fn(string $groupLabel) => [
        'label'        => $groupLabel,
        'icon'         => 'bx-user-check',
        'active'       => ['users.*', 'role-requests.*'],
        'badge'        => ($unverifiedCount > 0 || $pendingRoleRequestCount > 0) ? '!' : null,
        'badge_class'  => 'bg-warning',
        'children'     => [
            [
                'label'  => 'Pending Accounts',
                'route'  => 'users.index',
                'active' => ['users.index'],
                'badge'  => $unverifiedCount > 0 ? $unverifiedCount : null,
            ],
            [
                'label'  => 'Active Accounts',
                'route'  => 'users.taskforce.index',
                'active' => ['users.taskforce.index'],
            ],
            [
                'label'  => 'Role Requests',
                'route'  => 'role-requests.index',
                'active' => ['role-requests.*'],
                'badge'  => $pendingRoleRequestCount > 0 ? $pendingRoleRequestCount : null,
            ],
        ],
    ];

    // ── Per-role menu definitions ─────────────────────────────────────────────
    $menus = [
        UserType::ADMIN->value => [
            $dashboard,
            $usersSubmenu('Internal Assessors & Accreditors'),
            $accreditation,
            $evaluations,
            $archive,
        ],
        UserType::DEAN->value => [
            $dashboard,
            $usersSubmenu('Task Forces'),
            $accreditation,
            $evaluations,
        ],
        UserType::TASK_FORCE->value => [
            $dashboard,
            $accreditation,
            $evaluations,
        ],
        UserType::INTERNAL_ASSESSOR->value => [
            $dashboard,
            $accreditatorAccreditation,
            $evaluations,
        ],
        UserType::ACCREDITOR->value => [
            $dashboard,
            $accreditatorAccreditation,
            $evaluations,
        ],
    ];

    $menuItems = $menus[$currentRole] ?? [];
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu"
       style="background-color: {{ $sidebarColor }}; color: #fff;">

    {{-- Brand --}}
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/wdms/pit-logo-outlined.png') }}"
                     alt="Pit Logo" class="w-px-50 h-auto" />
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2 text-uppercase"
                  style="color:#fff;">WADMS</span>
        </a>

        <a href="javascript:void(0);"
           class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none"
           @click.prevent="toggleSidebar">
            <i class="bx bx-chevron-left bx-sm align-middle" style="color:#fff;"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        {{-- ── Inactive account ── --}}
        @if (!$isActive)
            <li class="menu-item disabled">
                <span class="menu-link text-muted">
                    <i class="menu-icon tf-icons bx bx-lock"></i>
                    <div>
                        @switch($user->status)
                            @case('Pending')   Account Under Review @break
                            @case('Inactive')  Account Inactive     @break
                            @case('Suspended') Account Suspended    @break
                            @default           Account Not Active
                        @endswitch
                    </div>
                </span>
            </li>

        {{-- ── Active account — render menu from $menuItems ── --}}
        @elseif ($isActive)
            @foreach ($menuItems as $item)

                {{-- Item with children (submenu) --}}
                @if (!empty($item['children']))
                    @php
                        $isOpen = collect($item['active'])->contains(fn($p) => Route::is($p));
                    @endphp
                    <li class="menu-item {{ $isOpen ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle" style="color:#fff;">
                            <i class="menu-icon tf-icons bx {{ $item['icon'] }}"></i>
                            <div>{{ $item['label'] }}</div>
                            @if (!empty($item['badge']))
                                <span class="badge {{ $item['badge_class'] ?? 'bg-secondary' }} rounded-pill ms-auto">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                        <ul class="menu-sub">
                            @foreach ($item['children'] as $child)
                                @php
                                    $childActive = collect($child['active'])->contains(fn($p) => Route::is($p));
                                @endphp
                                <li class="menu-item {{ $childActive ? 'active' : '' }}">
                                    <a href="{{ route($child['route']) }}" class="menu-link" style="color:#fff;">
                                        {{ $child['label'] }}
                                        @if (!empty($child['badge']))
                                            <span class="badge bg-warning rounded-pill ms-auto">
                                                {{ $child['badge'] }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>

                {{-- Simple item (no children) --}}
                @else
                    @php
                        $isActive = collect($item['active'])->contains(fn($p) => Route::is($p));
                    @endphp
                    <li class="menu-item {{ $isActive ? 'active' : '' }}">
                        <a href="{{ route($item['route']) }}" class="menu-link" style="color:#fff;">
                            <i class="menu-icon tf-icons bx {{ $item['icon'] }}"></i>
                            <div>{{ $item['label'] }}</div>
                        </a>
                    </li>
                @endif

            @endforeach
        @endif

    </ul>

    {{-- ── Profile footer ── --}}
    <div class="menu-footer mt-auto p-3" style="border-top: 1px solid rgba(255,255,255,0.2);">
        <div class="d-flex align-items-center gap-3">

            <x-initials-avatar :user="$user" />

            <div class="flex-grow-1 text-truncate">
                <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                <small class="opacity-75">
                    {{ $user->status !== UserStatus::PENDING->value ? $currentRole : $user->status }}
                </small>
            </div>

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

                    @if ($switchableRoles->count() > 0)
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header">Switch Role</li>
                        @foreach ($switchableRoles as $role)
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