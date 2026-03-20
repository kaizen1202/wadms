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
                                    <td class="text-center">{{ $item['evaluation']->created_at->format('M d, Y, h:i A') }}</td>
                                    <td class="text-center">{{ $item['evaluation']->updated_at->format('M d, Y, h:i A') }}</td>
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

    const body = modal.querySelector('.modal-body');

    const rows = [...body.querySelectorAll('table tbody tr')].map(tr => {
        const cells = tr.querySelectorAll('td');
        return { area: cells[0]?.innerText.trim(), mean: cells[1]?.innerText.trim() };
    }).filter(r => r.area && r.mean);

    const total      = body.querySelector('.row:nth-of-type(1) .col-6:last-child')?.innerText.trim() ?? '';
    const grandMean  = body.querySelector('.row:nth-of-type(2) .col-6:last-child')?.innerText.trim() ?? '';
    const interp     = body.querySelector('.row:nth-of-type(3) .col-6:last-child')?.innerText.trim() ?? '';

    // Collect all signatory names from the modal
    const sigNames = [...body.querySelectorAll('.col-4 .fw-semibold')]
                        .map(el => el.innerText.trim())
                        .filter(Boolean);

    // Split signatories into rows of 3
    const sigRows = [];
    for (let i = 0; i < sigNames.length; i += 3) {
        sigRows.push(sigNames.slice(i, i + 3));
    }

    // Build each signatory row HTML — evenly spaced regardless of count
    const sigRowsHtml = sigRows.map(rowNames => `
        <div class="sig-row">
            ${rowNames.map(name => `
                <div class="sig-item">
                    <div class="sig-name">${name}</div>
                    <div class="sig-line"></div>
                    <div class="sig-role">Internal Assessor</div>
                </div>`).join('')}
        </div>`).join('');

    const printWin = window.open('', '_blank', 'width=850,height=1100');
    printWin.document.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Summary of Ratings</title>
<style>
    @page { size: A4 portrait; margin: 30mm 25mm 30mm 25mm; }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: Arial, sans-serif;
        font-size: 11pt;
        color: #000;
        background: #fff;
    }

    .page {
        width: 100%;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* ── TITLE ── */
    h1 {
        text-align: center;
        font-size: 13pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 18px;
        letter-spacing: 0.5px;
    }

    /* ── TABLE ── */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 28px;
    }

    table th, table td {
        border: 1px solid #000;
        padding: 6px 10px;
        font-size: 10.5pt;
    }

    table thead th {
        text-align: center;
        font-weight: bold;
        background: #fff;
    }

    table thead th:last-child { width: 28%; text-align: center; }
    table tbody td:last-child  { text-align: center; }

    /* ── TOTALS ── */
    .total-row {
        display: flex;
        justify-content: flex-end;
        align-items: baseline;
        margin-bottom: 18px;
        gap: 12px;
    }

    .total-row .t-label {
        font-size: 11pt;
        min-width: 90px;
        text-align: right;
    }

    .total-row .t-line {
        width: 160px;
        border-bottom: 1px solid #000;
        font-size: 11pt;
        text-align: center;
        padding-bottom: 2px;
    }

    .total-row.grand-mean .t-line {
        border-bottom: 2px double #000;
    }

    /* ── INTERPRETATION ── */
    .interpretation {
        display: flex;
        justify-content: flex-end;
        align-items: baseline;
        gap: 12px;
        margin-top: 4px;
        margin-bottom: 40px;
    }

    .interpretation .i-label {
        font-size: 11pt;
        font-weight: bold;
        min-width: 90px;
        text-align: right;
    }

    .interpretation .i-value {
        width: 320px;
        border-bottom: 1px solid #000;
        font-size: 10.5pt;
        text-align: center;
        padding-bottom: 2px;
    }

    /* ── SIGNATORIES ── */
    .sig-section {
        margin-top: auto;
        padding-top: 40px;
    }

    .sig-row {
        display: flex;
        justify-content: space-around;
        gap: 24px;
        margin-bottom: 36px;
    }

    /* When only 1 or 2 signatories in a row, keep them from stretching full width */
    .sig-row .sig-item {
        flex: 0 1 220px;
        text-align: center;
    }

    .sig-line {
        border-top: 1px solid #000;
        margin-top: 4px;
        margin-bottom: 4px;
        width: 100%;
    }

    .sig-name {
        font-size: 10pt;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .sig-role {
        font-size: 9.5pt;
    }
</style>
</head>
<body>
<div class="page">

    <h1>Summary of Ratings</h1>

    <table>
        <thead>
            <tr>
                <th>Area</th>
                <th>Area Mean</th>
            </tr>
        </thead>
        <tbody>
            ${rows.map(r => `
                <tr>
                    <td>${r.area}</td>
                    <td>${r.mean}</td>
                </tr>`).join('')}
        </tbody>
    </table>

    <div class="total-row">
        <span class="t-label">Total</span>
        <span class="t-line">${total}</span>
    </div>
    <div class="total-row grand-mean">
        <span class="t-label">Grand Mean</span>
        <span class="t-line">${grandMean}</span>
    </div>

    ${interp ? `
    <div class="interpretation">
        <span class="i-label">Interpretation</span>
        <span class="i-value">${interp}</span>
    </div>` : ''}

    ${sigRows.length ? `
    <div class="sig-section">
        ${sigRowsHtml}
    </div>` : ''}

</div>
</body>
</html>`);

    printWin.document.close();
    printWin.focus();
    setTimeout(() => { printWin.print(); printWin.close(); }, 600);
}
</script>
@endpush

@endsection