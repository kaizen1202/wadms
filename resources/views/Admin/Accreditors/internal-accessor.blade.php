@extends('admin.layouts.master')

@section('contents')
<div class="container-xxl container-p-y">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Accreditation Overview</h4>
            <small class="text-muted">Ongoing accreditation programs and evaluation progress</small>
        </div>
    </div>

    @if (empty($data))
        <div class="card">
            <div class="card-body empty-state">
                <i class="bx bx-folder-open"></i>
                No ongoing accreditations available.
            </div>
        </div>
    @else
        @foreach ($data as $levelName => $levelInfo)
            <div class="mb-5">

                {{-- Level Heading --}}
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bx bx-bar-chart-alt-2 text-primary"></i>
                    <h5 class="fw-bold mb-0 text-primary">{{ $levelName }}</h5>
                </div>

                <div class="row g-3">
                    @forelse ($levelInfo['programs'] as $program)
                        @php
                            $progress = $program['progress'];
                            $statusLabel = $program['accreditation_status_label'];

                            $progressColor = $progress >= 100
                                ? 'bg-success'
                                : ($progress > 0 ? 'bg-warning' : 'bg-secondary');

                            $badgeClass = $statusLabel === 'Completed'
                                ? 'bg-success'
                                : ($statusLabel === 'Ongoing' ? 'bg-warning text-dark' : 'bg-secondary');
                        @endphp

                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm d-flex flex-column"
                                 style="border-radius: 10px; border-color: #e2e8f0;">

                                {{-- Card Header --}}
                                <div class="card-header border-0 pb-0 pt-3 px-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted mb-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: .5px;">
                                                {{ $program['accreditation_title'] }}
                                            </p>
                                            <h6 class="fw-bold mb-0" style="font-size: 15px; line-height: 1.3;">
                                                {{ $program['program_name'] ?? 'Unnamed Program' }}
                                            </h6>
                                        </div>
                                        <span class="badge {{ $badgeClass }} ms-2 flex-shrink-0">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Card Body --}}
                                <div class="card-body px-3 pt-3 pb-2 flex-grow-1">

                                    {{-- Progress --}}
                                    <div class="mb-1 d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Evaluation Progress</small>
                                        <small class="fw-semibold" style="font-size: 11px;">
                                            {{ $progress }}%
                                        </small>
                                    </div>
                                    <div class="progress mb-2" style="height: 6px; border-radius: 999px;">
                                        <div class="progress-bar {{ $progressColor }}"
                                             style="width: {{ $progress }}%; border-radius: 999px;">
                                        </div>
                                    </div>
                                    <small class="text-muted" style="font-size: 11px;">
                                        <i class="bx bx-layer me-1"></i>
                                        {{ $program['evaluated_areas'] }} / {{ $program['total_areas'] }} areas evaluated
                                    </small>

                                </div>

                                {{-- Card Footer --}}
                                <div class="card-footer border-0 px-3 pb-3 pt-0">
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('internal.accessor.program.areas', [
                                                $program['accreditation_id'],
                                                $levelInfo['level_id'],
                                                $program['program_id'],
                                            ]) }}"
                                           class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bx bx-folder-open me-1"></i> View Program
                                        </a>

                                        @if ($canEvaluate && $progress == 100)
                                            <button class="btn btn-sm btn-primary w-100 open-final-verdict"
                                                    data-program="{{ $program['program_name'] }}"
                                                    data-program-id="{{ $program['program_id'] }}"
                                                    data-accred-info-id="{{ $program['accreditation_id'] }}"
                                                    data-level-id="{{ $levelInfo['level_id'] }}">
                                                <i class="bx bx-check-shield me-1"></i> Final Verdict
                                            </button>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

                    @empty
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body empty-state">
                                    <i class="bx bx-folder-open"></i>
                                    No programs available for this level.
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

            </div>
        @endforeach
    @endif

</div>

{{-- ===== FINAL VERDICT MODAL ===== --}}
<div class="modal fade" id="finalVerdictModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form id="finalVerdictForm" class="modal-content">

            <input type="hidden" id="fvProgramId">
            <input type="hidden" id="fvAccredInfoId">
            <input type="hidden" id="fvLevelId">

            <div class="modal-header">
                <h5 class="modal-title">Final Accreditation Verdict</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Submitting the final accreditation decision for:
                    <strong id="fvProgram" class="text-dark d-block mt-1"></strong>
                </p>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Accreditation Status</label>
                    <select class="form-select" id="fvStatus" required>
                        <option value="" selected disabled>Select status</option>
                        <option value="revisit">Revisit</option>
                        <option value="completed">Completed / Granted</option>
                    </select>
                </div>

                <div class="mb-3 d-none" id="revisitYearContainer">
                    <label class="form-label fw-semibold">Revisit Until (Year)</label>
                    <input type="number"
                           class="form-control"
                           id="fvRevisitYear"
                           min="2022" max="2255"
                           placeholder="e.g. 2028">
                    <div class="form-text text-muted">
                        Year by which the program must be revisited.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Comments / Justification</label>
                    <textarea class="form-control"
                              id="fvComments"
                              rows="4"
                              placeholder="Provide justification for your decision..."
                              required></textarea>
                </div>

                <div class="alert alert-warning small mb-0">
                    <i class="bx bx-error me-1"></i>
                    This action finalizes the accreditation evaluation and cannot be undone.
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-check-shield me-1"></i> Submit Final Verdict
                </button>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {

    $(document).on('click', '.open-final-verdict', function () {
        $('#fvProgram').text($(this).data('program'));
        $('#fvProgramId').val($(this).data('program-id'));
        $('#fvAccredInfoId').val($(this).data('accred-info-id'));
        $('#fvLevelId').val($(this).data('level-id'));
        $('#fvStatus').val('');
        $('#fvComments').val('');
        $('#fvRevisitYear').val('');
        $('#revisitYearContainer').addClass('d-none');
        $('#finalVerdictModal').modal('show');
    });

    $('#fvStatus').on('change', function () {
        const isRevisit = $(this).val() === 'revisit';
        $('#revisitYearContainer').toggleClass('d-none', !isRevisit);
        $('#fvRevisitYear').prop('required', isRevisit);
        if (!isRevisit) $('#fvRevisitYear').val('');
    });

    $('#finalVerdictForm').on('submit', function (e) {
        e.preventDefault();

        if (!$('#fvStatus').val() || !$('#fvComments').val().trim()) {
            showToast('Please complete all required fields.', 'error');
            return;
        }

        $.ajax({
            url: "{{ route('internal.final.verdict.store') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                program_id:     $('#fvProgramId').val(),
                accred_info_id: $('#fvAccredInfoId').val(),
                level_id:       $('#fvLevelId').val(),
                status:         $('#fvStatus').val(),
                revisit_year:   $('#fvRevisitYear').val(),
                comments:       $('#fvComments').val()
            },
            success: function () {
                $('#finalVerdictModal').modal('hide');
                showToast('Final verdict saved successfully.', 'success');
                location.reload();
            },
            error: function () {
                showToast('Failed to save final verdict.', 'error');
            }
        });
    });

});
</script>
@endpush