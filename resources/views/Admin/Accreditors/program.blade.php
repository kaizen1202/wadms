@extends('admin.layouts.master')

@section('contents')
<div class="container-xxl container-p-y">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.accreditation.index') }}" class="text-muted">Accreditation</a>
                    </li>
                    <li class="breadcrumb-item active">Area Details</li>
                </ol>
            </nav>
            <h4 class="mb-0 fw-bold">{{ $programName }}</h4>
            <small class="text-muted">
                <i class="bx bx-bar-chart-alt-2 me-1"></i>{{ $level }}
            </small>
        </div>
        <div class="d-flex gap-2">
            @if ($isAdmin)
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignAreasModal">
                    <i class="bx bx-layer me-1"></i> Add Area
                </button>
            @endif
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm" style="border-radius:10px;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:42px;height:42px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;">
                        <i class="bx bx-layer text-primary" style="font-size:1.3rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.4rem;">{{ $programAreas->count() }}</div>
                        <div class="text-muted" style="font-size:0.78rem;">Total Areas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm" style="border-radius:10px;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:42px;height:42px;border-radius:10px;background:#edfaf3;display:flex;align-items:center;justify-content:center;">
                        <i class="bx bx-user-check text-success" style="font-size:1.3rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.4rem;">
                            {{ $programAreas->sum(fn($a) => $a->users->count()) }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">Total Assigned Users</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm" style="border-radius:10px;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:42px;height:42px;border-radius:10px;background:#fdeef0;display:flex;align-items:center;justify-content:center;">
                        <i class="bx bx-layer-minus text-danger" style="font-size:1.3rem;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.4rem;">
                            {{ $programAreas->filter(fn($a) => $a->users->count() === 0)->count() }}
                        </div>
                        <div class="text-muted" style="font-size:0.78rem;">Unassigned Areas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Area Cards --}}
    <div class="row g-3">
        @forelse ($programAreas as $area)
            <div class="col-md-4">
                <div class="card area-card h-100 shadow-sm d-flex flex-column" data-area-id="{{ $area->id }}">

                    {{-- Clickable header + body --}}
                    <a href="{{ route('program.areas.parameters', [$infoId, $levelId, $programId, $area->id]) }}"
                       class="text-decoration-none flex-grow-1 d-flex flex-column">

                        {{-- Header --}}
                        <div class="area-header">
                            <div class="area-title">Area</div>
                            <div class="area-name">{{ $area->area_name }}</div>
                            <span class="area-badge">
                                <i class="bx bx-user bx-xs"></i>
                                {{ $area->users->count() }} assigned
                            </span>
                        </div>

                        {{-- Body --}}
                        <div class="area-body">
                            @if ($area->users->count() > 0)
                                <div class="d-flex align-items-center">
                                    <div class="avatar-stack">
                                        @foreach ($area->users->take(4) as $user)
                                            <x-initials-avatar :user="$user" size="sm" shape="circle" />
                                        @endforeach
                                        @if ($area->users->count() > 4)
                                            <div class="av av-more">+{{ $area->users->count() - 4 }}</div>
                                        @endif
                                    </div>
                                    <span class="assigned-label">
                                        {{ $area->users->take(1)->first()?->name }}
                                        @if ($area->users->count() > 1)
                                            & {{ $area->users->count() - 1 }} more
                                        @endif
                                    </span>
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2">
                                    <div class="av av-empty">
                                        <i class="bx bx-user-x" style="font-size:0.9rem;"></i>
                                    </div>
                                    <span class="no-users-text">No users assigned yet</span>
                                </div>
                            @endif
                        </div>

                    </a>

                    {{-- Footer action --}}
                    @if ($isAdmin || $isDean)
                        <div class="area-footer">
                            <button type="button"
                                class="btn btn-sm btn-outline-primary w-100 assign-user-btn"
                                data-area-id="{{ $area->id }}"
                                data-area-name="{{ $area->area_name }}"
                                data-bs-toggle="modal"
                                data-bs-target="#assignUsersModal">
                                <i class="bx bx-user-plus me-1"></i>
                                {{ $isAdmin ? 'Assign Internal Assessors' : 'Assign Task Forces' }}
                            </button>
                        </div>
                    @endif

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius:12px;">
                    <i class="bx bx-layer d-block mb-3 text-muted" style="font-size:3rem; opacity:0.3;"></i>
                    <p class="text-muted mb-1">No areas found for this program.</p>
                    @if ($isAdmin)
                        <small class="text-muted">Click "Add Area" to get started.</small>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

</div>

{{-- ── Assign Areas Modal ── --}}
<div class="modal fade" id="assignAreasModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:12px; overflow:hidden;">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Add Area & Assign Internal Assessors</h5>
                    <small class="text-muted">Fill in the area name and assign users below</small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="areasForm" method="POST" action="{{ route('programs.areas.save', $programId) }}">
                    @csrf
                    <input type="hidden" name="level_id" value="{{ $levelId }}">
                    <input type="hidden" name="accreditation_info_id" value="{{ $infoId }}">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:30%;">Area Name</th>
                                    <th style="width:55%;">
                                        {{ $isAdmin ? 'Internal Assessors' : 'Task Forces' }}
                                    </th>
                                    <th style="width:15%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="areaTableBody"></tbody>
                        </table>
                    </div>

                    @if ($isAdmin)
                        <button type="button" class="btn btn-outline-primary btn-sm mt-1" id="addAreaRow">
                            <i class="bx bx-plus me-1"></i> Add Area
                        </button>
                    @endif

                    <div class="modal-footer border-0 px-0 pb-0 mt-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Save Assignments
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- ── Assign Users Modal ── --}}
<div class="modal fade" id="assignUsersModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:12px; overflow:hidden;">

            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        Assign {{ $isAdmin ? 'Internal Assessors' : 'Task Forces' }}
                    </h5>
                    <small class="text-muted">Area: <span id="assignAreaName" class="fw-semibold text-dark"></span></small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="assignUsersForm" method="POST" action="{{ route('areas.assign.users') }}">
                @csrf
                <input type="hidden" name="area_id" id="assignAreaId">
                <input type="hidden" name="program_id" value="{{ $programId }}">
                <input type="hidden" name="level_id" value="{{ $levelId }}">
                <input type="hidden" name="accreditation_info_id" value="{{ $infoId }}">

                <div class="modal-body">
                    @if ($isDean)
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50%">Task Force</th>
                                    <th style="width:30%">Role</th>
                                    <th style="width:20%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="taskForceTable"></tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="addTaskForceRow">
                            <i class="bx bx-plus me-1"></i> Add Task Force
                        </button>
                    @elseif ($isAdmin)
                        <label class="fw-semibold mb-2">Select Internal Assessors</label>
                        <select class="form-control js-assign-users" name="users[]" multiple style="width:100%">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-user-check me-1"></i> Assign
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    const programId = '{{ $programId }}';

    const ALL_USERS = [
        @foreach ($users as $user)
            { id: "{{ $user->id }}", name: "{{ $user->name }}" },
        @endforeach
    ];

    let taskForceIndex = 0;
    let areas = [];
    let areaIndex = 0;

    /* ── Modal open ── */
    $(document).on('click', '.assign-user-btn', function () {
        const areaId = $(this).data('area-id');
        $('#assignAreaId').val(areaId);
        $('#assignAreaName').text($(this).data('area-name'));
        $('#taskForceTable').empty();
        taskForceIndex = 0;

        $.get(`/admin/areas/${areaId}/assigned-users`)
            .done(res => {
                window.ASSIGNED_USERS = res.users.map(u => String(u.id));
                if ($('.js-assign-users').length) {
                    $('.js-assign-users').val(window.ASSIGNED_USERS).trigger('change');
                }
            })
            .fail(() => { window.ASSIGNED_USERS = []; });
    });

    $('.js-assign-users').select2({
        dropdownParent: $('#assignUsersModal'),
        width: '100%',
        placeholder: "Select users...",
        allowClear: true,
        closeOnSelect: false,
        templateResult: formatUser,
        templateSelection: formatUserSelection
    });

    /* ── Task Force ── */
    function initSelect2(context) {
        context.find('.select-user').select2({
            dropdownParent: $('#assignUsersModal'),
            width: '100%'
        });
    }

    function getSelectedUsers() {
        return $('.select-user').map(function () { return $(this).val(); }).get().filter(Boolean);
    }

    function updateUserDropdowns() {
        const selectedUsers = getSelectedUsers();
        const assignedUsers = window.ASSIGNED_USERS || [];

        $('.select-user').each(function () {
            const currentValue = $(this).val();
            const select = $(this);
            select.empty().append(`<option value="" disabled>Select Task Force</option>`);
            ALL_USERS.forEach(user => {
                if ((!selectedUsers.includes(user.id) || user.id === currentValue) &&
                    (!assignedUsers.includes(user.id) || user.id === currentValue)) {
                    select.append(`<option value="${user.id}">${user.name}</option>`);
                }
            });
            select.val(currentValue).trigger('change.select2');
        });
    }

    function updateChairAvailability() {
        const chairExists = $('.role-select').toArray().some(el => $(el).val() === 'chair');
        $('.role-select').each(function () {
            const isCurrentChair = $(this).val() === 'chair';
            $(this).find('option[value="chair"]').prop('disabled', chairExists && !isCurrentChair);
        });
    }

    function validateTaskForceForm() {
        let users = [], chairCount = 0;
        for (const row of $('#taskForceTable tr')) {
            const user = $(row).find('.select-user').val();
            const role = $(row).find('.role-select').val();
            if (!user || !role) return 'All fields are required.';
            if (users.includes(user)) return 'A user can only be assigned once.';
            users.push(user);
            if (role === 'chair') chairCount++;
        }
        if (chairCount > 1) return 'Only one Chair is allowed per area.';
        return null;
    }

    $('#addTaskForceRow').on('click', function () {
        const row = $(`
            <tr>
                <td>
                    <select name="users[${taskForceIndex}][id]" class="form-select form-select-sm select-user" required></select>
                </td>
                <td>
                    <select name="users[${taskForceIndex}][role]" class="form-select form-select-sm role-select" required>
                        <option value="chair">Chair</option>
                        <option value="member" selected>Member</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        $('#taskForceTable').append(row);
        initSelect2(row);
        taskForceIndex++;
        updateUserDropdowns();
        updateChairAvailability();
    });

    $(document).on('change', '.select-user', updateUserDropdowns);
    $(document).on('change', '.role-select', updateChairAvailability);
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        updateUserDropdowns();
        updateChairAvailability();
    });

    $('#assignUsersForm').on('submit', function (e) {
        e.preventDefault();
        const error = validateTaskForceForm();
        if (error) { showToast(error, 'error'); return; }

        $.post($(this).attr('action'), $(this).serialize())
            .done(res => {
                showToast(res.message, 'success');
                $('#assignUsersModal').modal('hide');
                refreshProgramAreas();
            })
            .fail(xhr => {
                showToast(xhr.responseJSON?.message || 'Failed to assign users', 'error');
            });
    });

    /* ── Areas ── */
    $('#addAreaRow').on('click', function () {
        const id = ++areaIndex;
        areas.push({ id, name: '', users: [] });

        const row = $(`
            <tr data-id="${id}">
                <td>
                    <input type="text" class="form-control area-name" placeholder="Area name" data-id="${id}">
                </td>
                <td>
                    <select class="form-select user-select" multiple data-id="${id}">
                        ${ALL_USERS.map(u => `<option value="${u.id}">${u.name}</option>`).join('')}
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove" data-id="${id}">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        $('#areaTableBody').append(row);
        row.find('.user-select').select2({ dropdownParent: $('#assignAreasModal'), width: '100%' });
    });

    $(document).on('change', '.user-select', function () {
        const id = Number($(this).data('id'));
        const area = areas.find(a => a.id === id);
        if (area) area.users = ($(this).val() || []).map(Number);
    });

    $(document).on('input', '.area-name', function () {
        const id = Number($(this).data('id'));
        const area = areas.find(a => a.id === id);
        if (area) area.name = this.value;
    });

    $(document).on('click', '.btn-remove', function () {
        const id = Number($(this).data('id'));
        areas = areas.filter(a => a.id !== id);
        $(this).closest('tr').remove();
    });

    $('#areasForm').on('submit', function (e) {
        e.preventDefault();
        $(this).find('input[name^="areas"]').remove();
        areas.forEach((area, i) => {
            $(this).append(`<input type="hidden" name="areas[${i}][name]" value="${area.name}">`);
            area.users.forEach(uid => {
                $(this).append(`<input type="hidden" name="areas[${i}][users][]" value="${uid}">`);
            });
        });

        $.post($(this).attr('action'), $(this).serialize())
            .done(res => {
                showToast(res.message, 'success');
                areas = [];
                $('#areaTableBody').empty();
                $('#assignAreasModal').modal('hide');
                refreshProgramAreas();
            })
            .fail(xhr => {
                showToast(xhr.responseJSON?.message || 'Something went wrong!', 'error');
            });
    });

    /* ── Refresh ── */
    function refreshProgramAreas() {
        $.get(`/admin/programs/${programId}/areas`)
            .done(data => {
                const container = $('.row.g-3').empty();
                data.forEach(area => {
                    const shown = area.users.slice(0, 4);
                    const avatarsHtml = shown.map(u => `
                        <div class="av" title="${u.name}">${getInitials(u.name)}</div>
                    `).join('');
                    const moreHtml = area.users.length > 4
                        ? `<div class="av av-more">+${area.users.length - 4}</div>` : '';
                    const firstName = area.users[0]?.name ?? '';
                    const moreCount = area.users.length > 1 ? ` & ${area.users.length - 1} more` : '';
                    const assignedHtml = area.users.length > 0
                        ? `<div class="d-flex align-items-center">
                               <div class="avatar-stack">${avatarsHtml + moreHtml}</div>
                               <span class="assigned-label">${firstName}${moreCount}</span>
                           </div>`
                        : `<div class="d-flex align-items-center gap-2">
                               <div class="av av-empty"><i class="bx bx-user-x" style="font-size:0.9rem;"></i></div>
                               <span class="no-users-text">No users assigned yet</span>
                           </div>`;

                    container.append(`
                        <div class="col-md-4">
                            <div class="card area-card h-100 shadow-sm d-flex flex-column" data-area-id="${area.id}">
                                <a href="/admin/programs/${programId}/areas/${area.id}/parameters"
                                   class="text-decoration-none flex-grow-1 d-flex flex-column">
                                    <div class="area-header">
                                        <div class="area-title">Area</div>
                                        <div class="area-name">${area.name}</div>
                                        <span class="area-badge">
                                            <i class="bx bx-user bx-xs"></i> ${area.users.length} assigned
                                        </span>
                                    </div>
                                    <div class="area-body">${assignedHtml}</div>
                                </a>
                            </div>
                        </div>
                    `);
                });
            });
    }

    function getInitials(name) {
        if (!name) return '?';
        const parts = name.trim().split(' ').filter(Boolean);
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    function formatUser(user) {
        if (!user.id) return user.text;
        return $(`
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#2563eb);
                            color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;">
                    ${getInitials(user.text)}
                </div>
                <span>${user.text}</span>
            </div>
        `);
    }

    function formatUserSelection(user) {
        if (!user.id) return user.text;
        return $(`
            <span style="background:#e8eaf6;padding:3px 8px;border-radius:20px;font-size:12px;
                         display:inline-flex;align-items:center;gap:5px;">
                <span style="width:18px;height:18px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#2563eb);
                             color:#fff;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center;">
                    ${getInitials(user.text)}
                </span>
                ${user.text}
            </span>
        `);
    }
});
</script>
@endpush

@endsection