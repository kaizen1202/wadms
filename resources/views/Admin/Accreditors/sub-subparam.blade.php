@extends('admin.layouts.master')

@section('contents')

@php
    use App\Enums\UserType;
    $user = auth()->user();
    $isAdmin = $user->currentRole->name === UserType::ADMIN->value;
    $isIA = $user->currentRole->name === UserType::INTERNAL_ASSESSOR->value;
    $isCompleted = $accreditationStatus === 'completed';

    $routeParams = [
        'infoId'       => $infoId,
        'levelId'      => $levelId,
        'programId'    => $programId,
        'programAreaId' => $programAreaId,
    ];

    // Determine back URL
    $backUrl = $isIA
        ? route('program.areas.evaluation', $routeParams)
        : route('program.areas.parameters', $routeParams);
@endphp

    <div class="container-xxl container-p-y">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    {{ $subSubParameter->name }}
                </h4>
                <p class="text-muted mb-0">
                    Upload documents for this sub-sub-parameter
                </p>
            </div>
            <a href="{{ $backUrl }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>
                Back
            </a>
        </div>

        {{-- Readonly alert --}}
        @if ($isCompleted)
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <i class="bx bx-lock fs-5"></i>
                <span>This accreditation is completed and archived. All records are read-only.</span>
            </div>
        @endif

        {{-- Upload Card --}}
        <div class="card mb-4">
            @if (!$isCompleted && ($user->currentRole->name === UserType::DEAN->value
                    || $user->currentRole->name === UserType::TASK_FORCE->value
                ))
                <div class="card-body">
                    <form
                        action="{{ route('subsubparam.uploads.store', [
                            'infoId'            => $infoId,
                            'levelId'           => $levelId,
                            'programId'         => $programId,
                            'programAreaId'     => $programAreaId,
                            'subSubParameterId' => $subSubParameter->id,
                        ]) }}"
                        id="uploadForm"
                        method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Upload Files</label>

                            {{-- Drop zone --}}
                            <div id="dropZone"
                                onclick="document.getElementById('fileInput').click()"
                                ondragenter="event.preventDefault(); this.style.borderColor='#0d6efd'; this.style.background='#eff6ff';"
                                ondragover="event.preventDefault(); event.stopPropagation();"
                                ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';"
                                ondrop="handleDrop(event)"
                                style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 2rem 1.5rem;
                                    background: #f8fafc; cursor: pointer; transition: all .2s;
                                    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .5rem;">

                                <div style="width:48px; height:48px; background:#e0eaff; border-radius:50%;
                                            display:flex; align-items:center; justify-content:center;">
                                    <i class="bx bx-cloud-upload" style="font-size:1.5rem; color:#0d6efd;"></i>
                                </div>
                                <div class="text-center">
                                    <span class="fw-semibold text-dark" style="font-size:.9rem;">Click to choose files or drag & drop</span><br>
                                    <small class="text-muted">PDF files only · Max 10MB each</small>
                                </div>
                            </div>

                            {{-- Hidden input --}}
                            <input type="file" id="fileInput" name="files[]" multiple required
                                accept="application/pdf" style="display:none;"
                                onchange="handleFiles(this.files)">

                            {{-- File list --}}
                            <ul id="fileList" class="list-unstyled mt-3 mb-0" style="display:none;"></ul>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-primary px-4" onclick="submitUpload()">
                                <i class="bx bx-upload me-1"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        {{-- Uploaded Files --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between">
                <h6 class="fw-bold mb-0">
                    Uploaded Files ({{ $uploads->count() }})
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>File Name</th>
                            <th>Type</th>
                            <th>Uploaded By</th>
                            <th>Uploader Role</th>
                            <th>Uploaded At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($uploads as $index => $upload)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $upload->file_name }}</td>
                                <td>{{ strtoupper($upload->file_type) }}</td>
                                <td>
                                    {{ $upload->uploader?->name ?? 'Unknown' }}

                                    @if ($upload->uploader && $upload->uploader->id === auth()->id())
                                        <span class="text-muted">(You)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ ucfirst($upload->uploaderRole->name ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>{{ $upload->created_at->format('M d, Y, h:i A') }}</td>
                                <td class="d-flex gap-1">

                                    {{-- VIEW --}}
                                    <a href="{{ Storage::url($upload->file_path) }}"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i>
                                        View
                                    </a>

                                    {{-- DELETE (only uploader can delete, and only when not completed) --}}
                                    @if (!$isCompleted && $upload->uploader && $upload->uploader->id === auth()->id() && $upload->uploaderRole->id === auth()->user()->current_role_id)
                                        <button
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="{{ $upload->id }}"
                                            data-url="{{ route('subsubparam.uploads.destroy', $upload->id) }}"
                                            data-name="{{ $upload->file_name }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteModal">
                                            <i class="bx bx-trash"></i>
                                            Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No files uploaded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- DELETE CONFIRMATION MODAL --}}
    @if (!$isCompleted)
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            Confirm Delete
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p>
                            Are you sure you want to delete <strong id="fileName"></strong> file?
                        </p>

                        <p class="text-danger mt-2 mb-0">
                            This action cannot be undone.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit"
                                class="btn btn-danger">
                            Yes, Delete
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    @endif

@push('scripts')
<script>
$(document).ready(function () {
    $('#deleteModal').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let url    = button.data('url');
        let name   = button.data('name');
        $('#deleteForm').attr('action', url);
        $('#fileName').text(name);
    });
});

let selectedFiles = []; // ✅ plain array, works everywhere

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    const zone = document.getElementById('dropZone');
    zone.style.borderColor = '#cbd5e1';
    zone.style.background  = '#f8fafc';
    handleFiles(e.dataTransfer.files);
}

function handleFiles(incoming) {
    for (const file of incoming) {
        if (file.type !== 'application/pdf') {
            alert(`"${file.name}" is not a PDF and was skipped.`);
            continue;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert(`"${file.name}" exceeds 10MB and was skipped.`);
            continue;
        }
        // Duplicate check
        if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
            selectedFiles.push(file);
        }
    }
    renderList();
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    renderList();
}

function submitUpload() {
    if (selectedFiles.length === 0) {
        alert('Please select at least one file.');
        return;
    }

    const form = document.getElementById('uploadForm');
    const formData = new FormData(form); // grabs CSRF token etc.

    selectedFiles.forEach(file => {
        formData.append('files[]', file);
    });

    fetch(form.action, {
        method: 'POST',
        body: formData,
    })
    .then(res => {
        if (res.redirected) {
            window.location.href = res.url;
        } else if (!res.ok) {
            return res.json().then(data => { throw new Error(data.message || 'Upload failed'); });
        } else {
            window.location.reload();
        }
    })
    .catch(err => alert(err.message));
}

function renderList() {
    const list = document.getElementById('fileList');
    list.innerHTML = '';

    if (selectedFiles.length === 0) {
        list.style.display = 'none';
        return;
    }

    list.style.display = 'block';

    selectedFiles.forEach((file, i) => {
        const size = (file.size / 1024).toFixed(1) + ' KB';
        const li = document.createElement('li');
        li.style.cssText = 'display:flex; align-items:center; gap:.75rem; padding:.6rem .75rem; background:#fff; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:.5rem;';
        li.innerHTML = `
            <div style="width:36px;height:36px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bx bxs-file-pdf" style="color:#ef4444;font-size:1.1rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="fw-semibold text-truncate text-dark" style="font-size:.82rem;">${file.name}</div>
                <div class="text-muted" style="font-size:.72rem;">${size}</div>
            </div>
            <button type="button" onclick="removeFile(${i})"
                    style="border:none;background:none;color:#94a3b8;cursor:pointer;padding:.25rem;line-height:1;"
                    title="Remove">
                <i class="bx bx-x" style="font-size:1.1rem;"></i>
            </button>
        `;
        list.appendChild(li);
    });
}
</script>
@endpush

@endsection