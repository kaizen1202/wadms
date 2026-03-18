@extends('admin.layouts.master')

@section('contents')
<div class="container-xxl flex-grow-1 container-p-y">

    @php
        use App\Enums\UserType;
        $user = auth()->user();
    @endphp

    @if ($user->status !== 'Active')

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center shadow-sm">
                    <div class="card-body py-5">

                        <i class="bx bx-info-circle bx-lg text-warning mb-3"></i>

                        @if ($user->status === 'Pending')
                            <h4>Account Under Review</h4>
                            <p class="text-muted">
                                Your account has not yet been approved.<br>
                                Please wait or contact the administrator.
                            </p>

                        @elseif ($user->status === 'Suspended')
                            <h4>Account Suspended</h4>
                            <p class="text-danger">
                                Your account has been removed or suspended.<br>
                                Please contact the administrator.
                            </p>

                        @elseif ($user->status === 'Inactive')
                            <h4>Account Inactive</h4>
                            <p class="text-muted">
                                Your account is currently inactive.<br>
                                Please contact the administrator.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('admin.dashboard.partials.' . $roleView)
    @endif
</div>
@endsection
