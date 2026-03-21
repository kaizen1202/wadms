@extends('admin.layouts.master')

@section('contents')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            {{-- PAGE HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <a href="{{ route('users.taskforce.index') }}">
                        <span class="text-muted fw-light">Users</span>
                    </a>
                    / Detail
                </h4>

                <a href="{{ route('users.taskforce.index') }}" class="btn btn-outline-primary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Active Accounts
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

                        {{-- ================= ACCOUNT TAB ================= --}}
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

                                            @php
                                                $statusClass = match ($user->status) {
                                                    'Active'    => 'success',
                                                    'Pending'   => 'warning',
                                                    'Suspended' => 'danger',
                                                    'Inactive'  => 'secondary',
                                                    default     => 'secondary',
                                                };
                                            @endphp

                                            <span class="badge bg-label-{{ $statusClass }} mb-1">
                                                {{ $user->status }}
                                            </span>

                                            <p class="text-muted mt-1 mb-0">{{ $user->user_type }}</p>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-0" />

                                <div class="card-body">
                                    <form onsubmit="return false">
                                        <div class="row">
                                            @php
                                                $nameParts = explode(' ', trim($user->name));
                                                $lastName  = array_pop($nameParts);
                                                $firstName = implode(' ', $nameParts);
                                            @endphp

                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">First Name</label>
                                                <input class="form-control" value="{{ $firstName }}" disabled />
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Last Name</label>
                                                <input class="form-control" value="{{ $lastName }}" disabled />
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Email</label>
                                                <input class="form-control" value="{{ $user->email }}" disabled />
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Role</label>
                                                <input class="form-control" value="{{ $user->user_type }}" disabled />
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Status</label>
                                                <input class="form-control" value="{{ $user->status }}" disabled />
                                            </div>

                                            <div class="mb-3 col-md-6">
                                                <label class="form-label">Registered At</label>
                                                <input class="form-control" value="{{ $user->created_at->format('M d, Y h:i A') }}" disabled />
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- DEACTIVATE ACCOUNT --}}
                            <div class="card border-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar flex-shrink-0">
                                                <span class="avatar-initial rounded bg-label-warning">
                                                    <i class="bx bx-user-x fs-4"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Deactivate Account</h6>
                                                <small class="text-muted">
                                                    This will suspend the user's access immediately.
                                                    They will not be able to log in until reactivated.
                                                </small>
                                            </div>
                                        </div>

                                        @if ($user->status === 'Active')
                                            <button class="btn btn-warning btn-deactivate flex-shrink-0"
                                                    data-id="{{ $user->id }}"
                                                    data-url="{{ url('/users/' . $user->id . '/suspend') }}">
                                                <i class="bx bx-lock me-1"></i> Deactivate
                                            </button>
                                        @else
                                            <span class="badge bg-label-secondary px-3 py-2">
                                                <i class="bx bx-lock-open me-1"></i> Already Inactive
                                            </span>
                                        @endif

                                    </div>
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

                                                                <div class="col-md-4">
                                                                    <div class="card h-100 border shadow-sm">
                                                                        <div class="card-body">
                                                                            <h6 class="fw-semibold mb-2">{{ $area['name'] }}</h6>
                                                                            <button
                                                                                class="btn btn-sm btn-outline-primary w-100"
                                                                                data-bs-toggle="collapse"
                                                                                data-bs-target="#area-details-{{ $areaId }}"
                                                                                aria-expanded="false">
                                                                                View Details
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12">
                                                                    <div id="area-details-{{ $areaId }}"
                                                                         class="collapse mt-3"
                                                                         data-bs-parent="#areas-accordion">

                                                                        <div class="card border">
                                                                            <div class="card-body p-0">

                                                                                {{-- TASK FORCE VIEW --}}
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
                                                                                                        @php $documents = $sub['documents'] ?? []; @endphp

                                                                                                        @if (count($documents) === 0)
                                                                                                            <tr>
                                                                                                                <td>{{ $parameter['name'] }}</td>
                                                                                                                <td>{{ $sub['name'] }}</td>
                                                                                                                <td colspan="3" class="text-muted fst-italic">No documents uploaded</td>
                                                                                                            </tr>
                                                                                                        @else
                                                                                                            @foreach ($documents as $doc)
                                                                                                                <tr>
                                                                                                                    <td>{{ $parameter['name'] }}</td>
                                                                                                                    <td>{{ $sub['name'] }}</td>
                                                                                                                    <td>
                                                                                                                        <a href="{{ Storage::url($doc['file_path']) }}" target="_blank">
                                                                                                                            {{ $doc['file_name'] }}
                                                                                                                        </a>
                                                                                                                    </td>
                                                                                                                    <td>
                                                                                                                        <span class="badge bg-success">{{ $doc['status'] }}</span>
                                                                                                                    </td>
                                                                                                                    <td>
                                                                                                                        <a href="{{ Storage::url($doc['file_path']) }}" target="_blank"
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

                                                                                {{-- INTERNAL ASSESSOR VIEW --}}
                                                                                @if ($isInternalAssessor)
                                                                                    @php $evaluation = $area['evaluation'] ?? null; @endphp

                                                                                    <div class="p-3">
                                                                                        <div class="d-flex justify-content-between mb-3">
                                                                                            <strong>Evaluation Status</strong>

                                                                                            @if ($evaluation && $evaluation['status'] === 'Evaluated')
                                                                                                <span class="badge bg-success">Evaluated</span>
                                                                                            @else
                                                                                                <span class="badge bg-warning text-dark">Pending</span>
                                                                                            @endif
                                                                                        </div>

                                                                                        @if ($evaluation && ($evaluation['is_updated'] ?? false))
                                                                                            <p class="text-muted small mb-2">
                                                                                                Last updated:
                                                                                                {{ \Carbon\Carbon::parse($evaluation['updated_at'])->format('M d, Y h:i A') }}
                                                                                            </p>
                                                                                        @endif

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
                                                                                                <p class="text-muted fst-italic mb-0">No recommendations provided.</p>
                                                                                            @endif
                                                                                        @else
                                                                                            <p class="text-muted fst-italic mb-0">No evaluation submitted for this area.</p>
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

                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ================= PERMISSIONS TAB ================= --}}
                        <div class="tab-pane fade" id="tab-permissions">
                            <div class="card">
                                <div class="card-body">
                                    <p class="text-muted fst-italic mb-0">
                                        Permissions configuration coming soon.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>{{-- end tab-content --}}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).on('click', '.btn-deactivate', function () {
    const id  = $(this).data('id');
    const url = $(this).data('url');

    if (!confirm('Are you sure you want to deactivate this user? They will lose access immediately.')) return;

    $.ajax({
        url,
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function () {
            showToast('User deactivated successfully.', 'success');
            setTimeout(() => window.location.reload(), 1000);
        },
        error: function () {
            showToast('Failed to deactivate user.', 'error');
        }
    });
});
</script>
@endpush