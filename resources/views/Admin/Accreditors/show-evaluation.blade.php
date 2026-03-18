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
     <p class="text-muted mb-4">Submitted Area Evaluation</p>
    <div class="mb-3 mt-3">
        <strong>Assessor:</strong> {{ $evaluation->evaluator->name ?? 'N/A' }} <br>
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
                    {{-- PARAMETER HEADER --}}
                    <tr class="table-secondary fw-semibold">
                        <td colspan="5">{{ $parameter->parameter_name }}</td>
                    </tr>

                    @foreach($parameter->sub_parameters as $sub)
                        @php
                            $rating = $ratings[$sub->id] ?? null;
                            $label  = $rating?->ratingOption?->label;
                        @endphp

                        <tr>
                            <td style="padding-left: 30px">{{ $sub->sub_parameter_name }}</td>

                            <td class="text-center">
                                {{ $label === 'Available' ? $rating->score : '' }}
                            </td>
                            <td class="text-center">
                                {{ $label === 'Available but Inadequate' ? $rating->score : '' }}
                            </td>
                            <td class="text-center">
                                {{ $label === 'Not Available' ? '0' : '' }}
                            </td>
                            <td class="text-center">
                                {{ $label === 'Not Applicable' ? 'NA' : '' }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No parameters found for this area.
                        </td>
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
                <label class="fw-bold">Recommendations</label>
                <textarea class="form-control no-resize" rows="4" disabled>
{{ $evaluation?->areaRecommendations->first()?->recommendation ?? '' }}
                </textarea>
            </div>

            {{-- ACTION BUTTONS (now only Print remains) --}}
            <div class="mt-4 d-flex justify-content-end gap-2">
                <button onclick="window.print()" class="btn btn-outline-primary">
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

<style>
@media print {
    /* Page setup */
    @page {
        size: A4 portrait;
        margin: 15mm;
    }

    body {
        font-size: 11px;
        color: #000;
        background: #fff;
    }

    /* Hide UI-only elements */
    .btn,
    .alert,
    nav,
    footer,
    .modal {
        display: none !important;
    }

    /* Table layout like accreditation form */
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10.5px;
        page-break-inside: avoid;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
        vertical-align: top;
    }

    th {
        text-align: center;
        font-weight: bold;
    }

    /* Section headers (A, B, C) */
    .table-secondary {
        background: #f2f2f2 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Sub-items (A.1, A.2...) */
    td:first-child {
        padding-left: 12px;
    }

    /* Totals */
    tfoot tr:first-child {
        font-weight: bold;
    }

    /* Recommendations area */
    textarea {
        border: 1px solid #000;
        height: 120px;
        margin-top: 10px;
        padding: 5px;
        background: none;
        resize: none;
    }
}
</style>
@endsection