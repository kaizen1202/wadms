<div class="container-fluid">

    <h2 class="mb-4 fw-bold">Accreditor Dashboard</h2>

    {{-- Summary Cards --}}
    <div class="row">

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Programs for Accreditation</h6>
                    <h3 class="fw-bold">7</h3>
                    <small class="text-success">+1 this month</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Accredited Programs</h6>
                    <h3 class="fw-bold text-success">3</h3>
                    <small>Last approved Feb 2026</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Programs Pending Approval</h6>
                    <h3 class="fw-bold text-warning">4</h3>
                    <small>Check evaluations</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Upcoming Accreditation Dates</h6>
                    <h3 class="fw-bold text-info">2</h3>
                    <small>Next visit March 2026</small>
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
                    ✔ BS Nursing approved for accreditation.
                </li>
                <li class="list-group-item">
                    ✔ Reviewed evaluation for BS Computer Sci.
                </li>
                <li class="list-group-item">
                    ✔ Scheduled accreditation visit for BS Accountancy.
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
                Approve Program
            </a>

            <a href="#" class="btn btn-outline-secondary me-2">
                View Evaluations
            </a>

            <a href="#" class="btn btn-outline-success">
                Generate Reports
            </a>

        </div>
    </div>
</div>
