@php use App\Enums\EvaluationStatus; @endphp

<div class="container-fluid">

    <h2 class="mb-1 fw-bold d-flex align-items-center justify-content-between">
        Accreditor Dashboard
        <button class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()" id="refreshBtn">
            <i class="bx bx-refresh me-1"></i> Refresh
        </button>
    </h2>
    <p class="text-muted mb-4">Overview of accreditation progress and internal assessor evaluations.</p>

    {{-- ── STAT CARDS ── --}}
    <div class="row g-3 mb-2">

        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning fs-4 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bx bx-loader-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size:.75rem;">Ongoing Accreditations</p>
                        <h4 class="fw-bold mb-0 text-warning">{{ $ongoingCount }}</h4>
                        <small class="text-muted">Currently active</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success fs-4 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bx bx-badge-check"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size:.75rem;">Finalized Evaluations</p>
                        <h4 class="fw-bold mb-0 text-success">{{ $finalizedEvaluations }}</h4>
                        <small class="text-muted">By internal assessors</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-info bg-opacity-10 text-info fs-4 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bx bx-book-open"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size:.75rem;">Accreditation Programs</p>
                        <h4 class="fw-bold mb-0">{{ $programsCount }}</h4>
                        <small class="text-muted">Across all levels</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 text-primary fs-4 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size:.75rem;">Completed Accreditations</p>
                        <h4 class="fw-bold mb-0">{{ $completedCount }}</h4>
                        <small class="text-muted">All time</small>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── ACCREDITATION OVERVIEW ── --}}
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Accreditation Overview</span>
            <a href="{{ route('program.areas.evaluations') }}" class="btn btn-sm btn-outline-primary">
                View All Evaluations
            </a>
        </div>
        <div class="card-body">

            @forelse($accreditationOverviews as $overview)
                @php $acc = $overview['accreditation']; @endphp

                <div class="{{ !$loop->last ? 'mb-4 pb-4 border-bottom' : '' }}">

                    {{-- Accreditation title --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold mb-0">{{ $acc->title }} {{ $acc->year }}</h6>
                        <span class="badge bg-warning text-dark">Ongoing</span>
                    </div>

                    @foreach($overview['levels'] as $level)

                        {{-- Level header --}}
                        <div class="mb-2">
                            <span class="text-muted fw-semibold" style="font-size:.75rem; text-transform:uppercase; letter-spacing:.05em;">
                                <i class="bx bx-layer me-1"></i>{{ $level['levelName'] }}
                            </span>
                        </div>

                        @foreach($level['programs'] as $program)
                            @php
                                $pct = $program['totalAreas']
                                    ? round($program['finalizedAreaCount'] / $program['totalAreas'] * 100)
                                    : 0;
                            @endphp

                            {{-- Program header + progress --}}
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-primary bg-opacity-10 text-white border border-primary border-opacity-25"
                                    style="font-size:.78rem;">
                                    <i class="bx bx-book me-1"></i>{{ $program['program_name'] }}
                                </span>
                                <small class="text-muted">
                                    {{ $program['finalizedAreaCount'] }} / {{ $program['totalAreas'] }} Finalized
                                </small>
                            </div>

                            <div class="progress mb-3" style="height:5px; border-radius:99px;">
                                <div class="progress-bar bg-primary" style="width:{{ $pct }}%;"></div>
                            </div>

                            {{-- Area cards --}}
                            <div class="row g-2 mb-3">
                                @foreach($program['overviewAreas'] as $item)
                                    @php
                                        [$badgeClass, $badgeLabel] = match($item['status']) {
                                            'finalized' => ['bg-success', 'Finalized'],
                                            default     => ['bg-secondary', 'Pending'],
                                        };

                                        $href = $item['status'] === 'finalized'
                                            ? route('program.areas.evaluations.summary', [
                                                'evaluation' => $item['evaluation']->id,
                                                'area'       => $item['area_id'],
                                            ])
                                            : route('program.areas.parameters', [
                                                'infoId'        => $item['info_id'],
                                                'levelId'       => $item['level_id'],
                                                'programId'     => $item['program_id'],
                                                'programAreaId' => $item['program_area_id'],
                                            ]);
                                    @endphp
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <a href="{{ $href }}" class="text-decoration-none">
                                            <div class="card border h-100 shadow-none"
                                                onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.08)'; this.style.borderColor='#0d6efd';"
                                                onmouseout="this.style.boxShadow='none'; this.style.borderColor='#dee2e6';"
                                                style="transition: box-shadow .15s, border-color .15s;">
                                                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center gap-2">
                                                    <div style="min-width:0;">
                                                        <p class="fw-semibold mb-0 text-truncate text-dark"
                                                        style="font-size:.83rem;" title="{{ $item['area_name'] }}">
                                                            {{ $item['area_name'] }}
                                                        </p>
                                                        <small class="text-muted">
                                                            <i class="bx bx-user me-1"></i>
                                                            @if($item['assigned_count'] > 0)
                                                                {{ $item['assigned_count'] }} assessor{{ $item['assigned_count'] > 1 ? 's' : '' }} assigned
                                                            @else
                                                                No assessor assigned yet
                                                            @endif
                                                        </small>
                                                    </div>
                                                    <span class="badge {{ $badgeClass }} flex-shrink-0">{{ $badgeLabel }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                        @endforeach

                        @if(!$loop->last)
                            <hr class="my-3">
                        @endif

                    @endforeach
                </div>

            @empty
                <p class="text-muted mb-0">No ongoing accreditation at the moment.</p>
            @endforelse

        </div>
    </div>

    {{-- ── BOTTOM ROW: Activity + Quick Actions ── --}}
    <div class="row g-3 mt-2">

        {{-- Recent Activity --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Recent Activities</div>
                <div class="card-body p-0" style="max-height:480px; overflow-y:auto;">

                    @forelse($recentActivities as $group)
                        @foreach($group['levels'] as $levelGroup)
                            @foreach($levelGroup['programs'] as $programGroup)

                                {{-- Breadcrumb header --}}
                                <div class="px-3 py-2 d-flex align-items-center gap-1 flex-wrap"
                                    style="background:#f8fafc; border-bottom: 2px solid #e2e8f0; position:sticky; top:0; z-index:1;">
                                    <span class="text-muted fw-semibold" style="font-size:.75rem;">
                                        <i class="bx bx-certification me-1"></i>{{ $group['accreditation'] }}
                                    </span>
                                    <span class="text-muted" style="font-size:.65rem;">•</span>
                                    <span class="text-muted fw-semibold" style="font-size:.75rem;">
                                        <i class="bx bx-layer me-1"></i>{{ $levelGroup['level'] }}
                                    </span>
                                    <span class="text-muted" style="font-size:.65rem;">•</span>
                                    <span class="text-primary fw-semibold" style="font-size:.75rem;">
                                        <i class="bx bx-book me-1"></i>{{ $programGroup['program'] }}
                                    </span>
                                </div>

                                {{-- Activities --}}
                                @foreach($programGroup['activities'] as $act)
                                    <div class="d-flex align-items-start gap-3 px-3 py-2"
                                        style="border-bottom: 1px solid #f1f5f9;">

                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                            style="width:28px; height:28px; min-width:28px;
                                                    background:{{ $act['color'] === 'text-success' ? '#dcfce7' : '#dbeafe' }};">
                                            <i class="bx {{ $act['icon'] }} {{ $act['color'] }}" style="font-size:.8rem;"></i>
                                        </div>

                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="text-dark" style="font-size:.82rem; line-height:1.5;">
                                                {{ $act['text'] }}
                                            </div>
                                        </div>

                                        <div class="text-end flex-shrink-0">
                                            <div class="text-muted" style="font-size:.72rem;">{{ $act['time'] }}</div>
                                            <div class="text-muted" style="font-size:.65rem; opacity:.7;">{{ $act['date'] }}</div>
                                        </div>

                                    </div>
                                @endforeach

                            @endforeach
                        @endforeach

                        @if(!$loop->last)
                            <div style="height:8px; background:#f1f5f9;"></div>
                        @endif

                    @empty
                        <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                            <i class="bx bx-calendar-x" style="font-size:2rem; opacity:.4;"></i>
                            <small class="mt-2">No recent activity.</small>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Quick Actions</div>
                <div class="card-body d-flex flex-column gap-2">

                    <a href="{{ route('program.areas.evaluations') }}" class="btn btn-primary text-start d-flex align-items-center gap-2">
                        <i class="bx bx-list-check fs-5"></i> View Assessor's Evaluations
                    </a>

                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function refreshDashboard() {
    const btn = document.getElementById('refreshBtn');
    btn.disabled = true;
    btn.querySelector('i').classList.add('bx-spin');
    location.reload();
}
</script>
@endpush