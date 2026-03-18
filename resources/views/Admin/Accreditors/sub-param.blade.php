@extends('admin.layouts.master')

@section('contents')

@php
    use App\Enums\UserType;
    $user = auth()->user();
    $isAdmin = $user->currentRole->name === UserType::ADMIN->value;
    $isIA = $user->currentRole->name === UserType::INTERNAL_ASSESSOR->value;

    $routeParams = [
        'infoId' => $infoId,
        'levelId' => $levelId,
        'programId' => $programId,
        'programAreaId' => $programAreaId
    ];

    // Determine back URL
    $backUrl = $isIA
        ? route('program.areas.evaluation', $routeParams)
        : route('program.areas.parameters', $routeParams);
@endphp

    <div class="container-xxl container-p-y">

        {{-- Breadcrumb --}}
        <div class="mb-3">
            <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary">
                ← Back
            </a>
        </div>

        {{-- Header --}}
        <h4 class="fw-bold mb-1">
            {{ $subParameter->sub_parameter_name }}
        </h4>
        <p class="text-muted mb-4">
            Upload documents for this sub-parameter
        </p>
        
        {{-- Upload Card --}}
        <div class="card mb-4">
            @if ($user->currentRole->name === UserType::DEAN->value
                    || $user->currentRole->name === UserType::TASK_FORCE->value
                )
                <div class="card-body">
                    <form
                        action="{{ route('subparam.uploads.store', [
                            'subParameter' => $subParameter->id,
                            'infoId' => $infoId,
                            'levelId' => $levelId,
                            'programId' => $programId,
                            'programAreaId' => $programAreaId,
                        ]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Upload Files</label>
                            <input type="file" name="files[]" class="form-control" multiple required>

                            <small class="text-muted">
                                Multiple files allowed (PDF, DOCX, Images).
                            </small>
                        </div>

                        <button class="btn btn-primary">
                            <i class="bx bx-upload me-1"></i> Upload
                        </button>
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
                                <td>{{ $upload->created_at->format('M d, Y') }}</td>
                                <td class="d-flex gap-1">

                                    {{-- VIEW --}}
                                    <a href="{{ Storage::url($upload->file_path) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-show"></i>
                                        View
                                    </a>

                                    {{-- DELETE (only uploader can delete) --}}
                                    @if ($upload->uploader && $upload->uploader->id === auth()->id() && $upload->uploaderRole->id === auth()->user()->current_role_id)
                                        <button 
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="{{ $upload->id }}"
                                            data-url="{{ route('subparam.uploads.destroy', $upload->id) }}"
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
                                <td colspan="6" class="text-center text-muted">
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
@push('scripts')
<script>
$(document).ready(function () {

    $('#deleteModal').on('show.bs.modal', function (event) {

        let button = $(event.relatedTarget); // Button that triggered modal
        let url = button.data('url');        // Get delete URL
        let name = button.data('name');      // Get file name

        $('#deleteForm').attr('action', url); // Set form action
        $('#fileName').text(name);            // Set file name text

    });

});
</script>
@endpush
@endsection
