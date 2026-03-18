@extends('admin.layouts.master')

@section('contents')

@php
    use App\Enums\UserType;
    use App\Enums\UserStatus;

    $userType = $user->user_type;

    $canRequestRole = !in_array($userType, [
        UserType::ADMIN,
        UserType::ACCREDITOR
    ]);

    $active = $user->status === UserStatus::ACTIVE->value;
@endphp

<div class="container py-5">

    {{-- PROFILE CARD --}}
    <div class="card shadow-sm mb-5">
        <h5 class="card-header bg-primary text-white">Profile Details</h5>

        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4 mt-4">

                {{-- AVATAR --}}
                <img src="{{ $user->profile_pic_path
                    ? asset($user->profile_pic_path)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=121872&color=fff' }}"
                    class="rounded-circle border border-2 border-primary"
                    height="120" width="120"
                    alt="User Avatar" />

                {{-- BASIC INFO --}}
                <div class="flex-grow-1">
                    <h4 class="mb-1">{{ $user->name }}</h4>

                    @php
                        $statusClass = match ($user->status->value ?? $user->status) {
                            'Active' => 'success',
                            'Pending' => 'warning',
                            'Suspended' => 'danger',
                            'Inactive' => 'secondary',
                            default => 'secondary',
                        };
                    @endphp

                    <p class="text-muted mb-1">
                        @foreach($user->roles as $role)
                            <span class="badge 
                                {{ $user->current_role_id === $role->id 
                                    ? 'bg-primary' 
                                    : 'bg-secondary' }}">
                                {{ $role->name }}

                                @if($user->roles->count() > 1 && $user->current_role_id === $role->id)
                                    (Current)
                                @endif
                            </span>
                        @endforeach
                    </p>

                    <span class="badge bg-{{ $statusClass }}">
                        {{ $user->status->value ?? $user->status }}
                    </span>
                </div>
            </div>
        </div>

        <hr class="my-0" />

        {{-- PROFILE DETAILS --}}
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Registered At</label>
                    <input type="text"
                        class="form-control"
                        value="{{ $user->created_at->format('M d, Y h:i A') }}"
                        disabled>
                </div>

            </div>
        </div>
    </div>

    @if($requestableRoles->count() > 0 && $active)
    {{-- REQUEST ADDITIONAL ROLE --}}
    <div class="card shadow-sm mb-5">
        <h5 class="card-header">Request Additional Role</h5>

        <div class="card-body">

            @if($pendingRequest ?? false)
                <div class="alert alert-warning">
                    You already have a pending role request.
                </div>
            @else
                <form id="role-request-form" action="{{ route('role-requests.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Select Role</label>
                        <select name="role_id" id="roleSelect" class="form-select" required>
                            <option value="">-- Select Role --</option>

                            @foreach($requestableRoles as $role)
                                <option value="{{ $role->id }}">
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea name="reason" class="form-control" rows="3"
                            placeholder="Explain why you are requesting this role"></textarea>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
                        Submit Request
                    </button>
                </form>
            @endif
        </div>
    </div>
    @endif

    {{-- DANGER ZONE --}}
    <div class="card shadow-sm border-danger">
        <h5 class="card-header text-danger">Danger Zone</h5>
        <div class="card-body">
            <div class="alert alert-warning">
                <h6 class="alert-heading mb-1">Terminate My Account</h6>
                <p class="mb-0">
                    Terminating your account will suspend your account immediately.
                </p>
            </div>

            <form action="{{ url('/users/' . $user->id . '/suspend') }}" method="POST">
                @csrf
                <button class="btn btn-danger" type="submit">
                    Terminate Account
                </button>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $('#role-request-form').on('submit', function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ route('role-requests.store') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(res){
                showToast(res.message || 'Role request submitted', 'success');

                location.reload();
            },
            error: function(xhr){
                let msg = xhr.responseJSON?.message || 'Something went wrong';
                showToast(msg, 'error');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('roleSelect');
        const submitBtn = document.getElementById('submitBtn');

        roleSelect.addEventListener('change', function () {
            submitBtn.disabled = this.value === '';
        });
    });
</script>

@endpush
@endsection
