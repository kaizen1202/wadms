@extends('admin.layouts.master')

@section('contents')
<div class="container-xxl container-p-y">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ url()->previous() }}" class="text-muted">Accreditation</a>
                    </li>
                    <li class="breadcrumb-item active">Areas</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">{{ $programName }}</h4>
            <small class="text-muted">
                <i class="bx bx-layer me-1"></i>Program Areas
            </small>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
    </div>

    {{-- ===== AREA CARDS ===== --}}
    <div class="row g-3">
        @forelse ($programAreas as $mapping)
            @php
                $evaluation   = $mapping->evaluations->first();
                $status       = $evaluation->status ?? 'not_started';
                $evaluatorName = $evaluation?->files->first()?->uploader?->name;

                $badgeClass = match($status) {
                    'completed'  => 'bg-success',
                    'ongoing'    => 'bg-warning text-dark',
                    default      => 'bg-secondary',
                };

                $statusLabel = ucfirst(str_replace('_', ' ', $status));
            @endphp

            <div class="col-md-4">
                <div class="card area-card h-100 shadow-sm d-flex flex-column"
                     style="border-radius: 10px; border-color: #e2e8f0;">

                    <a href="{{ route('program.areas.evaluation', [$infoId, $levelId, $programId, $mapping->id]) }}"
                       class="text-decoration-none flex-grow-1 d-flex flex-column">

                        {{-- Card Header --}}
                        <div class="area-header">
                            <div class="area-title">Area</div>
                            <div class="area-name">{{ $mapping->area->area_name }}</div>
                            <span class="area-badge">
                                <i class="bx bx-user bx-xs"></i>
                                {{ $mapping->users->count() }} assigned
                            </span>
                        </div>

                        {{-- Card Body --}}
                        <div class="area-body">

                            {{-- Avatar stack --}}
                            @if ($mapping->users->count() > 0)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-stack">
                                        @foreach ($mapping->users->take(4) as $user)
                                            <div class="av" title="{{ $user->name }}">
                                                <x-initials-avatar :user="$user" size="xs" shape="circle" />
                                            </div>
                                        @endforeach
                                        @if ($mapping->users->count() > 4)
                                            <div class="av av-more">+{{ $mapping->users->count() - 4 }}</div>
                                        @endif
                                    </div>
                                    <span class="assigned-label">
                                        {{ $mapping->users->first()?->name }}
                                        @if ($mapping->users->count() > 1)
                                            & {{ $mapping->users->count() - 1 }} more
                                        @endif
                                    </span>
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="av av-empty">
                                        <i class="bx bx-user-x" style="font-size:0.9rem;"></i>
                                    </div>
                                    <span class="no-users-text">No users assigned yet</span>
                                </div>
                            @endif

                            {{-- Evaluation status --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                                @if ($evaluatorName)
                                    <small class="text-muted" style="font-size: 11px;">
                                        <i class="bx bx-user me-1"></i>{{ $evaluatorName }}
                                    </small>
                                @endif
                            </div>

                        </div>
                    </a>

                </div>
            </div>

        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body empty-state">
                        <i class="bx bx-layer"></i>
                        {{
                            $isInternalAssessor || $isTaskForce
                                ? 'No areas assigned to you yet.'
                                : 'No areas available for this program.'
                        }}
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection