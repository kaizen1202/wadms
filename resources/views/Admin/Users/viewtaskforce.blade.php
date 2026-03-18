@extends('admin.layouts.master')

@section('contents')
    <div class="content-wrapper">
        <!-- Content -->
        <div class="container-xxl flex-grow-1 container-p-y">

            {{-- PAGE HEADER WITH BACK BUTTON --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <a href="{{ route('users.taskforce.index') }}">
                        <span class="text-muted fw-light">Users</span>
                    </a> 
                    / Detail
                </h4>

                <a href="{{ route('users.taskforce.index') }}" class="btn btn-outline-primary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Task Force
                </a>
            </div>

            <div class="row">
                <div class="col-md-12">

                    {{-- TABS --}}
                    <ul class="nav nav-pills flex-column flex-md-row mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-account">
                                <i class="bx bx-user me-1"></i> Account
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-assignment">
                                <i class="bx bx-briefcase me-1"></i> Assignment
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-permissions">
                                <i class="bx bx-shield-quarter me-1"></i> Permissions
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- ACCOUNT TAB --}}
                        <div class="tab-pane fade show active" id="tab-account">
                            {{-- PROFILE CARD --}}
                            <div class="card mb-4">
                                <h5 class="card-header">Profile Details</h5>

                                <div class="card-body">
                                    <div class="d-flex align-items-start align-items-sm-center gap-4">

                                        {{-- AVATAR --}}
                                        <img src="{{ $user->profile_pic_path
                                            ? asset($user->profile_pic_path)
                                            : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=121872&color=fff' }}"
                                            class="rounded" height="100" width="100" alt="User Avatar" />

                                        {{-- BASIC INFO --}}
                                        <div>
                                            <h5 class="mb-1">{{ $user->name }}</h5>

                                            {{-- STATUS BADGE --}}
                                            @php
                                                $statusClass = match ($user->status) {
                                                    'Active' => 'success',
                                                    'Pending' => 'warning',
                                                    'Suspended' => 'danger',
                                                    'Inactive' => 'secondary',
                                                    default => 'secondary',
                                                };
                                            @endphp

                                            <span class="badge bg-label-{{ $statusClass }}">
                                                {{ $user->status }}
                                            </span>

                                            <p class="text-muted mt-2 mb-0">
                                                {{ $user->user_type }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-0" />

                                {{-- DETAILS --}}
                                <div class="card-body">
                                    <form onsubmit="return false">
                                        <div class="row">
                                            @php
                                                $nameParts = explode(' ', trim($user->name));
                                                $lastName = array_pop($nameParts);
                                                $firstName = implode(' ', $nameParts);
                                            @endphp

                                            {{-- FIRST NAME --}}
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">First Name</label>
                                                <input class="form-control"
                                                    value="{{ $firstName }}"
                                                    disabled />
                                            </div>

                                            {{-- LAST NAME --}}
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Last Name</label>
                                                <input class="form-control"
                                                    value="{{ $lastName }}"
                                                    disabled />
                                            </div>

                                            {{-- EMAIL --}}
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Email</label>
                                                <input class="form-control" value="{{ $user->email }}" disabled />
                                            </div>

                                            {{-- USER TYPE --}}
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Role</label>
                                                <input class="form-control" value="{{ $user->user_type }}" disabled />
                                            </div>

                                            {{-- STATUS --}}
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Status</label>
                                                <input class="form-control" value="{{ $user->status }}" disabled />
                                            </div>

                                            {{-- REGISTERED AT --}}
                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Registered At</label>
                                                <input class="form-control"
                                                    value="{{ $user->created_at->format('M d, Y h:i A') }}" disabled />
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- DANGER ZONE --}}
                            <div class="card">
                                <h5 class="card-header text-danger">Danger Zone</h5>

                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <h6 class="alert-heading mb-1">Terminate User Account</h6>
                                        <p class="mb-0">
                                            Terminating this user will suspend their access immediately.
                                        </p>
                                    </div>

                                   <button
                                        class="btn btn-danger btn-terminate"
                                        data-id="{{ $user->id }}"
                                        data-url="{{ url('/users/' . $user->id . '/suspend') }}">
                                        <i class="bx bx-trash me-1"></i> Terminate User
                                    </button>

                                </div>
                            </div>
                        </div>
                        
                        {{-- ================= ASSIGNMENT TAB ================= --}}
                        <div class="tab-pane fade" id="tab-assignment">

                            <div class="card">
                                <h5 class="card-header">Assigned Areas</h5>

                                <div class="card-body">

                                    @if (empty($assignmentHierarchy))
                                        <p class="text-muted fst-italic">
                                            This user has no task force assignments.
                                        </p>
                                    @else

                                        {{-- ACCORDION WRAPPER (IMPORTANT) --}}
                                        <div id="areas-accordion">

                                            @foreach ($assignmentHierarchy as $accreditation)

                                                <div class="mb-4">
                                                <h6 class="fw-bold mb-2">
                                                        {{ $accreditation['title'] }} {{ $accreditation['year'] }}
                                                    </h6>
                                                    <p class="text mb-2">
                                                        Level: <span class="fw-bold">{{ $accreditation['level'] }}</span>
                                                    </p>

                                                    @foreach ($accreditation['programs'] as $program)
                                                        
                                                        <p class="text mb-2">
                                                            Program: <span class="fw-bold">{{ $program['name'] }}</span>
                                                        </p>

                                                        <div class="row g-3 mb-3">

                                                            @foreach ($program['areas'] as $areaId => $area)

                                                                {{-- ================= AREA CARD ================= --}}
                                                                <div class="col-md-4">
                                                                    <div class="card h-100 border shadow-sm">
                                                                        <div class="card-body">
                                                                            <h6 class="fw-semibold mb-2">
                                                                                {{ $area['name'] }}
                                                                            </h6>

                                                                            <button
                                                                                class="btn btn-sm btn-outline-primary w-100"
                                                                                data-bs-toggle="collapse"
                                                                                data-bs-target="#area-details-{{ $areaId }}"
                                                                                aria-expanded="false"
                                                                                aria-controls="area-details-{{ $areaId }}">
                                                                                View Details
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- ================= COLLAPSIBLE DETAILS ================= --}}
                                                                <div class="col-12">

                                                                    <div id="area-details-{{ $areaId }}"
                                                                        class="collapse mt-3"
                                                                        data-bs-parent="#areas-accordion">

                                                                        <div class="card border">
                                                                            <div class="card-body p-0">

                                                                                {{-- ========== TASK FORCE VIEW ========== --}}
                                                                                @if ($isTaskForce)

                                                                                    <div class="table-responsive">
                                                                                        <table class="table table-sm table-bordered mb-0">
                                                                                            <thead class="table-light">
                                                                                                <tr>
                                                                                                    <th>Parameter</th>
                                                                                                    <th>Sub-parameter</th>
                                                                                                    <th>File</th>
                                                                                                    <th>Status</th>
                                                                                                    <th>Action</th>
                                                                                                </tr>
                                                                                            </thead>

                                                                                            <tbody>
                                                                                                @foreach ($area['parameters'] ?? [] as $parameter)
                                                                                                    @foreach ($parameter['sub_parameters'] as $sub)

                                                                                                        @php
                                                                                                            $documents = $sub['documents'] ?? [];
                                                                                                        @endphp

                                                                                                        @if (count($documents) === 0)
                                                                                                            <tr>
                                                                                                                <td>{{ $parameter['name'] }}</td>
                                                                                                                <td>{{ $sub['name'] }}</td>
                                                                                                                <td colspan="3"
                                                                                                                    class="text-muted fst-italic">
                                                                                                                    No documents uploaded
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        @else
                                                                                                            @foreach ($documents as $doc)
                                                                                                                <tr>
                                                                                                                    <td>{{ $parameter['name'] }}</td>
                                                                                                                    <td>{{ $sub['name'] }}</td>
                                                                                                                    <td>
                                                                                                                        <a href="{{ Storage::url($doc['file_path']) }}"
                                                                                                                        target="_blank">
                                                                                                                            {{ $doc['file_name'] }}
                                                                                                                        </a>
                                                                                                                    </td>
                                                                                                                    <td>
                                                                                                                        <span class="badge bg-success">
                                                                                                                            {{ $doc['status'] }}
                                                                                                                        </span>
                                                                                                                    </td>
                                                                                                                    <td>
                                                                                                                        <a href="{{ Storage::url($doc['file_path']) }}"
                                                                                                                        target="_blank"
                                                                                                                        class="btn btn-sm btn-outline-primary">
                                                                                                                            View
                                                                                                                        </a>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            @endforeach
                                                                                                        @endif
                                                                                                    @endforeach
                                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                @endif

                                                                                {{-- ========== INTERNAL ASSESSOR VIEW ========== --}}
                                                                                @if ($isInternalAssessor)

                                                                                    @php
                                                                                        $evaluation = $area['evaluation'] ?? null;
                                                                                    @endphp

                                                                                    <div class="p-3">
                                                                                        <div class="d-flex justify-content-between mb-3">
                                                                                            <strong>Evaluation Status</strong>

                                                                                            @if ($evaluation && $evaluation['status'] === 'Evaluated')
                                                                                    
                                                                                                <span class="badge bg-success">
                                                                                                    Evaluated
                                                                                                </span>
                                                                                            @else
                                                                                                <span class="badge bg-warning text-dark">
                                                                                                    Pending
                                                                                                </span>
                                                                                            @endif
                                                                                        </div>

                                                                                        {{-- UPDATED INFO --}}
                                                                                        @if ($evaluation && ($evaluation['is_updated'] ?? false))
                                                                                            <p class="text-muted small mb-2">
                                                                                                Last updated:
                                                                                                {{ \Carbon\Carbon::parse($evaluation['updated_at'])
                                                                                                    ->format('M d, Y h:i A') }}
                                                                                            </p>
                                                                                        @endif

                                                                                        {{-- SUMMARY --}}
                                                                                        @if ($evaluation && $evaluation['status'] === 'Evaluated')

                                                                                            <div class="mb-2">
                                                                                                <span class="fw-semibold">Area Mean:</span>
                                                                                                <span class="badge bg-success ms-2">
                                                                                                    {{ number_format($evaluation['area_mean'], 2) }}
                                                                                                </span>
                                                                                            </div>

                                                                                            @if (!empty($evaluation['recommendations']))
                                                                                                <div>
                                                                                                    <span class="fw-semibold">Recommendations:</span>
                                                                                                    <ul class="mt-2 mb-0">
                                                                                                        @foreach ($evaluation['recommendations'] as $rec)
                                                                                                            <li>{{ $rec }}</li>
                                                                                                        @endforeach
                                                                                                    </ul>
                                                                                                </div>
                                                                                            @else
                                                                                                <p class="text-muted fst-italic mb-0">
                                                                                                    No recommendations provided.
                                                                                                </p>
                                                                                            @endif

                                                                                        @else
                                                                                            <p class="text-muted fst-italic mb-0">
                                                                                                No evaluation submitted for this area.
                                                                                            </p>
                                                                                        @endif

                                                                                    </div>
                                                                                @endif

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach

                                        </div> {{-- END ACCORDION --}}
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- PERMISSIONS TAB (MUST BE OUTSIDE ASSIGNMENT TAB) --}}
                        <div class="tab-pane fade" id="tab-permissions">
                            <p class="text-muted fst-italic">
                                Permissions configuration coming soon.
                            </p>
                        </div>

                    @endsection

                    @push('scripts')
                        <script>
                            function terminateUser(id) {
                                alert('Terminate user ID: ' + id + ' (fake action)');
                            }
                        </script>
                    @endpush
