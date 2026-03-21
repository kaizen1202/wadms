@extends('admin.layouts.master')
@section('contents')
<link rel="stylesheet" href="{{ asset('assets/css/semantic.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/data-tables.semanticui.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tom-select.bootstrap5.min.css') }}">

<div class="container-xxl container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-4 fw-bold">Accreditation Event</h2>
        @if($isAdmin)
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccreditationModal">
            <i class="bx bx-plus"></i> Add Accreditation
        </button>
        @endif
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table id="accreditationTable" class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Year</th>
                        <th>Programs</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody class="table-border-bottom-0">
                    {{-- rows injected by JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= ADD ACCREDITATION MODAL ================= --}}
<div class="modal fade" id="addAccreditationModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="accreditationForm" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Add Accreditation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {{-- TITLE --}}
                <div class="mb-3">
                    <label class="form-label">Accreditation Title <span class="text-danger">*</span></label>
                    <input name="title" type="text" class="form-control" required>
                </div>

                {{-- DATE + AGENCY --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Accreditation Date <span class="text-danger">*</span></label>
                        <input name="date" type="date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Accreditation Agency <span class="text-danger">*</span></label>
                        <input name="accreditation_body" type="text" class="form-control" required>
                    </div>
                </div>

                {{-- VISIT TYPE --}}
                <div class="mb-3">
                    <label class="form-label">Visit Type <span class="text-danger">*</span></label>
                    <select name="visit_type" class="form-select" required>
                        <option value="physical">Physical</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <h6 class="mb-3">Level & Programs</h6>

                <div class="row g-3">
                    {{-- LEVEL --}}
                    <div class="col-md-6">
                        <label class="form-label">Accreditation Level <span class="text-danger">*</span></label>
                        <select name="level" id="levelSelect" class="form-select" required>
                            <option disabled selected>Select level</option>
                            <option>Preliminary</option>
                            <option>Level I</option>
                            <option>Level II</option>
                            <option>Level III</option>
                            <option>Level IV</option>
                        </select>
                    </div>

                    {{-- PROGRAM COMBOBOX --}}
                    <div class="col-md-6">
                        <label class="form-label">Programs <span class="text-danger">*</span></label>
                        <select id="programSelect" name="programs[]" multiple placeholder="Type & press Enter to add..."></select>
                        <small class="text-muted">Example: Master of Management</small>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Accreditation</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= ADD LEVEL & PROGRAMS MODAL ================= --}}
<div class="modal fade" id="addLevelProgramModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="addLevelProgramForm" class="modal-content">
            @csrf
            <input type="hidden" name="accreditation_info_id" id="lp_info_id">

            <div class="modal-header">
                <h5 class="modal-title">Add Level & Programs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Accreditation Level <span class="text-danger">*</span></label>
                    <select name="level" class="form-select" required>
                        <option disabled selected>Select level</option>
                        <option>Preliminary</option>
                        <option>Level I</option>
                        <option>Level II</option>
                        <option>Level III</option>
                        <option>Level IV</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Programs <span class="text-danger">*</span></label>
                    <select id="lpSelect" name="programs[]" multiple placeholder="Type & press Enter to add..."></select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= ADD PROGRAM MODAL ================= --}}
<div class="modal fade" id="addProgramModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="addProgramForm" class="modal-content">
            @csrf
            <input type="hidden" name="accreditation_info_id" id="p_info_id">
            <input type="hidden" name="level" id="p_level">

            <div class="modal-header">
                <h5 class="modal-title">Add Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Programs <span class="text-danger">*</span></label>
                    <select id="pSelect" name="programs[]" multiple placeholder="Type & press Enter to add..."></select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= EDIT ACCREDITATION MODAL ================= --}}
<div class="modal fade" id="editAccreditationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form id="editAccreditationForm" class="modal-content">
            @csrf
            @method('PUT')

            <input type="hidden" name="id" id="edit_id">

            <div class="modal-header">
                <h5 class="modal-title">Edit Accreditation Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Accreditation Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Accreditation Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="edit_date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Accreditation Agency <span class="text-danger">*</span></label>
                        <input type="text" name="accreditation_body" id="edit_body" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Visit Type <span class="text-danger">*</span></label>
                    <select name="visit_type" id="edit_visit_type" class="form-select">
                        <option value="physical">Physical</option>
                        <option value="online">Online</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Update Accreditation</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.5/js/dataTables.semanticui.js"></script>
<script src="{{ asset('assets/js/tomselect.js') }}"></script>

<script>
/* ================= GLOBALS ================= */
const form    = document.getElementById('accreditationForm');
const isAdmin = @json($isAdmin);

/* ================= TOM SELECT INIT ================= */
const tomConfig = {
    create        : true,
    persist       : false,
    createOnBlur  : true,
    plugins       : ['remove_button'],
    placeholder   : 'Type & press Enter to add...',
};

const programSelect = new TomSelect('#programSelect', tomConfig);
const lpSelect      = new TomSelect('#lpSelect',      tomConfig);
const pSelect       = new TomSelect('#pSelect',       tomConfig);

/* ================= DATATABLE ROWS ================= */
function accreditationRow(info) {
    return `
        <tr class="accred-row" data-id="${info.id}" data-href="/admin/accreditations/${info.id}">
            <td>
                <i class="bx bx-certification bx-sm text-primary me-3"></i>
                <span class="fw-medium">${info.title}</span>
            </td>
            <td>${info.year}</td>
            <td>
                <span class="badge bg-label-primary">${info.programs.length} Programs</span>
            </td>
            <td>
                <span class="badge bg-label-success me-1">${info.status}</span>
            </td>
            <td>
                <div class="d-flex align-items-center gap-1">

                    <a href="/admin/accreditations/${info.id}"
                       class="action-btn"
                       title="View Details"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top">
                        <i class="bx bx-show"></i>
                    </a>

                    <button type="button"
                            class="action-btn expand-programs"
                            title="View Level & Programs"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top">
                        <i class="bx bx-collection"></i>
                    </button>

                    ${isAdmin ? `
                    <button type="button"
                            class="action-btn action-btn--success add-level-program"
                            title="Add Level / Program"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-info-id="${info.id}">
                        <i class="bx bx-plus"></i>
                    </button>

                    <button type="button"
                            class="action-btn action-btn--warning edit-accreditation"
                            title="Edit Accreditation"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-id="${info.id}">
                        <i class="bx bx-edit"></i>
                    </button>
                    ` : ''}

                </div>
            </td>
        </tr>
    `;
}

function programChildRow(info) {
    const grouped = {};

    info.programs.forEach(p => {
        if (!grouped[p.level]) grouped[p.level] = [];
        grouped[p.level].push(p);
    });

    let html = `
<tr class="program-child bg-light">
    <td colspan="5" class="p-3">
        <div class="mb-3">
            <h6 class="fw-semibold mb-1">Level</h6>
            <small class="text-muted">Programs under this level</small>
        </div>
        <div class="accordion mt-3" id="accordion-${info.id}">
    `;

    Object.keys(grouped).forEach((level, idx) => {
        html += `
        <div class="card accordion-item border-0 mb-2">
            <h2 class="accordion-header" id="heading-${info.id}-${idx}">
                <button type="button"
                        class="accordion-button collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-${info.id}-${idx}"
                        aria-expanded="false">
                    <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                        <span class="fw-semibold">${level}</span>
                        <span class="badge bg-label-primary">${grouped[level].length}</span>
                    </div>
                </button>
            </h2>
            <div id="collapse-${info.id}-${idx}" class="accordion-collapse collapse" data-bs-parent="#accordion-${info.id}">
                <div class="accordion-body pt-0">
                    <hr class="my-2">
                    <div class="list-group list-group-flush ps-4">
        `;

        grouped[level].forEach(p => {
            html += `
                <a href="/admin/accreditations/${info.id}/level/${p.level_id}/program/${encodeURIComponent(p.name)}"
                   class="list-group-item list-group-item-action py-2">
                    ${p.name}
                </a>`;
        });

        html += `
                    </div>
                </div>
            </div>
        </div>`;
    });

    html += `
        </div>
    </td>
</tr>`;

    return html;
}

/* ================= FETCH ACCREDITATIONS ================= */
function fetchAccreditations() {
    $.get("{{ route('admin.accreditations.data') }}", function (data) {
        const tbody = $('#accreditationTable tbody');
        tbody.empty();

        let hasOngoing = false;

        data.forEach(body => {
            body.accreditation_infos.forEach(info => {
                if (info.status !== 'ongoing') return;
                hasOngoing = true;

                tbody.append(accreditationRow(info));
                const child = $(programChildRow(info)).hide();
                tbody.append(child);

                tbody.find(`tr[data-id="${info.id}"] .expand-programs`).on('click', function(e) {
                    e.preventDefault();
                    tbody.find('.program-child').not(child).hide();
                    child.toggle();
                });
            });
        });

        if (!hasOngoing) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bx bx-info-circle bx-lg mb-2"></i>
                        <div>No ongoing accreditations yet.</div>
                        ${isAdmin ? '<small>Click "Add Accreditation" to create one.</small>' : ''}
                    </td>
                </tr>
            `);
        }

        /* ── Init tooltips on newly rendered rows ── */
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover' });
        });
    });
}

$(document).ready(fetchAccreditations);

/* ================= SUBMIT: ADD ACCREDITATION ================= */
form.addEventListener('submit', function(e) {
    e.preventDefault();

    if (programSelect.getValue().length === 0) {
        alert('Please add at least one program.');
        return;
    }

    const formData = new FormData(form);

    $.ajax({
        url: "{{ route('admin.accreditations.store') }}",
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function() {
            $('#addAccreditationModal').modal('hide');
            form.reset();
            programSelect.clear();
            fetchAccreditations();
            showToast('Accreditation saved successfully!', 'success');
        },
        error: function(xhr) {
            let msg = 'Failed to save accreditation. Please try again.';
            if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
            showToast(msg, 'error');
        }
    });
});

/* ================= MODAL CONTEXT HANDLERS ================= */
$('#addAccreditationModal').on('hidden.bs.modal', function () {
    programSelect.clear();
});

$('#addLevelProgramModal').on('show.bs.modal', function(e) {
    const infoId = $(e.relatedTarget).data('info-id');
    $('#lp_info_id').val(infoId);
    lpSelect.clear();
    $(this).find('select[name="level"]').val('');
});

$('#addProgramModal').on('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    $('#p_info_id').val($(btn).data('info-id'));
    $('#p_level').val($(btn).data('level'));
    pSelect.clear();
});

/* ================= ADD LEVEL/PROGRAM BUTTON (delegated) ================= */
$(document).on('click', '.add-level-program', function() {
    const infoId = $(this).data('info-id');
    $('#lp_info_id').val(infoId);
    lpSelect.clear();
    $('#addLevelProgramModal').find('select[name="level"]').val('');
    $('#addLevelProgramModal').modal('show');
});

/* ================= EDIT ACCREDITATION ================= */
$(document).on('click', '.edit-accreditation', function(e) {
    e.preventDefault();
    const id = $(this).data('id');

    $.get(`/admin/accreditations/${id}/edit`, function(data) {
        $('#edit_id').val(data.id);
        $('#edit_title').val(data.title);
        $('#edit_date').val(data.date);
        $('#edit_body').val(data.accreditation_body);
        $('#edit_visit_type').val(data.visit_type);
        $('#editAccreditationModal').modal('show');
    });
});

$('#editAccreditationForm').on('submit', function(e) {
    e.preventDefault();
    const id = $('#edit_id').val();

    $.ajax({
        url: `/admin/accreditations/${id}`,
        method: 'PUT',
        data: $(this).serialize(),
        success: function() {
            $('#editAccreditationModal').modal('hide');
            fetchAccreditations();
            showToast('Accreditation updated successfully!', 'success');
        },
        error: function() {
            showToast('Failed to update accreditation.', 'error');
        }
    });
});

/* ================= CLICKABLE ROW ================= */
$(document).on('click', '.accred-row', function(e) {
    if ($(e.target).closest('.dropdown, .dropdown-toggle, .dropdown-menu, button, a').length) return;
    const href = $(this).data('href');
    if (href) window.location.href = href;
});

/* ================= AJAX CSRF SETUP ================= */
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});

/* ================= SUBMIT: ADD LEVEL + PROGRAMS ================= */
$('#addLevelProgramForm').on('submit', function(e) {
    e.preventDefault();

    if (lpSelect.getValue().length === 0) {
        alert('Please add at least one program.');
        return;
    }

    const fd = new FormData(this);
    if (!fd.get('accreditation_info_id')) {
        alert('Missing accreditation_info_id');
        return;
    }

    $.post({
        url: "{{ route('admin.accreditations.addLevelPrograms') }}",
        data: fd,
        processData: false,
        contentType: false,
        success: () => {
            $('#addLevelProgramModal').modal('hide');
            fetchAccreditations();
            showToast('Program added successfully!', 'success');
        },
        error: () => {
            showToast('Failed to add program. Please try again.', 'error');
        }
    });
});

/* ================= SUBMIT: ADD PROGRAM ONLY ================= */
$('#addProgramForm').on('submit', function(e) {
    e.preventDefault();

    if (pSelect.getValue().length === 0) {
        alert('Please add at least one program.');
        return;
    }

    const fd = new FormData(this);

    $.post({
        url: "{{ route('admin.accreditations.addProgram') }}",
        data: fd,
        processData: false,
        contentType: false,
        success: () => {
            $('#addProgramModal').modal('hide');
            fetchAccreditations();
            showToast('Program added successfully!', 'success');
        },
        error: () => {
            showToast('Failed to add program. Please try again.', 'error');
        }
    });
});
</script>
@endpush