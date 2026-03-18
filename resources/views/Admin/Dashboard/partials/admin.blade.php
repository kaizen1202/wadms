<div class="container-fluid">

    <h2 class="mb-1 fw-bold d-flex align-items-center justify-content-between">
        Admin Dashboard
        <button class="btn btn-sm btn-outline-secondary" onclick="refreshDashboard()" id="refreshBtn">
            <i class="bx bx-refresh me-1"></i> Refresh
        </button>
    </h2>
    <p class="text-muted mb-4">Welcome back! Here's an overview of the accreditation system.</p>

    {{-- ── STAT CARDS ── --}}
    <div class="row g-3 mb-2">

        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary fs-4 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bx bx-group"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size:.75rem;">Assessors & Accreditors</p>
                        <h4 class="fw-bold mb-0">{{ $totalAssessorsAccreditors }}</h4>
                        <small class="text-muted">Registered users</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-warning bg-opacity-10 text-warning fs-4 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bx bx-loader-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size:.75rem;">Ongoing Accreditations</p>
                        <h4 class="fw-bold mb-0 text-warning">{{ $ongoingCount }}</h4>
                        <small class="text-muted">
                            {{ $ongoingAccreditation?->accreditation_date?->format('M d, Y') ?? 'No date set' }}
                        </small>
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
                    <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success fs-4 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="bx bx-badge-check"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0" style="font-size:.75rem;">Completed Accreditations</p>
                        <h4 class="fw-bold mb-0 text-success">{{ $completedCount }}</h4>
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

            @if($ongoingAccreditation)

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div>
                        <h6 class="fw-semibold mb-0">
                            {{ $ongoingAccreditation->title }} {{ $ongoingAccreditation->year }}
                        </h6>
                        <small class="text-muted">Click an area to view its evaluations</small>
                    </div>
                    <span class="badge bg-warning text-dark">Ongoing</span>
                </div>

                <div class="mb-3 mt-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Overall Completion</small>
                        <small class="text-muted fw-semibold">{{ $finalizedAreaCount }} / {{ $totalAreas }} Areas Finalized</small>
                    </div>
                    <div class="progress" style="height:6px; border-radius:99px;">
                        @php $pct = $totalAreas ? round($finalizedAreaCount / $totalAreas * 100) : 0; @endphp
                        <div class="progress-bar bg-primary" role="progressbar" style="width:{{ $pct }}%;" aria-valuenow="{{ $pct }}"></div>
                    </div>
                </div>

                <div class="row g-2">
                    @foreach($overviewAreas as $item)
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
                                            <p class="fw-semibold mb-0 text-truncate text-dark" style="font-size:.83rem;" title="{{ $item['area_name'] }}">
                                                {{ $item['area_name'] }}
                                            </p>
                                            <small class="text-muted">
                                                <i class="bx bx-user me-1"></i>
                                                @if($item['evaluators'] > 0)
                                                    {{ $item['evaluators'] }} evaluator{{ $item['evaluators'] > 1 ? 's' : '' }}
                                                @else
                                                    No evaluator yet
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

            @else
                <p class="text-muted mb-0">No ongoing accreditation at the moment.</p>
            @endif

        </div>
    </div>

    {{-- ── BOTTOM ROW: Activity + Quick Actions ── --}}
    <div class="row g-3 mt-2">

        {{-- Recent Activity --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Recent Activities</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($recentEvaluations as $act)
                            <li class="list-group-item d-flex align-items-center gap-3 py-3">
                                <i class="bx {{ $act['icon'] }} {{ $act['color'] }} fs-5"></i>
                                <span class="flex-grow-1" style="font-size:.875rem;">{{ $act['text'] }}</span>
                                <small class="text-muted flex-shrink-0">{{ $act['time'] }}</small>
                            </li>
                        @empty
                            <li class="list-group-item text-muted py-3">No recent activity.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-semibold">Quick Actions</div>
                <div class="card-body d-flex flex-column gap-2">

                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary text-start d-flex align-items-center gap-2">
                        <i class="bx bx-user-x fs-5"></i> View Pending Accounts
                    </a>

                    <a href="{{ route('users.taskforce.index') }}" class="btn btn-primary text-start d-flex align-items-center gap-2">
                        <i class="bx bx-group fs-5"></i> View Active Accounts
                    </a>

                    <a href="{{ route('program.areas.evaluations') }}" class="btn btn-outline-success text-start d-flex align-items-center gap-2">
                        <i class="bx bx-list-check fs-5"></i> View Assessor's Evaluations
                    </a>

                    {{-- At a Glance --}}
                    <div class="bg-light rounded-3 p-3 mt-2">
                        <p class="text-muted fw-semibold mb-2" style="font-size:.72rem; letter-spacing:.06em; text-transform:uppercase;">At a Glance</p>
                        <div class="row text-center g-0">
                            <div class="col-4">
                                <h5 class="fw-bold text-primary mb-0">{{ $pendingAccounts }}</h5>
                                <small class="text-muted">Pending<br>Accounts</small>
                            </div>
                            <div class="col-4">
                                <h5 class="fw-bold text-success mb-0">{{ $finalizedAreas }}</h5>
                                <small class="text-muted">Finalized<br>Areas</small>
                            </div>
                        </div>
                    </div>

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