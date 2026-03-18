@extends('admin.layouts.master')
@section('contents')
<link rel="stylesheet" href="{{ asset('assets/css/semantic.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/data-tables.semanticui.css') }}">

<div class="container-xxl container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-4 fw-bold">Accreditation</h2>
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
                        <th>Accreditation</th>
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

{{-- ================= MODAL ================= --}}
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
                    <label class="form-label">Accreditation Title</label>
                    <input name="title" type="text" class="form-control" required>
                </div>

                {{-- DATE + AGENCY --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Accreditation Date</label>
                        <input name="date" type="date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Accreditation Agency</label>
                        <input name="accreditation_body" type="text" class="form-control" required>
                    </div>
                </div>

                {{-- VISIT TYPE --}}
                <div class="mb-3">
                    <label class="form-label">Visit Type</label>
                    <select name="visit_type" class="form-select" required>
                        <option value="physical">Physical</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <h6 class="mb-3">Level & Programs</h6>

                <div class="row g-3">
                    {{-- LEVEL --}}
                    <div class="col-md-6">
                        <label class="form-label">Accreditation Level</label>
                        <select name="level" id="levelSelect" class="form-select" required>
                            <option disabled selected>Select level</option>
                            <option>Preliminary</option>
                            <option>Level I</option>
                            <option>Level II</option>
                            <option>Level III</option>
                            <option>Level IV</option>
                        </select>
                    </div>

                    {{-- PROGRAM TAG INPUT --}}
                    <div class="col-md-6">
                        <label class="form-label">Programs</label>
                        <div class="tag-input">
                            <div class="tags"></div>
                            <input id="programInput" type="text" placeholder="Press Enter to add">
                        </div>

                        {{-- REAL FORM INPUTS --}}
                        <div id="programHiddenInputs"></div>

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
                    <label class="form-label">Accreditation Level</label>
                    <select name="level" class="form-select" required>
                        <option disabled selected>Select level</option>
                        <option>Preliminary</option>
                        <option>Level I</option>
                        <option>Level II</option>
                        <option>Level III</option>
                        <option>Level IV</option>
                    </select>
                </div>

                <label class="form-label">Programs</label>
                <div class="tag-input">
                    <div class="tags" id="lpTags"></div>
                    <input id="lpInput" type="text" placeholder="Press Enter to add">
                </div>

                <div id="lpHiddenInputs"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

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
                <label class="form-label">Programs</label>
                <div class="tag-input">
                    <div class="tags" id="pTags"></div>
                    <input id="pInput" type="text" placeholder="Press Enter to add">
                </div>
                <div id="pHiddenInputs"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

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
                    <label class="form-label">Accreditation Title</label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Accreditation Date</label>
                        <input type="date" name="date" id="edit_date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Accreditation Agency</label>
                        <input type="text" name="accreditation_body" id="edit_body" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Visit Type</label>
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

<script>
/* ================= ELEMENTS ================= */
const form = document.getElementById('accreditationForm');
const input = document.getElementById('programInput');
const tagsContainer = document.querySelector('.tags');
const hiddenInputs = document.getElementById('programHiddenInputs');
  const isAdmin = @json($isAdmin);
/* ================= STATE ================= */
let programs = [];

/* ================= ADD PROGRAM ================= */
input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const value = input.value.trim();
        if (!value || programs.includes(value)) return;

        programs.push(value);

        const tag = document.createElement('span');
        tag.className = 'tag';
        tag.innerHTML = `${value} <button type="button">&times;</button>`;

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'programs[]';
        hidden.value = value;

        tag.querySelector('button').onclick = () => {
            programs = programs.filter(p => p !== value);
            tag.remove();
            hidden.remove();
        };

        tagsContainer.appendChild(tag);
        hiddenInputs.appendChild(hidden);
        tagsContainer.scrollTop = tagsContainer.scrollHeight;

        input.value = '';
    }
});

form.addEventListener('submit', function(e) {
    if (programs.length === 0) {
        e.preventDefault();
        alert('Please add at least one program.');
    }
});

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
                <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="/admin/accreditations/${info.id}"><i class="bx bx-detail me-1"></i> View Details </a>
                        <a class="dropdown-item expand-programs" href="#"><i class="bx bx-collection me-1"></i> View Level & Programs</a>

                        ${isAdmin ? `
                        <a class="dropdown-item add-level-program" href="#"
                        data-bs-toggle="modal" data-bs-target="#addLevelProgramModal"
                        data-info-id="${info.id}">
                        <i class="bx bx-plus me-1"></i> Add Level / Program
                        </a>
                        <a class="dropdown-item edit-accreditation"
                        href="#"
                        data-id="${info.id}">
                            <i class="bx bx-edit me-1"></i> Edit Accreditation Info
                        </a>
                        ` : ''}
                    </div>
                </div>
            </td>
        </tr>
    `;
}

function programChildRow(info) {
    const grouped = {};

    info.programs.forEach(p => {
        console.log(p);
        if (!grouped[p.level]) grouped[p.level] = [];
        grouped[p.level].push(p);
    });

    console.log(grouped);

    let html = `
<tr class="program-child bg-light">
    <td colspan="5" class="p-3">

        <!-- ✅ TITLE FOR LEVEL & PROGRAMS -->
        <div class="mb-3">
            <h6 class="fw-semibold mb-1">Level</h6>
            <small class="text-muted">Programs under this level</small>
        </div>

        <div class="accordion mt-3" id="accordion-${info.id}">
    `;

    Object.keys(grouped).forEach((level, idx) => {
        html += `

        <div class="card accordion-item border-0 mb-2">
            <!-- LEVEL HEADER -->
            <h2 class="accordion-header" id="heading-${info.id}-${idx}">
                <button type="button"
                        class="accordion-button collapsed"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-${info.id}-${idx}"
                        aria-expanded="false">

                    <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                        <span class="fw-semibold">
                            ${level}
                        </span>

                        <span class="badge bg-label-primary">
                            ${grouped[level].length}
                        </span>
                    </div>

                </button>
            </h2>

            <!-- PROGRAMS LIST INSIDE (UNCHANGED) -->
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

form.addEventListener('submit', function(e) {
    e.preventDefault();

    if (programs.length === 0) {
        alert('Please add at least one program.');
        return;
    }

    const formData = new FormData(form); // already includes programs[] from hidden inputs

    $.ajax({
        url: "{{ route('admin.accreditations.store') }}",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#addAccreditationModal').modal('hide');
            form.reset();
            tagsContainer.innerHTML = '';
            hiddenInputs.innerHTML = '';
            programs = [];
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


/* ================= FETCH ACCREDITATIONS ================= */
function fetchAccreditations() {
    $.get("{{ route('admin.accreditations.data') }}", function (data) {
        const tbody = $('#accreditationTable tbody');
        tbody.empty();

        let hasOngoing = false;

        data.forEach(body => {
            body.accreditation_infos.forEach(info => {
                if (info.status !== 'ongoing') return; // Skip completed programs
                hasOngoing = true;

                tbody.append(accreditationRow(info));
                const child = $(programChildRow(info)).hide();
                tbody.append(child);

                tbody.find(`tr[data-id="${info.id}"] .expand-programs`).on('click', function(e) {
                    e.preventDefault();

                    // Close all other program rows
                    tbody.find('.program-child').not(child).hide();

                    // Toggle current one
                    child.toggle();
                });
            });
        });

        // If no ongoing accreditations, show empty state row
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
    });
}

$(document).ready(fetchAccreditations);

/* ================= MODAL TAG INPUTS ================= */
function initTagInput(inputId, tagsId, hiddenId, inputName) {
    let values = [];
    const input = document.getElementById(inputId);
    const tags = document.getElementById(tagsId);
    const hidden = document.getElementById(hiddenId);

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = input.value.trim();
            if (!value || values.includes(value)) return;
            values.push(value);

            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.innerHTML = `${value} <button type="button">&times;</button>`;

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = inputName + '[]';
            hiddenInput.value = value;

            tag.querySelector('button').onclick = () => {
                values = values.filter(v => v !== value);
                tag.remove();
                hiddenInput.remove();
            };

            tags.appendChild(tag);
            hidden.appendChild(hiddenInput);
            input.value = '';
        }
    });
}

initTagInput('lpInput', 'lpTags', 'lpHiddenInputs', 'programs');
initTagInput('pInput', 'pTags', 'pHiddenInputs', 'programs');

/* ================= MODAL CONTEXT HANDLERS ================= */
$('#addLevelProgramModal').on('show.bs.modal', function(e) {
    const button = e.relatedTarget;
    const infoId = $(button).data('info-id');
    $('#lp_info_id').val(infoId);
    $('#lpTags, #lpHiddenInputs').empty();
    $(this).find('select[name="level"]').val('');
});

$('#addProgramModal').on('show.bs.modal', function(e) {
    const button = e.relatedTarget;
    $('#p_info_id').val($(button).data('info-id'));
    $('#p_level').val($(button).data('level'));
    $('#pTags, #pHiddenInputs').empty();
});



$(document).on('click', '.edit-accreditation', function (e) {
    e.preventDefault();

    const id = $(this).data('id');

    $.get(`/admin/accreditations/${id}/edit`, function (data) {

        $('#edit_id').val(data.id);
        $('#edit_title').val(data.title);
        $('#edit_date').val(data.date);
        $('#edit_body').val(data.accreditation_body);
        $('#edit_visit_type').val(data.visit_type);

        $('#editAccreditationModal').modal('show');
    });
});
$('#editAccreditationForm').on('submit', function (e) {
    e.preventDefault();
    const id = $('#edit_id').val();

    $.ajax({
        url: `/admin/accreditations/${id}`,
        method: 'PUT', // <-- important
        data: $(this).serialize(),
        success: function () {
            $('#editAccreditationModal').modal('hide');
            fetchAccreditations();
            showToast('Accreditation updated successfully!', 'success');
        },
        error: function () {
            showToast('Failed to update accreditation.', 'error');
        }
    });
});

// Make accreditation row clickable
$(document).on('click', '.accred-row', function(e) {
    // Prevent navigating if user clicked inside dropdown or button
    if ($(e.target).closest('.dropdown, .dropdown-toggle, .dropdown-menu, button, a').length) return;

    const href = $(this).data('href');
    if (href) {
        window.location.href = href;
    }
});


</script>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});

initTagInput('lpInput', 'lpTags', 'lpHiddenInputs');
initTagInput('pInput', 'pTags', 'pHiddenInputs');

/* ========== SUBMIT: LEVEL + PROGRAM ========== */
$('#addLevelProgramForm').on('submit', function (e) {
    e.preventDefault();

    const fd = new FormData(this);
    if (!fd.get('accreditation_info_id')) {
        alert('Missing accreditation_info_id');
        return;
    }

    if (!fd.has('programs[]')) {
        fd.append('programs[]', null);
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
        error: (xhr) => {
            showToast('Failed to add program. Please try again.', 'error');
        }
    });
});

/* ========== SUBMIT: PROGRAM ONLY ========== */
$('#addProgramForm').on('submit', function (e) {
    e.preventDefault();

    const fd = new FormData(this);
    if (!fd.has('programs[]')) {
        fd.append('programs[]', null);
    }

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
        error: (xhr) => {
            showToast('Failed to add program. Please try again.', 'error');
        }
    });
});
</script>
@endpush
