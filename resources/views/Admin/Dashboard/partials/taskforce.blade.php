<div class="container-fluid">

    <h2 class="mb-4 fw-bold">Task Force Dashboard</h2>

    {{-- Summary Cards --}}
    <div class="row">

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Programs Assigned</h6>
                    <h3 class="fw-bold">5</h3>
                    <small class="text-success">+2 this week</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Pending Evaluations</h6>
                    <h3 class="fw-bold text-warning">2</h3>
                    <small>Due this month</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Completed Evaluations</h6>
                    <h3 class="fw-bold text-success">3</h3>
                    <small>Submitted last week</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Upcoming Deadlines</h6>
                    <h3 class="fw-bold text-info">2</h3>
                    <small>Check schedule</small>
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
                    ✔ Area I evaluation submitted for BS Nursing.
                </li>
                <li class="list-group-item">
                    ✔ Assigned to Area III for BS Accountancy.
                </li>
                <li class="list-group-item">
                    ✔ Reviewed internal assessor comments.
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
                Submit Report
            </a>

            <a href="#" class="btn btn-outline-success">
                View Program Details
            </a>

        </div>
    </div>

</div>
