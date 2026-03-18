@extends('admin.layouts.master')

@section('contents')

@php
    use App\Enums\UserType;
    use App\Enums\EvaluationStatus;
    $user = auth()->user();

    $subHeader = match ($user->currentRole->name) {
        UserType::TASK_FORCE->value =>
            "Evaluation of Internal Assessor for areas you're assigned to",

        UserType::INTERNAL_ASSESSOR->value => 
            "Evaluation you've made",

        UserType::DEAN->value,
        UserType::ADMIN->value,
        UserType::ACCREDITOR->value =>
            'Evaluations of Internal Assessor',

        default => '',
    };
@endphp

<div class="container-xxl container-p-y">

    <h2 class="fw-bold">Evaluations</h2>
    <p class="text-muted mb-4">{{ $subHeader }}</p>

    @forelse ($evaluations as $key => $group)
        @php
            $first = $group->first();

            // Only internal assessor evaluations
            $internal = $group->filter(fn ($e) => $e->role_id === $internalAssessorRoleId);

            // Group recommendations by area
            $areaGroups = $internal->flatMap(fn ($e) => 
                $e->areaRecommendations->map(fn($rec) => [
                    'area' => $rec->area,
                    'evaluation' => $e,
                ])
            )->groupBy(fn($item) => $item['area']->id);
        @endphp

        <div class="card mb-4">
            <div class="card-body">

                {{-- HEADER --}}
                <div class="mb-3">
                    <h5 class="mb-3 fw-bold">
                        {{ $first->accreditationInfo->title }}
                        {{ $first->accreditationInfo->year }}
                    </h5>
                    <p class="mb-0">
                        Program: <strong>{{ $first->program->program_name }}</strong>
                    </p>
                    <p class="mb-0">
                        Level: <strong>{{ $first->level->level_name }}</strong>
                    </p>
                </div>

                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Area/s</th>
                            <th>Assessor/s</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                            <th>Updated At</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($areaGroups as $areaId => $items)
                            @php
                                $rowspan = $items->count();
                                $areaName = strtoupper($items->first()['area']->area_name);
                            @endphp

                            @foreach($items as $index => $item)
                                <tr>
                                    @if($index === 0)
                                        <td rowspan="{{ $rowspan }}">
                                            <a href="{{ route($isInternalAssessor ? 'program.areas.evaluation' : 'program.areas.parameters', [
                                                'infoId' => $first->accreditationInfo->id,
                                                'levelId' => $first->level->id,
                                                'programId' => $first->program->id,
                                                'programAreaId' => $item['area']->id,
                                            ]) }}" class="fw-semibold text-decoration-none link-primary">
                                                {{ $areaName }}
                                            </a>
                                        </td>
                                    @endif

                                    <td>{{ $item['evaluation']->evaluator->name }}</td>
                                    <td class="text-center">
                                        @php
                                            $status = $item['evaluation']->status;
                                        @endphp
                                        <span class="badge 
                                            {{ $status === EvaluationStatus::FINALIZED ? 'bg-success' : '' }}
                                            {{ $status === EvaluationStatus::UPDATED ? 'bg-warning text-dark' : '' }}
                                            {{ $status === EvaluationStatus::SUBMITTED ? 'bg-primary text-white' : '' }}
                                            {{ !in_array($status, [EvaluationStatus::FINALIZED, EvaluationStatus::UPDATED, EvaluationStatus::SUBMITTED]) ? 'bg-secondary' : '' }}
                                        ">
                                            {{ $status === EvaluationStatus::FINALIZED ? 'Finalized' : '' }}
                                            {{ $status === EvaluationStatus::UPDATED ? 'Updated' : '' }}
                                            {{ $status === EvaluationStatus::SUBMITTED ? 'Submitted' : '' }}
                                            {{ !in_array($status, [EvaluationStatus::FINALIZED, EvaluationStatus::UPDATED, EvaluationStatus::SUBMITTED]) ? '—' : '' }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $item['evaluation']->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="text-center">{{ $item['evaluation']->updated_at->format('M d, Y h:i A') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('program.areas.evaluations.summary', [$item['evaluation']->id, $item['area']->id]) }}"
                                           class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No internal assessor evaluation yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- SUMMARY BUTTON --}}
                @if ($isAdmin || $isDean || $isAccreditor)
                    @php
                        $gm = $grandMeans[$key]['internal'] ?? null;
                        $internalSignatories = $signatories[$key]['internal'] ?? [];
                    @endphp

                    @if($gm)
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#grandMeanModal-{{ $loop->index }}">
                                View Summary of Ratings
                            </button>
                        </div>

                        {{-- MODAL --}}
                        <div class="modal fade" id="grandMeanModal-{{ $loop->index }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-fullscreen">
                                <div class="modal-content">
                                    <div class="modal-header justify-content-center position-relative">
                                        <h5 class="modal-title fw-bold">SUMMARY OF RATINGS</h5>
                                        <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body px-4">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th style="width:70%">Area</th>
                                                    <th style="width:30%">Area Mean</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($gm['areaModels'] as $area)
                                                    <tr>
                                                        <td>{{ preg_replace('/^AREA\s+([IVXLC]+)\s*:\s*/i', '$1. ', strtoupper($area->area_name)) }}</td>
                                                        <td class="text-center">{{ number_format($gm['areas'][$area->id] ?? 0, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        {{-- TOTAL & GRAND MEAN --}}
                                        <div class="row mt-4">
                                            <div class="col-6 text-end fw-bold">Total</div>
                                            <div class="col-6 text-center">{{ number_format($gm['total'], 2) }}</div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6 text-end fw-bold">Grand Mean</div>
                                            <div class="col-6 text-center fw-bold">{{ number_format($gm['grand'], 2) }}</div>
                                        </div>

                                        {{-- Interpretation --}}
                                        @php
                                            $grandMean = $gm['grand'];
                                            $interpretation = $grandMean >= 4 ? 'Proceed to Level I within 6 months' :
                                                              ($grandMean >= 2.5 ? 'Proceed to Level I not earlier than 1 year' :
                                                              ($grandMean >= 1 ? 'Proceed to Level I not earlier than 2 years' : 
                                                              'Conduct another Preliminary Survey Visit'));
                                        @endphp
                                        <div class="row mt-3">
                                            <div class="col-6 text-end fw-bold">Interpretation</div>
                                            <div class="col-6 text-center fw-semibold text-primary">{{ $interpretation }}</div>
                                        </div>

                                        {{-- Signatories --}}
                                        <div class="row mt-5 justify-content-center">
                                            @foreach ($internalSignatories as $name)
                                                <div class="col-4 text-center">
                                                    <div class="fw-semibold">{{ $name }}</div>
                                                    <div class="signature-line mx-auto"></div>
                                                    <div class="small text-muted">Internal Assessor</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-primary"
                                                onclick="printGrandMean('grandMeanModal-{{ $loop->index }}')">
                                            <i class="bx bx-printer me-1"></i> Print
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-info">No evaluations found.</div>
    @endforelse
</div>

<style>
.signature-line {
    width: 260px;
    border-bottom: 1.5px solid #000;
    margin-top: 4px;
}
@media print {
    .signature-line { margin: 6px auto 0; }
}
</style>

@push('scripts')
<script>
function printGrandMean(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const printContents = modal.querySelector('.modal-content').innerHTML;
    const originalContents = document.body.innerHTML;

    document.body.innerHTML = `
        <html>
            <head>
                <title>Summary of Ratings</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 40px; }
                    table { width: 100%; border-collapse: collapse; }
                    table, th, td { border: 1px solid #000; }
                    th, td { padding: 8px; }
                    .modal-header button, .modal-footer { display: none !important; }
                    .signature-line { width: 260px; border-bottom: 1.5px solid #000; margin: 6px auto 0; }
                </style>
            </head>
            <body>${printContents}</body>
        </html>
    `;

    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>
@endpush

@endsection