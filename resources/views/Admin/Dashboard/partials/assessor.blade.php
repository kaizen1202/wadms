<div class="container-fluid">

    <h2 class="mb-4 fw-bold">Internal Assessor Dashboard</h2>

    {{-- Summary Cards --}}
    <div class="row">

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Evaluations Assigned</h6>
                    <h3 class="fw-bold">6</h3>
                    <small class="text-success">+1 this week</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Evaluations Submitted</h6>
                    <h3 class="fw-bold text-success">2</h3>
                    <small>Recent submissions</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Evaluations Pending</h6>
                    <h3 class="fw-bold text-warning">4</h3>
                    <small>Due soon</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Average Score</h6>
                    <h3 class="fw-bold text-info">87%</h3>
                    <small>Across submitted evaluations</small>
                </div>
            </div>
        </div>

    </div>

    {{-- Recent Activity --}}
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white fw-semibold">
            Recent Activities
        </div>
        <div class="card-body">

            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    ✔ Submitted Area II evaluation for BS Nursing.
                </li>
                <li class="list-group-item">
                    ✔ Started Area IV evaluation for BS Education.
                </li>
                <li class="list-group-item">
                    ✔ Added comments for BS Accountancy Area III.
                </li>
            </ul>

        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white fw-semibold">
            Quick Actions
        </div>
        <div class="card-body">

            <a href="#" class="btn btn-primary me-2">
                Start Evaluation
            </a>

            <a href="#" class="btn btn-outline-secondary me-2">
                Submit Evaluation
            </a>

            <a href="#" class="btn btn-outline-success">
                View Program Details
            </a>

        </div>
    </div>

</div>
