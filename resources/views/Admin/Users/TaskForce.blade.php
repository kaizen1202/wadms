@extends('admin.layouts.master')

@section('contents')

<link rel="stylesheet" href="{{ asset('assets/css/semantic.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/data-tables.semanticui.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
/* ── Action buttons ─────────────────────────────── */
.action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: #f0f1f3;
    color: #566a7f;
    font-size: 1rem;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s, color 0.15s, transform 0.1s;
}

.action-btn:hover          { background: #696cff; color: #fff; transform: translateY(-1px); }
.action-btn--warning:hover { background: #ffab00; color: #fff; }
.action-btn--danger:hover  { background: #ff3e1d; color: #fff; }

/* ── Filter bar ─────────────────────────────── */
.filter-bar {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    padding: 12px 16px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    margin-bottom: 1.25rem;
}

.filter-bar .filter-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #8592a3;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-right: 4px;
    white-space: nowrap;
}

/* ── Pill shape on top of Sneat btn ─────────────────────────────── */
.pill-btn {
    border-radius: 50px !important;
}
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Active Accounts</h2>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
            <i class="bx bx-plus me-1"></i> Add Account
        </button>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- FILTER BAR --}}
            <div class="filter-bar">
                <span class="filter-label">Filter by Role:</span>

                <button type="button" class="pill-btn btn btn-primary btn-sm" data-role="">
                    All
                </button>

                @if($isAdmin)
                    <button type="button" class="pill-btn btn btn-outline-secondary btn-sm" data-role="INTERNAL ASSESSOR">
                        Internal Assessor
                    </button>
                    <button type="button" class="pill-btn btn btn-outline-secondary btn-sm" data-role="ACCREDITOR">
                        Accreditor
                    </button>
                @endif

                @if($isDean)
                    <button type="button" class="pill-btn btn btn-outline-secondary btn-sm" data-role="TASK FORCE">
                        Task Force
                    </button>
                @endif
            </div>

            <table id="taskforce-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered At</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>

{{-- ================= ADD ACCOUNT MODAL ================= --}}
<div class="modal fade" id="addAccountModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="addAccountForm" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-user-plus me-2 text-primary"></i> Add Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Juan Dela Cruz" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="e.g. juan@example.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="user_type" class="form-select" required>
                        <option disabled selected value="">Select role</option>
                        @if($isAdmin)
                            <option value="INTERNAL ASSESSOR">Internal Assessor</option>
                            <option value="ACCREDITOR">Accreditor</option>
                        @endif
                        @if($isDean)
                            <option value="TASK FORCE">Task Force</option>
                        @endif
                    </select>
                </div>

                <div class="alert alert-info small mb-0">
                    <i class="bx bx-envelope me-1"></i>
                    A password setup link will be sent to the user's email address.
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="addAccountBtn">
                    <span id="addAccountSpinner" class="spinner-border spinner-border-sm d-none me-1"></span>
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')

<script src="{{ asset('assets/js/jQuery.js') }}"></script>
<script src="{{ asset('assets/js/data-tables.js') }}"></script>
<script src="{{ asset('assets/js/data-tables.semanticui.js') }}"></script>

<script>
const isAdmin = @json($isAdmin);
const isDean  = @json($isDean);

/* ================= DATATABLE ================= */
const table = $('#taskforce-table').DataTable({
    processing: true,
    ajax: "{{ route('taskforce.data') }}",
    columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        { data: 'user_type' }, // index 3 — filter target

        // STATUS BADGE
        {
            data: 'status',
            render: function (status) {
                const map = {
                    'Pending'   : 'bg-warning',
                    'Active'    : 'bg-success',
                    'Suspended' : 'bg-danger',
                };
                return `<span class="badge ${map[status] ?? 'bg-secondary'}">${status}</span>`;
            }
        },

        // DATE
        {
            data: 'created_at',
            render: function (date) {
                return new Date(date).toLocaleString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric',
                    hour: 'numeric', minute: '2-digit', hour12: true
                });
            }
        },

        // ACTIONS
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center align-middle',
            render: function (data, type, row) {
                const editBtn = (isAdmin || isDean) ? `
                    <button type="button" class="action-btn action-btn--warning"
                            title="Update Position" data-bs-toggle="tooltip" data-bs-placement="top"
                            onclick="updatePosition(${row.id})">
                        <i class="bx bx-edit"></i>
                    </button>` : '';

                const terminateBtn = (isAdmin || isDean) ? `
                    <button type="button" class="action-btn action-btn--danger btn-terminate"
                            title="Terminate User" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-id="${row.id}" data-url="{{ url('/users') }}/${row.id}/suspend">
                        <i class="bx bx-user-x"></i>
                    </button>` : '';

                return `
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <button type="button" class="action-btn"
                                title="View User" data-bs-toggle="tooltip" data-bs-placement="top"
                                onclick="viewUser(${row.id})">
                            <i class="bx bx-show"></i>
                        </button>
                        ${editBtn}
                        ${terminateBtn}
                    </div>`;
            }
        }
    ],
    drawCallback: function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover' });
        });
    }
});

/* ================= ROLE FILTER ================= */
$(document).on('click', '.pill-btn', function () {
    $('.pill-btn')
        .removeClass('btn-primary')
        .addClass('btn-outline-secondary');

    $(this)
        .removeClass('btn-outline-secondary')
        .addClass('btn-primary');

    table.column(3).search($(this).data('role')).draw();
});

/* ================= ROW ACTIONS ================= */
function viewUser(id) {
    window.location.href = "{{ url('taskforce/view') }}/" + id;
}

function updatePosition(id) {
    console.log('Update position', id);
}

$(document).on('click', '.btn-terminate', function () {
    const id  = $(this).data('id');
    const url = $(this).data('url');

    if (!confirm('Are you sure you want to terminate this user?')) return;

    $.ajax({
        url,
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function () {
            table.ajax.reload(null, false);
            showToast('User terminated successfully.', 'success');
        },
        error: function () {
            showToast('Failed to terminate user.', 'error');
        }
    });
});

/* ================= RESET MODAL ON CLOSE ================= */
$('#addAccountModal').on('hidden.bs.modal', function () {
    document.getElementById('addAccountForm').reset();
});

/* ================= SUBMIT: ADD ACCOUNT ================= */
$('#addAccountForm').on('submit', function (e) {
    e.preventDefault();

    const btn     = document.getElementById('addAccountBtn');
    const spinner = document.getElementById('addAccountSpinner');
    btn.disabled  = true;
    spinner.classList.remove('d-none');

    $.ajax({
        url: "{{ route('users.store') }}",
        method: 'POST',
        data: $(this).serialize(),
        success: function () {
            $('#addAccountModal').modal('hide');
            table.ajax.reload(null, false);
            showToast('Account created successfully!', 'success');
        },
        error: function (xhr) {
            let msg = 'Failed to create account.';
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
            } else if (xhr.responseJSON?.message) {
                msg = xhr.responseJSON.message;
            }
            showToast(msg, 'error');
        },
        complete: function () {
            btn.disabled = false;
            spinner.classList.add('d-none');
        }
    });
});

/* ================= AJAX CSRF ================= */
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});
</script>

@endpush