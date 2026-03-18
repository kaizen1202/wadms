@extends('admin.layouts.master')

@section('contents')

    @php $isCompleted = $accreditationStatus->value === 'completed'; @endphp

    <div class="container-xxl container-p-y">

        {{-- HEADER WITH BACK BUTTON --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">{{ $programArea->area->area_name }}</h4>
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>

        {{-- Readonly alert --}}
        @if ($isCompleted)
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <i class="bx bx-lock fs-5"></i>
                <span>This accreditation is completed and archived. All records are read-only.</span>
            </div>
        @endif

        {{-- ASSIGNED USERS --}}
        <div class="card mb-4">
            <div class="card-body">

                {{-- ===== INTERNAL ASSESSORS ===== --}}
                @if ($isAdmin || $isIA || $isAccreditor)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">
                            <i class="bx bx-user-check me-2 text-primary"></i>Internal Assessors
                        </h6>
                        @if ($isAdmin && !$isCompleted)
                            <button class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignUserModal">
                                <i class="bx bx-user-plus me-1"></i> Assign
                            </button>
                        @endif
                    </div>

                    @if ($internalAssessors->isEmpty())
                        <div class="empty-state">
                            <i class="bx bx-user-x"></i>
                            No Internal Assessors assigned yet.
                        </div>
                    @else
                        <div class="tf-grid">
                            @foreach ($internalAssessors as $assignment)
                                @php $isYou = $loggedInUser->id === $assignment->user->id; @endphp

                                <div class="tf-card {{ $isYou ? 'is-you' : '' }}">
                                    <x-initials-avatar :user="$assignment->user" size="sm" shape="circle" />

                                    <div class="tf-info">
                                        <div class="tf-name" title="{{ $assignment->user->name }}">
                                            {{ $assignment->user->name }}
                                        </div>
                                        @if ($isYou)
                                            <div class="tf-you-label">You</div>
                                        @endif
                                    </div>

                                    @if ($isAdmin && !$isCompleted)
                                        <button type="button"
                                                class="tf-unassign unassign-btn"
                                                data-id="{{ $assignment->id }}"
                                                data-name="{{ $assignment->user->name }}"
                                                title="Unassign {{ $assignment->user->name }}">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

                {{-- ===== DIVIDER ===== --}}
                @if (($isAdmin || $isIA) && ($isDean || $isTaskForce))
                    <hr class="my-4">
                @endif

                {{-- ===== TASK FORCES ===== --}}
                @if ($isDean || $isTaskForce)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">
                            <i class="bx bx-user-check me-2 text-primary"></i>Task Forces
                        </h6>
                        @if ($isDean && !$isCompleted)
                            <button class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignUserModal">
                                <i class="bx bx-user-plus me-1"></i> Assign
                            </button>
                        @endif
                    </div>

                    @if ($taskForces->isEmpty())
                        <div class="text-center py-3 text-muted">
                            No assigned Task Forces yet.
                        </div>
                    @else
                        <div class="tf-grid">
                            @foreach ($taskForces as $assignment)
                                @php
                                    $isChair = $assignment->role?->value === 'chair';
                                    $isYou   = $loggedInUser->id === $assignment->user->id;
                                @endphp

                                <div class="tf-card {{ $isYou ? 'is-you' : '' }}">
                                    <x-initials-avatar
                                        :user="$assignment->user"
                                        size="sm"
                                        shape="circle"
                                        :role="$assignment->role?->value" />

                                    <div class="tf-info">
                                        <div class="tf-name" title="{{ $assignment->user->name }}">
                                            {{ $assignment->user->name }}
                                        </div>
                                        @if ($isYou)
                                            <div class="tf-you-label">You</div>
                                        @endif
                                        <span class="tf-role-badge {{ $isChair ? 'chair' : 'member' }}">
                                            {{ strtoupper($assignment->role?->value ?? 'MEMBER') }}
                                        </span>
                                    </div>

                                    @if ($isDean && !$isCompleted)
                                        <button type="button"
                                                class="tf-unassign unassign-btn"
                                                data-id="{{ $assignment->id }}"
                                                data-name="{{ $assignment->user->name }}"
                                                title="Unassign {{ $assignment->user->name }}">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- PARAMETERS CARD --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Parameters</h6>

                @if ($isAdmin && !$isCompleted)
                    <div class="d-flex gap-2">
                        @if ($parameters->count() > 0)
                            <button class="btn btn-outline-secondary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editParameterModal">
                                <i class="bx bx-edit"></i> Edit
                            </button>

                            <button class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteParameterModal">
                                <i class="bx bx-trash"></i> Delete
                            </button>
                        @endif

                        <button class="btn btn-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#addParameterModal">
                            <i class="bx bx-plus-circle me-1"></i> Add Parameter
                        </button>
                    </div>
                @endif
            </div>

            <div class="card-body">
                <div class="accordion mt-3" id="parameterAccordion">

                    @forelse($parameters as $index => $parameter)
                        <div class="card accordion-item {{ $index === 0 ? 'active' : '' }}">

                            {{-- Header --}}
                            <h2 class="accordion-header" id="heading{{ $parameter->id }}">
                                <button type="button" class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}"
                                    data-bs-toggle="collapse" data-bs-target="#collapse{{ $parameter->id }}"
                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-controls="collapse{{ $parameter->id }}">

                                    <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                                        <span class="fw-semibold">{{ $parameter->parameter_name }}</span>
                                        <span class="badge bg-label-primary">
                                            {{ $parameter->sub_parameters->count() }}
                                        </span>
                                    </div>
                                </button>
                            </h2>

                            {{-- Body --}}
                            <div id="collapse{{ $parameter->id }}"
                                class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                data-bs-parent="#parameterAccordion">

                                <div class="accordion-body">

                                    @if ($parameter->sub_parameters->isNotEmpty())
                                        @foreach ($parameter->sub_parameters as $sub)
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between align-items-center p-2 border rounded">

                                                    <div class="fw-medium">{{ $sub->sub_parameter_name }}</div>

                                                    <div class="d-flex gap-2">
                                                        @if ($isAdmin && !$isCompleted)
                                                            <button class="btn btn-sm btn-outline-secondary edit-subparam-btn"
                                                                    data-id="{{ $sub->id }}"
                                                                    data-name="{{ $sub->sub_parameter_name }}">
                                                                <i class="bx bx-edit"></i> Edit
                                                            </button>

                                                            <button class="btn btn-sm btn-outline-danger delete-subparam-btn"
                                                                    data-id="{{ $sub->id }}"
                                                                    data-name="{{ $sub->sub_parameter_name }}">
                                                                <i class="bx bx-trash"></i> Delete
                                                            </button>

                                                            <button class="btn btn-sm btn-outline-success add-sub-of-sub-btn"
                                                                    data-sub-param-id="{{ $sub->id }}"
                                                                    data-sub-param-name="{{ $sub->sub_parameter_name }}"
                                                                    title="Add Sub-of-Sub-Parameter">
                                                                <i class="bx bx-list-plus"></i> Add Sub
                                                            </button>
                                                        @endif

                                                        @if ($sub->subSubParameters->isEmpty())
                                                            <a href="{{ route('subparam.uploads.index', [
                                                                'subParameter'  => $sub->id,
                                                                'infoId'        => $infoId,
                                                                'levelId'       => $levelId,
                                                                'programId'     => $programId,
                                                                'programAreaId' => $programAreaId,
                                                            ]) }}"
                                                                class="btn btn-sm btn-outline-primary">
                                                                Open
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Sub-of-Sub --}}
                                                @if ($sub->subSubParameters && $sub->subSubParameters->isNotEmpty())
                                                    <div class="ps-4 mt-1">
                                                        @foreach ($sub->subSubParameters as $subSub)
                                                            <div class="d-flex justify-content-between align-items-center p-2 border rounded bg-light mb-1">

                                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                                    <i class="bx bx-subdirectory-right"></i>
                                                                    <span>{{ $subSub->name }}</span>
                                                                </div>

                                                                <div class="d-flex gap-2 align-items-center">
                                                                    @if ($isAdmin && !$isCompleted)
                                                                        <button class="btn btn-sm btn-outline-secondary edit-sub-of-sub-btn"
                                                                                data-id="{{ $subSub->id }}"
                                                                                data-name="{{ $subSub->name }}">
                                                                            <i class="bx bx-edit"></i>
                                                                        </button>

                                                                        <button class="btn btn-sm btn-outline-danger delete-sub-of-sub-btn"
                                                                                data-id="{{ $subSub->id }}"
                                                                                data-name="{{ $subSub->name }}">
                                                                            <i class="bx bx-trash"></i>
                                                                        </button>
                                                                    @endif

                                                                    <a href="{{ route('subsubparam.uploads.index', [
                                                                        'infoId'            => $infoId,
                                                                        'levelId'           => $levelId,
                                                                        'programId'         => $programId,
                                                                        'programAreaId'     => $programAreaId,
                                                                        'subSubParameterId' => $subSub->id,
                                                                    ]) }}"
                                                                        class="btn btn-sm btn-outline-primary">
                                                                        Open
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-muted fst-italic">No sub-parameters available.</div>
                                    @endif

                                    @if ($isAdmin && !$isCompleted)
                                        <div class="d-flex justify-content-end mt-3">
                                            <button class="btn btn-sm btn-outline-primary add-subparam-direct-btn"
                                                    data-parameter-id="{{ $parameter->id }}">
                                                <i class="bx bx-plus-circle me-1"></i> Add Sub-Parameter
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">No parameters found.</div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>

{{-- All modals and JS only rendered when NOT completed --}}
@if (!$isCompleted)

    {{-- ================= ASSIGN USER MODAL ================= --}}
    <div class="modal fade" id="assignUserModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">
                            Assign {{ $isAdmin ? 'Internal Assessors' : 'Task Forces' }}
                        </h5>
                        <small class="text-muted">{{ $programArea->area->area_name }}</small>
                    </div>
                    <button class="btn-close align-self-start" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($isAdmin)
                        <label class="fw-semibold mb-2">Select Internal Assessors</label>
                        <select class="form-control js-assign-ia-users" id="assignIAUsers" multiple style="width:100%">
                            @foreach($availableIAs as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @if($isDean)
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50%">Task Force</th>
                                    <th style="width:30%">Role</th>
                                    <th style="width:20%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="assignTFTable"></tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" id="addAssignTFRow">
                            <i class="bx bx-plus me-1"></i> Add Task Force
                        </button>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAssignUser">
                        <i class="bx bx-user-check me-1"></i> Assign
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= UNASSIGN USER MODAL ================= --}}
    <div class="modal fade" id="unassignModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Unassign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to unassign <strong id="unassignUserName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <form id="unassignForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Unassign</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= ADD PARAMETERS MODAL ================= --}}
    <div class="modal fade" id="addParameterModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Parameters & Sub-Parameters</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addParametersForm">
                    @csrf
                    <input type="hidden" name="area_id" value="{{ $programArea->area->id }}">
                    <div class="modal-body">
                        <div id="parametersContainer">
                            {{-- First parameter block rendered by default --}}
                            <div class="parameter-block mb-3 border rounded p-3" data-id="1">
                                <div class="d-flex align-items-center mb-2">
                                    <input type="text" name="parameters[1][name]" class="form-control me-2"
                                        placeholder="Parameter Name" required>
                                    <button type="button" class="btn btn-outline-danger remove-parameter"
                                        title="Remove" disabled>
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                                <div class="subparams-container ps-4" data-parameter-id="1"></div>
                                <button type="button" class="btn btn-outline-secondary btn-sm add-subparam mt-2"
                                    data-parameter-id="1">
                                    <i class="bx bx-plus-circle me-1"></i> Add Sub-Parameter
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="addParameterBtn">
                            <i class="bx bx-plus-circle me-1"></i> Add Another Parameter
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save All</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= EDIT PARAMETERS MODAL ================= --}}
    <div class="modal fade" id="editParameterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $parameters->count() > 1 ? 'Edit Parameters' : 'Edit Parameter' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editParameterForm">
                    @csrf
                    <div class="modal-body d-flex flex-column gap-3">
                        @foreach($parameters as $parameter)
                            <div>
                                <input type="text"
                                    name="parameters[{{ $parameter->id }}]"
                                    value="{{ $parameter->parameter_name }}"
                                    class="form-control" required>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= DELETE PARAMETERS MODAL ================= --}}
    <div class="modal fade" id="deleteParameterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Parameters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteParameterForm">
                    @csrf
                    <input type="hidden" name="area_id" value="{{ $programArea->id }}">
                    <div class="modal-body">
                        <p>Select parameters to delete:</p>
                        @foreach($parameters as $parameter)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox"
                                    name="parameters[]" value="{{ $parameter->id }}"
                                    id="param{{ $parameter->id }}">
                                <label class="form-check-label" for="param{{ $parameter->id }}">
                                    {{ $parameter->parameter_name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Selected</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= EDIT SUB-PARAMETERS MODAL ================= --}}
    <div class="modal fade" id="editSubParamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sub-Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editSubParamForm">
                    @csrf
                    <input type="hidden" id="editSubParamId" name="sub_parameter_id">
                    <div class="modal-body mb-3">
                        <label class="form-label">Sub-Parameter Name</label>
                        <input type="text" id="editSubParamName" name="sub_parameter_name" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= DELETE SUB-PARAMETERS MODAL ================= --}}
    <div class="modal fade" id="deleteSubParamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Sub-Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteSubParamName"></strong>?</p>
                    <input type="hidden" id="deleteSubParamId" name="sub_parameter_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteSubParam">Delete</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= ADD SUB-PARAMETER DIRECT MODAL ================= --}}
    <div class="modal fade" id="addSubParamDirectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Sub-Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addSubParamDirectForm">
                    @csrf
                    <input type="hidden" id="addSubParamDirectParamId" name="parameter_id">
                    <div class="modal-body">
                        <div id="addSubParamDirectContainer">
                            <div class="sub-param-direct-row mb-2">
                                <div class="input-group">
                                    <input type="text" name="sub_parameter_names[]" class="form-control"
                                        placeholder="Sub-Parameter Name" required>
                                    <button type="button" class="btn btn-outline-success add-sub-of-sub-direct-inline">
                                        <i class="bx bx-list-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger remove-direct-subparam-row">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                                <div class="direct-sub-of-sub-container ps-4 mt-1"></div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addDirectSubParamRowBtn">
                            <i class="bx bx-plus-circle me-1"></i> Add Another
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= ADD SUB-OF-SUB MODAL ================= --}}
    <div class="modal fade" id="addSubOfSubModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Sub-of-Sub-Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addSubOfSubForm">
                    @csrf
                    <input type="hidden" id="addSubOfSubParamId" name="sub_parameter_id">
                    <div class="modal-body">
                        <p class="text-muted mb-3">Under: <strong id="addSubOfSubParamName"></strong></p>
                        <div id="subOfSubContainer"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="addSubOfSubRowBtn">
                            <i class="bx bx-plus-circle me-1"></i> Add Another
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= EDIT SUB-OF-SUB MODAL ================= --}}
    <div class="modal fade" id="editSubOfSubModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sub-of-Sub-Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editSubOfSubForm">
                    @csrf
                    <input type="hidden" id="editSubOfSubId" name="sub_of_sub_parameter_id">
                    <div class="modal-body mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="editSubOfSubName" name="name" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= DELETE SUB-OF-SUB MODAL ================= --}}
    <div class="modal fade" id="deleteSubOfSubModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Sub-of-Sub-Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteSubOfSubName"></strong>?</p>
                    <input type="hidden" id="deleteSubOfSubId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteSubOfSub">Delete</button>
                </div>
            </div>
        </div>
    </div>

@endif
{{-- End modals guard --}}

@push('scripts')
<script>
$(function () {
    @if (!$isCompleted)

    // =====================================================
    // ASSIGN USER MODAL
    // =====================================================
    const ALL_TF_USERS = [
        @foreach($availableTFs as $u)
            { id: "{{ $u->id }}", name: "{{ addslashes($u->name) }}" },
        @endforeach
    ];

    let assignTFIndex = 0;

    @if($isAdmin)
    $('.js-assign-ia-users').select2({
        dropdownParent: $('#assignUserModal'),
        width: '100%',
        placeholder: 'Select Internal Assessors...',
        allowClear: true,
        closeOnSelect: false,
    });
    @endif

    function initTFSelect2(row) {
        row.find('.tf-select-user').select2({ dropdownParent: $('#assignUserModal'), width: '100%' });
    }

    function getSelectedTFUsers() {
        return $('.tf-select-user').map(function () { return $(this).val(); }).get().filter(Boolean);
    }

    function updateTFDropdowns() {
        const selected = getSelectedTFUsers();
        $('.tf-select-user').each(function () {
            const current = $(this).val();
            const $sel = $(this);
            $sel.empty().append('<option value="">Select Task Force</option>');
            ALL_TF_USERS.forEach(u => {
                if (!selected.includes(u.id) || u.id === current) {
                    $sel.append(`<option value="${u.id}">${u.name}</option>`);
                }
            });
            $sel.val(current).trigger('change.select2');
        });
    }

    function updateTFChairAvailability() {
        const chairExists = $('.tf-role-select').toArray().some(el => $(el).val() === 'chair');
        $('.tf-role-select').each(function () {
            const isCurrent = $(this).val() === 'chair';
            $(this).find('option[value="chair"]').prop('disabled', chairExists && !isCurrent);
        });
    }

    $('#addAssignTFRow').on('click', function () {
        const row = $(`
            <tr>
                <td><select name="tf_users[${assignTFIndex}][id]" class="form-select form-select-sm tf-select-user" required>
                    <option value="">Select Task Force</option></select></td>
                <td><select name="tf_users[${assignTFIndex}][role]" class="form-select form-select-sm tf-role-select" required>
                    <option value="member" selected>Member</option>
                    <option value="chair">Chair</option></select></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-tf-row">
                        <i class="bx bx-trash"></i></button></td>
            </tr>
        `);
        $('#assignTFTable').append(row);
        initTFSelect2(row);
        assignTFIndex++;
        updateTFDropdowns();
        updateTFChairAvailability();
    });

    $(document).on('change', '.tf-select-user', updateTFDropdowns);
    $(document).on('change', '.tf-role-select', updateTFChairAvailability);
    $(document).on('click', '.remove-tf-row', function () {
        $(this).closest('tr').remove();
        updateTFDropdowns();
        updateTFChairAvailability();
    });

    $('#assignUserModal').on('show.bs.modal', function () {
        @if($isAdmin)
            $('.js-assign-ia-users').val(null).trigger('change');
        @endif
        @if($isDean)
            $('#assignTFTable').empty();
            assignTFIndex = 0;
        @endif
    });

    $('#confirmAssignUser').on('click', function () {
        @if($isAdmin)
            const userIds = $('.js-assign-ia-users').val();
            if (!userIds || userIds.length === 0) { showToast('Please select at least one Internal Assessor.', 'error'); return; }
            const users = userIds.map(id => parseInt(id));
        @elseif($isDean)
            const rows = $('#assignTFTable tr');
            if (rows.length === 0) { showToast('Please add at least one Task Force.', 'error'); return; }
            let valid = true, users = [], chairCount = 0, userIdsSeen = [];
            rows.each(function () {
                const id   = $(this).find('.tf-select-user').val();
                const role = $(this).find('.tf-role-select').val();
                if (!id || !role) { valid = false; return false; }
                if (userIdsSeen.includes(id)) { valid = false; showToast('A user can only be assigned once.', 'error'); return false; }
                userIdsSeen.push(id);
                if (role === 'chair') chairCount++;
                users.push({ id: parseInt(id), role });
            });
            if (!valid) return;
            if (chairCount > 1) { showToast('Only one Chair is allowed per area.', 'error'); return; }
        @endif

        $.ajax({
            url: "{{ route('areas.assign.users') }}",
            type: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            data: JSON.stringify({
                area_id:               {{ $programAreaId }},
                program_id:            {{ $programId }},
                level_id:              {{ $levelId }},
                accreditation_info_id: {{ $infoId }},
                users:                 users,
            }),
            success: function (res) { showToast(res.message || 'Assigned successfully.', 'success'); $('#assignUserModal').modal('hide'); location.reload(); },
            error:   function (xhr) { showToast(xhr.responseJSON?.message || 'Something went wrong.', 'error'); }
        });
    });

    const csrfToken = '{{ csrf_token() }}';

    // Start at 1 since the first block is pre-rendered in the modal
    let parameterCount = 1;

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    // =====================================================
    // ADD PARAMETER
    // =====================================================
    $(document).on('click', '#addParameterBtn', function () {
        parameterCount++;

        // Enable the remove button on the first block once a second one is added
        $('#parametersContainer .parameter-block:first-child .remove-parameter').prop('disabled', false);

        $('#parametersContainer').append(`
            <div class="parameter-block mb-3 border rounded p-3" data-id="${parameterCount}">
                <div class="d-flex align-items-center mb-2">
                    <input type="text" name="parameters[${parameterCount}][name]" class="form-control me-2"
                        placeholder="Parameter Name" required>
                    <button type="button" class="btn btn-outline-danger remove-parameter" title="Remove">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="subparams-container ps-4" data-parameter-id="${parameterCount}"></div>
                <button type="button" class="btn btn-outline-secondary btn-sm add-subparam mt-2"
                    data-parameter-id="${parameterCount}">
                    <i class="bx bx-plus-circle me-1"></i> Add Sub-Parameter
                </button>
            </div>
        `);
    });

    $(document).on('click', '.unassign-btn', function () {
        $('#unassignUserName').text($(this).data('name'));
        $('#unassignForm').attr('action', "{{ url('assignments/unassign') }}/" + $(this).data('id'));
        $('#unassignModal').modal('show');
    });

    $(document).on('click', '.remove-parameter', function () {
        $(this).closest('.parameter-block').remove();

        // Re-disable the remove button if only one block remains
        if ($('#parametersContainer .parameter-block').length === 1) {
            $('#parametersContainer .parameter-block:first-child .remove-parameter').prop('disabled', true);
        }
    });

    $(document).on('click', '.add-subparam', function () {
        const paramId = $(this).data('parameter-id');
        const container = $(`.subparams-container[data-parameter-id="${paramId}"]`);
        const subCount = container.children('.sub-param-block').length + 1;
        container.append(`
            <div class="sub-param-block mb-2" data-sub-id="${subCount}">
                <div class="input-group mb-1">
                    <input type="text" name="parameters[${paramId}][sub_parameters][${subCount}][name]" class="form-control" placeholder="Sub-Parameter Name" required>
                    <button type="button" class="btn btn-outline-success add-sub-of-sub-inline" data-param-id="${paramId}" data-sub-id="${subCount}"><i class="bx bx-list-plus"></i></button>
                    <button type="button" class="btn btn-outline-danger remove-subparam"><i class="bx bx-x"></i></button>
                </div>
                <div class="sub-of-sub-container ps-4" data-param-id="${paramId}" data-sub-id="${subCount}"></div>
            </div>
        `);
    });

    $(document).on('click', '.add-sub-of-sub-inline', function () {
        const paramId = $(this).data('param-id');
        const subId   = $(this).data('sub-id');
        const container = $(`.sub-of-sub-container[data-param-id="${paramId}"][data-sub-id="${subId}"]`);
        const count = container.children().length + 1;
        container.append(`
            <div class="input-group mb-1">
                <span class="input-group-text bg-light text-muted"><i class="bx bx-subdirectory-right"></i></span>
                <input type="text" name="parameters[${paramId}][sub_parameters][${subId}][sub_of_sub][${count}]" class="form-control form-control-sm" placeholder="Sub-of-Sub-Parameter Name" required>
                <button type="button" class="btn btn-outline-danger remove-sub-of-sub-inline"><i class="bx bx-x"></i></button>
            </div>
        `);
    });

    $(document).on('click', '.remove-subparam', function () { $(this).closest('.sub-param-block').remove(); });
    $(document).on('click', '.remove-sub-of-sub-inline', function () { $(this).closest('.input-group').remove(); });

    // Reset modal to a clean single block when closed
    $('#addParameterModal').on('hidden.bs.modal', function () {
        parameterCount = 1;
        $('#parametersContainer').html(`
            <div class="parameter-block mb-3 border rounded p-3" data-id="1">
                <div class="d-flex align-items-center mb-2">
                    <input type="text" name="parameters[1][name]" class="form-control me-2"
                        placeholder="Parameter Name" required>
                    <button type="button" class="btn btn-outline-danger remove-parameter"
                        title="Remove" disabled>
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="subparams-container ps-4" data-parameter-id="1"></div>
                <button type="button" class="btn btn-outline-secondary btn-sm add-subparam mt-2"
                    data-parameter-id="1">
                    <i class="bx bx-plus-circle me-1"></i> Add Sub-Parameter
                </button>
            </div>
        `);
    });

    function ajaxFormSubmit(formSelector, url, successMessage, modalToClose = null, method = 'POST') {
        $(document).on('submit', formSelector, function(e) {
            e.preventDefault();
            $.ajax({
                url, type: method, data: $(this).serialize(),
                success: function(res) { showToast(res.message || successMessage, 'success'); if (modalToClose) $(modalToClose).modal('hide'); location.reload(); },
                error:   function(xhr) { showToast(xhr.responseJSON?.message || 'Something went wrong', 'error'); }
            });
        });
    }

    ajaxFormSubmit('#addParametersForm', "{{ route('program-area.parameters.store', ['areaId' => $programArea->id]) }}", 'Parameters added successfully', '#addParameterModal', 'POST');
    ajaxFormSubmit('#editParameterForm', "{{ route('parameters.bulk-update') }}", 'Parameters updated successfully', '#editParameterModal', 'PATCH');
    ajaxFormSubmit('#deleteParameterForm', "{{ route('parameters.bulk-delete') }}", 'Parameters deleted successfully', '#deleteParameterModal', 'DELETE');

    $('#editSubParamForm').submit(function(e) {
        e.preventDefault();
        $.ajax({ url: '/subparameters/' + $('#editSubParamId').val(), type: 'PATCH', data: $(this).serialize(),
            success: function(res) { showToast(res.message, 'success'); $('#editSubParamModal').modal('hide'); location.reload(); },
            error:   function(xhr) { showToast(xhr.responseJSON?.message || 'Something went wrong', 'error'); }
        });
    });

    $('#confirmDeleteSubParam').click(function() {
        $.ajax({ url: '/subparameters/' + $('#deleteSubParamId').val(), type: 'DELETE', data: { _token: '{{ csrf_token() }}' },
            success: function(res) { showToast(res.message, 'success'); $('#deleteSubParamModal').modal('hide'); location.reload(); },
            error:   function(xhr) { showToast(xhr.responseJSON?.message || 'Something went wrong', 'error'); }
        });
    });

    $(document).on('click', '.edit-subparam-btn', function () { $('#editSubParamId').val($(this).data('id')); $('#editSubParamName').val($(this).data('name')); $('#editSubParamModal').modal('show'); });
    $(document).on('click', '.delete-subparam-btn', function () { $('#deleteSubParamId').val($(this).data('id')); $('#deleteSubParamName').text($(this).data('name')); $('#deleteSubParamModal').modal('show'); });

    $(document).on('click', '.add-subparam-direct-btn', function () {
        $('#addSubParamDirectParamId').val($(this).data('parameter-id'));
        $('#addSubParamDirectContainer').html(`
            <div class="sub-param-direct-row mb-2">
                <div class="input-group">
                    <input type="text" name="sub_parameter_names[]" class="form-control" placeholder="Sub-Parameter Name" required>
                    <button type="button" class="btn btn-outline-success add-sub-of-sub-direct-inline"><i class="bx bx-list-plus"></i></button>
                    <button type="button" class="btn btn-outline-danger remove-direct-subparam-row"><i class="bx bx-x"></i></button>
                </div>
                <div class="direct-sub-of-sub-container ps-4 mt-1"></div>
            </div>
        `);
        $('#addSubParamDirectModal').modal('show');
    });

    $('#addDirectSubParamRowBtn').click(function () {
        $('#addSubParamDirectContainer').append(`
            <div class="sub-param-direct-row mb-2">
                <div class="input-group">
                    <input type="text" name="sub_parameter_names[]" class="form-control" placeholder="Sub-Parameter Name" required>
                    <button type="button" class="btn btn-outline-success add-sub-of-sub-direct-inline"><i class="bx bx-list-plus"></i></button>
                    <button type="button" class="btn btn-outline-danger remove-direct-subparam-row"><i class="bx bx-x"></i></button>
                </div>
                <div class="direct-sub-of-sub-container ps-4 mt-1"></div>
            </div>
        `);
    });

    $(document).on('click', '.remove-direct-subparam-row', function () {
        if ($('#addSubParamDirectContainer .sub-param-direct-row').length > 1) $(this).closest('.sub-param-direct-row').remove();
    });

    $(document).on('click', '.add-sub-of-sub-direct-inline', function () {
        const container = $(this).closest('.sub-param-direct-row').find('.direct-sub-of-sub-container');
        const count = container.children().length + 1;
        container.append(`
            <div class="input-group mb-1">
                <span class="input-group-text bg-light text-muted"><i class="bx bx-subdirectory-right"></i></span>
                <input type="text" name="sub_of_sub_names[${count}][]" class="form-control form-control-sm" placeholder="Sub-of-Sub-Parameter Name">
                <button type="button" class="btn btn-outline-danger remove-direct-sub-of-sub-row"><i class="bx bx-x"></i></button>
            </div>
        `);
    });

    $(document).on('click', '.remove-direct-sub-of-sub-row', function () { $(this).closest('.input-group').remove(); });

    $('#addSubParamDirectForm').submit(function (e) {
        e.preventDefault();
        $.ajax({ url: '/parameters/' + $('#addSubParamDirectParamId').val() + '/sub-parameters', type: 'POST', data: $(this).serialize(),
            success: function(res) { showToast(res.message || 'Sub-Parameter added successfully', 'success'); $('#addSubParamDirectModal').modal('hide'); location.reload(); },
            error:   function(xhr) { showToast(xhr.responseJSON?.message || 'Something went wrong', 'error'); }
        });
    });

    $(document).on('click', '.add-sub-of-sub-btn', function () {
        $('#addSubOfSubParamId').val($(this).data('sub-param-id'));
        $('#addSubOfSubParamName').text($(this).data('sub-param-name'));
        $('#subOfSubContainer').empty();
        addSubOfSubRow();
        $('#addSubOfSubModal').modal('show');
    });

    function addSubOfSubRow() {
        $('#subOfSubContainer').append(`
            <div class="input-group mb-2">
                <span class="input-group-text bg-light text-muted"><i class="bx bx-subdirectory-right"></i></span>
                <input type="text" name="sub_of_sub_names[]" class="form-control" placeholder="Sub-of-Sub-Parameter Name" required>
                <button type="button" class="btn btn-outline-danger remove-sub-of-sub-row"><i class="bx bx-x"></i></button>
            </div>
        `);
    }

    $('#addSubOfSubRowBtn').click(addSubOfSubRow);
    $(document).on('click', '.remove-sub-of-sub-row', function () { if ($('#subOfSubContainer .input-group').length > 1) $(this).closest('.input-group').remove(); });

    $('#addSubOfSubForm').submit(function(e) {
        e.preventDefault();
        $.ajax({ url: '/subparameters/' + $('#addSubOfSubParamId').val() + '/sub-of-sub', type: 'POST', data: $(this).serialize(),
            success: function(res) { showToast(res.message || 'Added successfully', 'success'); $('#addSubOfSubModal').modal('hide'); location.reload(); },
            error:   function(xhr) { showToast(xhr.responseJSON?.message || 'Something went wrong', 'error'); }
        });
    });

    $(document).on('click', '.edit-sub-of-sub-btn', function () { $('#editSubOfSubId').val($(this).data('id')); $('#editSubOfSubName').val($(this).data('name')); $('#editSubOfSubModal').modal('show'); });

    $('#editSubOfSubForm').submit(function(e) {
        e.preventDefault();
        $.ajax({ url: '/sub-of-sub-parameters/' + $('#editSubOfSubId').val(), type: 'PATCH', data: $(this).serialize(),
            success: function(res) { showToast(res.message || 'Updated successfully', 'success'); $('#editSubOfSubModal').modal('hide'); location.reload(); },
            error:   function(xhr) { showToast(xhr.responseJSON?.message || 'Something went wrong', 'error'); }
        });
    });

    $(document).on('click', '.delete-sub-of-sub-btn', function () { $('#deleteSubOfSubId').val($(this).data('id')); $('#deleteSubOfSubName').text($(this).data('name')); $('#deleteSubOfSubModal').modal('show'); });

    $('#confirmDeleteSubOfSub').click(function() {
        $.ajax({ url: '/sub-of-sub-parameters/' + $('#deleteSubOfSubId').val(), type: 'DELETE', data: { _token: '{{ csrf_token() }}' },
            success: function(res) { showToast(res.message || 'Deleted successfully', 'success'); $('#deleteSubOfSubModal').modal('hide'); location.reload(); },
            error:   function(xhr) { showToast(xhr.responseJSON?.message || 'Something went wrong', 'error'); }
        });
    });

    @endif
    {{-- End completed JS guard --}}
});
</script>
@endpush

@endsection