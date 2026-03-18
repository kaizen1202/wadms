<div class="container-fluid">

    <h2 class="mb-4 fw-bold">Dean Dashboard</h2>

    {{-- Summary Cards --}}
    <div class="row">

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Programs</h6>
                    <h3 class="fw-bold">8</h3>
                    <small class="text-success">+1 this month</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Programs Pending Evaluation</h6>
                    <h3 class="fw-bold text-warning">3</h3>
                    <small>Check with assessors</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Programs Accredited</h6>
                    <h3 class="fw-bold text-success">4</h3>
                    <small>Last accredited Feb 2026</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Programs Under Review</h6>
                    <h3 class="fw-bold text-info">1</h3>
                    <small>Review ongoing</small>
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
                    ✔ BS Nursing evaluation approved.
                </li>
                <li class="list-group-item">
                    ✔ BS Education pending Area IV review.
                </li>
                <li class="list-group-item">
                    ✔ New program BS Data Science added.
                </li>
                <li class="list-group-item">
                    ✔ Accreditation schedule updated.
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
                View Programs
            </a>

            <a href="#" class="btn btn-outline-secondary me-2">
                Approve Evaluations
            </a>

            <a href="#" class="btn btn-outline-success">
                Generate Reports
            </a>

        </div>
    </div>

</div>
