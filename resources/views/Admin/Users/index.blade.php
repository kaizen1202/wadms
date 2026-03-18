@php
    use App\Enums\UserType;
    $taskForce = UserType::TASK_FORCE;
    $internalAssessor = UserType::INTERNAL_ASSESSOR;
    $accreditor = UserType::ACCREDITOR;
@endphp

@extends('admin.layouts.master')

@section('contents')

<link rel="stylesheet" href="{{ asset('assets/css/semantic.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/data-tables.semanticui.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-xxl flex-grow-1 container-p-y bg-footer-theme">
    <h2 class="fw-bold">Pending Accounts</h2>
    <div class="card">

        <div class="card-body">
            <table id="users-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>REQUESTED ROLE</th>
                        <th>Status</th>
                        <th>Registered At</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmVerifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fw-bold text-dark">
                    Confirm User Verification
                </h3>
                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="fs-6 text-dark mb-3">
                    You are about to <strong class="text-success">verify and activate</strong> this account.
                </p>
                <div class="border rounded p-3 mb-3 bg-light">
                    <p class="mb-1">
                        <strong>Name:</strong>
                        <span id="confirm-name" class="text-dark"></span>
                    </p>
                    <p class="mb-0">
                        <strong>Requested Role:</strong>
                        <span id="confirm-role"
                            class="badge bg-primary text-uppercase"></span>
                    </p>
                </div>
                <div class="alert alert-warning d-flex align-items-start gap-2 mb-0">
                    <i class="bx bx-error-circle fs-4"></i>
                    <div>
                        <strong>Please confirm carefully.</strong>
                        <p class="mb-0">
                            This action will grant system access based on the requested role.
                        </p>
                    </div>
                </div>
                <input type="hidden" id="confirm-user-id">
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary px-4"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button class="btn btn-success px-4 fw-semibold"
                        id="confirm-verify">
                    <i class="bx bx-check-circle me-1"></i>
                    Verify User
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmSuspendModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h3 class="modal-title text-danger">Suspend User</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <h5>Are you sure you want to suspend this user?</h5>
                <input type="hidden" id="suspend-user-id">
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button class="btn btn-danger"
                        id="confirm-suspend">
                    Suspend
                </button>
            </div>

        </div>
    </div>
</div>


@endsection

@push('scripts')

<script src="{{ asset('assets/js/data-tables.js') }}"></script>
<script src="{{ asset('assets/js/data-tables.semanticui.js') }}"></script>

<script>
$(function () {

    const table = $('#users-table').DataTable({
        processing: true,
        ajax: "{{ route('users.data') }}",
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'email' },
            { data: 'user_type' },
            {
                data: 'status',
                render: status => {
                    if (status === 'Pending') return '<span class="badge bg-warning">Pending</span>';
                    if (status === 'Suspended') return '<span class="badge bg-danger">Suspended</span>';
                    return status;
                }
            },
            {
                data: 'created_at',
                render: date => new Date(date).toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                })
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: row => {

                    let buttons = [];

                    if (row.status === 'Pending') {
                        buttons.push(`
                            <button class="btn btn-sm btn-success btn-verify"
                                    title="Verify"
                                    data-id="${row.id}">
                                <i class="bx bx-check"></i>
                            </button>
                        `);
                    }

                    if (row.status !== 'Suspended') {
                        buttons.push(`
                            <button class="btn btn-sm btn-danger btn-suspend"
                                    title="Suspend"
                                    data-id="${row.id}">
                                <i class="bx bx-trash"></i>
                            </button>
                        `);
                    }

                    return `
                        <div class="d-flex justify-content-center gap-1">
                            ${buttons.join('')}
                        </div>
                    `;
                }
            }
        ]
    });

    // OPEN CONFIRM VERIFY MODAL
    $(document).on('click', '.btn-verify', function () {

        const row = table.row($(this).closest('tr')).data();

        $('#confirm-user-id').val(row.id);
        $('#confirm-name').text(row.name);
        $('#confirm-role').text(row.user_type);

        new bootstrap.Modal('#confirmVerifyModal').show();
    });

    // CONFIRM VERIFICATION
    $('#confirm-verify').on('click', function () {

        const userId = $('#confirm-user-id').val();

        $.ajax({
            url: `/users/${userId}/verify`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: res => {
                bootstrap.Modal.getInstance(
                    document.getElementById('confirmVerifyModal')
                ).hide();

                showToast(
                    res.message || 'User verified successfully.',
                    'success'
                );

                table.ajax.reload(null, false);
            },

            error: xhr => {
                showToast(xhr.responseJSON?.message ?? 'Verification failed.');
            }
        });
    });

    // OPEN SUSPEND MODAL
    $(document).on('click', '.btn-suspend', function () {

        const row = table.row($(this).closest('tr')).data();

        $('#suspend-user-id').val(row.id);

        new bootstrap.Modal('#confirmSuspendModal').show();
    });

    // CONFIRM SUSPEND
    $('#confirm-suspend').on('click', function () {

        const userId = $('#suspend-user-id').val();

        $.ajax({
            url: `/users/${userId}/suspend`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },

            success: res => {

                bootstrap.Modal.getInstance(
                    document.getElementById('confirmSuspendModal')
                ).hide();

                showToast(
                    res.message || 'User suspended successfully.',
                    'warning'
                );

                table.ajax.reload(null, false);
            },

            error: xhr => {
                showToast(
                    xhr.responseJSON?.message ?? 'Failed to suspend user.',
                    'error'
                );
            }
        });
    });

});

</script>

@endpush

