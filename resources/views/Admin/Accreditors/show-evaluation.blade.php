@extends('admin.layouts.master')

@section('contents')
<div class="container-xxl container-p-y">

    {{-- HEADER --}}
    <h4 class="fw-bold mb-1 d-flex justify-content-between align-items-center">
        {{-- Left side: area name + status --}}
        <div class="d-flex align-items-center gap-2">
            {{ $area->area_name }}
            @if($evaluation && $evaluation->is_final && !$isAccreditor)
                <span class="badge bg-success">{{ $evaluation->status }}</span>
            @endif
        </div>

        {{-- Right side: Back button --}}
        <a href="{{ route('program.areas.evaluations') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
    </h4>
    <p class="text-muted mb-4">{{ $evaluation->is_final ? 'Final' : 'Submitted' }} Area Evaluation</p>
    <div class="mb-3 mt-3">
        <strong>Assessor:</strong>
        {{ $evaluation->evaluator->name ?? 'N/A' }}
        @if($evaluation->evaluator && auth()->id() === $evaluation->evaluator->id)
            <span class="text-muted">(You)</span>
        @endif
        <br>
        <strong>Date Submitted:</strong> {{ $evaluation->updated_at->format('F d, Y') }}
    </div>

    {{-- LOCKED / DRAFT NOTICE --}}
    @if(
        auth()->user()->currentRole->name === \App\Enums\UserType::INTERNAL_ASSESSOR->value &&
        $evaluation && $evaluation->evaluated_by === auth()->id()
    )
        <div class="alert alert-info d-flex align-items-center">
            <i class="bx bx-info-circle me-2"></i>
            Evaluation is saved but not yet finalized.
        </div>
    @endif

    {{-- MARK AS FINAL BUTTON --}}
    @if(
        auth()->user()->currentRole->name === \App\Enums\UserType::INTERNAL_ASSESSOR->value &&
        $evaluation && $evaluation->evaluated_by === auth()->id() &&
        !$evaluation->is_final
    )
        <div class="mb-3">
            <button type="button" class="btn btn-success"
                    data-bs-toggle="modal" data-bs-target="#finalEvaluationModal">
                <i class="bx bx-check-shield me-1"></i> Mark as Final Evaluation
            </button>
        </div>
    @endif

    {{-- EVALUATION CARD --}}
    <div class="card mb-4">
        <div class="card-body">
            {{-- EVALUATION TABLE --}}
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th style="width:35%">Checklist Item</th>
                        <th>Available</th>
                        <th>Available but Inadequate</th>
                        <th>Not Available</th>
                        <th>Not Applicable</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($parameters as $parameter)
                        <tr class="table-secondary fw-semibold">
                            <td colspan="5">{{ $parameter->parameter_name }}</td>
                        </tr>

                        @foreach($parameter->sub_parameters as $sub)
                            @php
                                $hasSubSub = $sub->subSubParameters->isNotEmpty();
                                $rating    = !$hasSubSub ? ($ratings[$sub->id] ?? null) : null;
                                $label     = $rating?->ratingOption?->label;

                                // Compute sub-mean for sub-sub rows
                                $subMeanScore = 0;
                                $subMeanCount = 0;
                                if ($hasSubSub) {
                                    foreach ($sub->subSubParameters as $ss) {
                                        $r = $subSubRatings[$ss->id] ?? null;
                                        if ($r && in_array($r->ratingOption->label, ['Available', 'Available but Inadequate'])) {
                                            $subMeanScore += $r->score;
                                            $subMeanCount++;
                                        } elseif ($r && $r->ratingOption->label === 'Not Available') {
                                            $subMeanCount++;
                                        }
                                    }
                                    $subMean = $subMeanCount ? number_format($subMeanScore / $subMeanCount, 2) : '0.00';
                                }
                            @endphp

                            {{-- Sub-parameter row --}}
                            <tr>
                                <td style="padding-left: 30px; font-weight: 600;">
                                    {{ $sub->sub_parameter_name }}
                                </td>
                                <td class="text-center">{{ $label === 'Available' ? $rating->score : '' }}</td>
                                <td class="text-center">{{ $label === 'Available but Inadequate' ? $rating->score : '' }}</td>
                                <td class="text-center">{{ $label === 'Not Available' ? '0' : '' }}</td>
                                <td class="text-center">{{ $label === 'Not Applicable' ? 'NA' : '' }}</td>
                            </tr>

                            {{-- Sub-sub-parameter rows --}}
                            @if($hasSubSub)
                                @foreach($sub->subSubParameters as $subSub)
                                    @php
                                        $ssRating = $subSubRatings[$subSub->id] ?? null;
                                        $ssLabel  = $ssRating?->ratingOption?->label;
                                    @endphp
                                    <tr>
                                        <td style="padding-left: 55px; font-size: 14px;">
                                            <i class="bx bx-subdirectory-right me-1"></i>{{ $subSub->name }}
                                        </td>
                                        <td class="text-center">{{ $ssLabel === 'Available' ? $ssRating->score : '' }}</td>
                                        <td class="text-center">{{ $ssLabel === 'Available but Inadequate' ? $ssRating->score : '' }}</td>
                                        <td class="text-center">{{ $ssLabel === 'Not Available' ? '0' : '' }}</td>
                                        <td class="text-center">{{ $ssLabel === 'Not Applicable' ? 'NA' : '' }}</td>
                                    </tr>
                                @endforeach
                            @endif

                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No parameters found for this area.</td>
                        </tr>
                    @endforelse
                    </tbody>

                {{-- TOTALS --}}
                <tfoot class="fw-semibold">
                    <tr>
                        <td>Total</td>
                        <td class="text-center">{{ $totals['available'] ?? 0 }}</td>
                        <td class="text-center">{{ $totals['inadequate'] ?? 0 }}</td>
                        <td class="text-center">{{ $totals['not_available'] ?? 0 }}</td>
                        <td class="text-center">{{ $totals['not_applicable'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <td>Area Mean</td>
                        <td colspan="4" class="text-center fs-5 fw-bold">
                            {{ $mean ?? '0.00' }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            {{-- RECOMMENDATION --}}
            <div class="mt-4">
                <label class="fw-bold">Recommendations:</label>
                {{-- Screen: textarea --}}
                <textarea class="form-control no-resize" rows="4" disabled>{{ $evaluation?->areaRecommendations->first()?->recommendation ?? '' }}</textarea>
                {{-- Print only: ruled lines --}}
                <div class="rec-lines">
                    @for ($i = 0; $i < 6; $i++)
                        <span class="rec-line"></span>
                    @endfor
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="mt-4 d-flex justify-content-end gap-2">
                <button onclick="printEvaluation()" class="btn btn-outline-primary">
                    <i class="bx bx-printer me-1"></i> Print
                </button>
            </div>
        </div>
    </div>

    {{-- PREVIOUS / NEXT AREA NAVIGATION --}}
    <div class="mt-4 d-flex justify-content-between">
        @if($prevEvaluation)
            <a href="{{ route('program.areas.evaluations.summary', [
                'evaluation' => $prevEvaluation->id,
                'area' => $prevEvaluation->area_id
            ]) }}"
            class="btn btn-outline-secondary">
                <i class="bx bx-chevron-left"></i> Previous
            </a>
        @else
            <span></span>
        @endif

        @if($nextEvaluation)
            <a href="{{ route('program.areas.evaluations.summary', [
                'evaluation' => $nextEvaluation->id,
                'area' => $nextEvaluation->area_id
            ]) }}"
            class="btn btn-outline-primary">
                Next <i class="bx bx-chevron-right"></i>
            </a>
        @endif
    </div>

    {{-- FINALIZE CONFIRMATION MODAL --}}
    <x-modal id="finalEvaluationModal" title="Confirm Final Evaluation">
        <p>Are you sure you want to mark this as <strong>Final Evaluation</strong>?</p>
        <p class="text-muted mb-2">
            Once marked as final, this evaluation will be locked and can no longer be edited.
        </p>

        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Cancel
            </button>

            <form action="{{ route('evaluations.finalize', $evaluation) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success">
                    Yes, Mark as Final
                </button>
            </form>
        </x-slot>
    </x-modal>
</div>

@push('scripts')
<script>
function printEvaluation() {

    const areaTitle    = document.querySelector('h4.fw-bold .d-flex')?.innerText.trim()
                     ?? document.querySelector('h4.fw-bold')?.innerText.trim() ?? '';
    const assessorName = document.querySelector('.mb-3.mt-3 div')?.innerText.trim()
                        ?? document.querySelector('.mb-3.mt-3')?.innerText.trim() ?? '';
    const statusLabel  = document.querySelector('.mb-3.mt-3 span')?.innerText.trim() ?? '';
    const rec           = document.querySelector('textarea')?.value.trim() ?? '';

    // ── Clone tbody ──
    const tbodyEl    = document.querySelector('table tbody');
    const tbodyClone = tbodyEl.cloneNode(true);
    tbodyClone.querySelectorAll('i.bx').forEach(i => i.remove());
    tbodyClone.querySelectorAll('tr').forEach(tr => {
        const tds = tr.querySelectorAll('td');
        if (tds.length >= 5 && tds[4]?.innerText.trim() === 'NA') {
            tds[4].innerText = '';
        }
    });
    const tbody = tbodyClone.innerHTML;

    // ── Totals ──
    const totals = {
        available:     document.querySelector('tfoot tr:first-child td:nth-child(2)')?.innerText.trim() ?? '',
        inadequate:    document.querySelector('tfoot tr:first-child td:nth-child(3)')?.innerText.trim() ?? '',
        not_available: document.querySelector('tfoot tr:first-child td:nth-child(4)')?.innerText.trim() ?? '',
    };
    const mean = document.querySelector('tfoot tr:last-child td:nth-child(2)')?.innerText.trim() ?? '';

    const printWin = window.open('', '_blank', 'width=900,height=1100');
    printWin.document.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>${areaTitle}</title>
<style>
    @page { size: A4 portrait; margin: 15mm; }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        color: #000;
        background: #fff;
    }

    /* ── TITLE ── */
    .doc-title {
        text-align: center;
        font-size: 11pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 12px;
        letter-spacing: 0.3px;
    }

    /* ── META ── */
    .doc-meta {
        font-size: 9.5pt;
        margin-bottom: 10px;
        line-height: 1.6;
    }

    /* ── TABLE ── */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9pt;
    }

    thead th {
        border: 1px solid #000;
        padding: 4px 6px;
        text-align: center;
        font-weight: bold;
        vertical-align: middle;
        line-height: 1.3;
    }

    thead th:first-child {
        text-align: left;
        width: 52%;
    }

    /* score columns equal width */
    thead th:not(:first-child) { width: 12%; }

    tbody td {
        border: 1px solid #000;
        padding: 3px 6px;
        vertical-align: middle;
        line-height: 1.3;
    }

    tbody td:not(:first-child) { text-align: center; }

    .table-secondary td { font-weight: bold; }

    tr { page-break-inside: avoid; }

    /* ── TOTALS BLOCK ──
       Mirrors table layout: 52% label area, then 3 × 12% underlines with gap
    ── */
    .totals-wrap {
        width: 100%;
        margin-top: 10px;
    }

    .totals-row, .mean-row {
        display: flex;
        align-items: baseline;
        margin-bottom: 10px;
    }

    /* Left spacer matches checklist column (52%) */
    .totals-spacer {
        width: 52%;
        flex-shrink: 0;
    }

    /* Label (Total / Area Mean) sits at end of spacer */
    .totals-label {
        width: 52%;
        flex-shrink: 0;
        text-align: center;
        font-size: 10pt;
        padding-right: 8px;
    }

    /* 3 underlines, each 12% wide with a gap between */
    .totals-vals {
        display: flex;
        width: 48%; /* remaining columns space */
        gap: 0;
    }

    .t-val {
        width: 33.33%;
        text-align: center;
        border-bottom: 1px solid #000;
        padding-bottom: 2px;
        font-size: 10pt;
    }

    /* Gap between underlines — use margin */
    .t-val + .t-val {
        margin-left: 8px;
        width: calc(33.33% - 8px);
    }

    /* Area Mean — single underline under Available column only */
    .mean-val {
        width: 25%;
        text-align: center;
        border-bottom: 1px solid #000;
        padding-bottom: 2px;
        font-weight: bold;
        font-size: 11pt;
    }

    /* ── RECOMMENDATIONS ── */
    .rec-section { margin-top: 18px; }

    .rec-label {
        font-weight: bold;
        font-size: 10.5pt;
        display: block;
        margin-bottom: 8px;
    }

    .rec-text {
        font-size: 9.5pt;
        line-height: 1.6;
    }

    .rec-line {
        display: block;
        width: 100%;
        border-bottom: 1px solid #000;
        height: 22px;
        margin-bottom: 2px;
    }
</style>
</head>
<body>

    <div class="doc-title">${areaTitle}</div>
    ${statusLabel ? `
    <div style="text-align:center; margin-top:-8px; margin-bottom:12px;">
        <span style="
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 2px 10px;
            border: 1px solid #000;
            border-radius: 20px;
        ">${statusLabel}</span>
    </div>` : ''}
    <div class="doc-meta">${assessorName.replace(/\n/g, '<br>')}</div>

    <table>
        <thead>
            <tr>
                <th style="width:52%; text-align:left;">Checklist of data/information, processes and activities</th>
                <th>Available</th>
                <th>Available but<br>Inadequate</th>
                <th>Not<br>Available</th>
                <th>Not<br>Applicable</th>
            </tr>
        </thead>
        <tbody>${tbody}</tbody>
    </table>

    <!-- Total row — 3 underlines aligned under first 3 data columns -->
    <div class="totals-wrap">
        <div class="totals-row">
            <span class="totals-label">Total</span>
            <div class="totals-vals">
                <span class="t-val">${totals.available}</span>
                <span class="t-val">${totals.inadequate}</span>
                <span class="t-val">${totals.not_available}</span>
            </div>
        </div>

        <div class="mean-row">
            <span class="totals-label">Area Mean</span>
            <span class="mean-val">${mean}</span>
        </div>
    </div>

    <div class="rec-section">
        <span class="rec-label">Recommendations:</span>
        ${rec
            ? `<div class="rec-text">${rec.replace(/\n/g, '<br>')}</div>`
            : `<span class="rec-line"></span>
               <span class="rec-line"></span>
               <span class="rec-line"></span>
               <span class="rec-line"></span>
               <span class="rec-line"></span>
               <span class="rec-line"></span>`
        }
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