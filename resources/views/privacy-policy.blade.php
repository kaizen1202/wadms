<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Privacy Policy — WADMS</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
        body { font-family: 'Public Sans', sans-serif; background: #f5f5f9; }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* TOC nav hover */
        .toc-link { display:flex; align-items:center; gap:10px; padding:9px 20px;
                    font-size:0.82rem; color:#566a7f; text-decoration:none;
                    transition:background .15s, color .15s; border-left:3px solid transparent; }
        .toc-link:hover { background:rgba(105,108,255,0.08); color:#696cff;
                          border-left-color:#696cff; }

        /* Section card accent */
        .section-card { border-top:3px solid #696cff; }

        /* Prohibited list */
        .prohibited-item { display:flex; align-items:flex-start; gap:10px;
                           padding:8px 0; border-bottom:1px solid #fce4e4;
                           font-size:0.87rem; color:#4a1818; }
        .prohibited-item:last-child { border-bottom:none; }

        /* Sticky sidebar */
        @media (min-width:992px) { .sticky-sidebar { position:sticky; top:24px; } }
    </style>
</head>
<body>

<div class="layout-wrapper layout-content-navbar layout-without-menu">
<div class="layout-container">
<div class="layout-page">

    <!-- Topbar -->
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme px-4"
         style="border-bottom:1px solid rgba(0,0,0,0.06);">
        <div class="d-flex align-items-center gap-3 w-100">
            <a href="{{ route('register') }}" class="btn btn-sm btn-icon btn-outline-secondary">
                <i class="bx bx-arrow-back"></i>
            </a>
            <span class="fw-semibold" style="font-size:0.85rem; letter-spacing:0.3px;">
                WEB-BASED ACCREDITATION DOCUMENT MANAGEMENT SYSTEM
            </span>
            <span class="badge bg-label-primary ms-auto" style="font-size:0.72rem;">
                RA 10173 — Data Privacy Act of 2012
            </span>
        </div>
    </nav>

    <!-- Content -->
    <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <!-- Page header card -->
        <div class="card mb-4" style="border-top:4px solid #696cff;">
            <div class="card-body py-4 px-5">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:52px;height:52px;background:rgba(105,108,255,0.12);border-radius:10px;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bx bx-shield-quarter text-primary" style="font-size:1.7rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Privacy Policy (Sample Draft)</h4>
                        <p class="text-muted mb-0" style="font-size:0.83rem;">
                            Palompon Institute of Technology &bull; WADMS
                        </p>
                    </div>
                    <div class="ms-auto text-end d-none d-md-block">
                        <div class="text-muted" style="font-size:0.75rem;">Effective Date</div>
                        <strong style="font-size:0.9rem;">{{ \Carbon\Carbon::now()->format('F d, Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <!-- ── LEFT: Table of Contents ── -->
            <div class="col-lg-3">
                <div class="card sticky-sidebar">
                    <div class="card-header py-3 d-flex align-items-center gap-2">
                        <i class="bx bx-list-ul text-primary"></i>
                        <h6 class="fw-bold mb-0">Table of Contents</h6>
                    </div>
                    <div class="card-body p-0">
                        @php $toc = [
                            ['id'=>'s1',  'icon'=>'bx-info-circle',  'label'=>'Introduction'],
                            ['id'=>'s2',  'icon'=>'bx-user-check',   'label'=>'Scope & Users'],
                            ['id'=>'s3',  'icon'=>'bx-data',         'label'=>'Data We Collect'],
                            ['id'=>'s4',  'icon'=>'bx-target-lock',  'label'=>'Purpose of Collection'],
                            ['id'=>'s5',  'icon'=>'bx-file',         'label'=>'Legal Basis'],
                            ['id'=>'s6',  'icon'=>'bx-share-alt',    'label'=>'Data Sharing'],
                            ['id'=>'s7',  'icon'=>'bx-time-five',    'label'=>'Data Retention'],
                            ['id'=>'s8',  'icon'=>'bx-lock-alt',     'label'=>'Data Security'],
                            ['id'=>'s9',  'icon'=>'bx-id-card',      'label'=>'Your Rights'],
                            ['id'=>'s10', 'icon'=>'bx-group',        'label'=>"Children's Privacy"],
                            ['id'=>'s11', 'icon'=>'bx-edit',         'label'=>'Amendments'],
                            ['id'=>'s12', 'icon'=>'bx-envelope',     'label'=>'Contact'],
                        ]; @endphp
                        @foreach($toc as $i => $t)
                        <a href="#{{ $t['id'] }}" class="toc-link">
                            <span class="text-muted" style="font-size:0.68rem;min-width:18px;font-weight:600;">
                                {{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}
                            </span>
                            <i class="bx {{ $t['icon'] }}" style="font-size:1rem;"></i>
                            {{ $t['label'] }}
                        </a>
                        @endforeach
                    </div>
                    <div class="card-footer py-3">
                        <a href="{{ route('terms') }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bx bx-file-blank me-1"></i> Terms &amp; Conditions
                        </a>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT: Content ── -->
            <div class="col-lg-9">

                <!-- Intro notice -->
                <div class="alert alert-primary d-flex gap-3 align-items-start mb-4"
                     style="border-left:4px solid #696cff;border-radius:8px;">
                    <i class="bx bx-info-circle flex-shrink-0 mt-1" style="font-size:1.1rem;"></i>
                    <div style="font-size:0.88rem;">
                        <strong>Palompon Institute of Technology</strong> is committed to safeguarding your personal data.
                        This Privacy Policy explains how <strong>WADMS</strong> collects, uses, stores, and protects your
                        information in accordance with <strong>RA 10173 — Data Privacy Act of 2012</strong>.
                        By accessing the system, you acknowledge and accept the practices described herein.
                    </div>
                </div>

                <!-- ─ S1: Introduction ─ -->
                <div class="card mb-4 section-card" id="s1">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">01</span>
                        <i class="bx bx-info-circle text-primary"></i>
                        <h6 class="fw-bold mb-0">Introduction</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p>
                            Palompon Institute of Technology ("we," "our," or "us") operates WADMS to manage and streamline
                            accreditation document workflows. We are legally obligated under RA 10173 and its Implementing
                            Rules and Regulations to process personal data lawfully, transparently, and securely.
                        </p>
                        <p class="mb-0">
                            If you do not agree with any part of this policy, you must discontinue use of the System
                            immediately and notify your designated administrator.
                        </p>
                    </div>
                </div>

                <!-- ─ S2: Scope ─ -->
                <div class="card mb-4 section-card" id="s2">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">02</span>
                        <i class="bx bx-user-check text-primary"></i>
                        <h6 class="fw-bold mb-0">Scope &amp; Covered Users</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <p class="text-muted mb-3">This policy applies to all individuals granted access to WADMS:</p>
                        <div class="row g-3">
                            @php $roles = [
                                ['icon'=>'bx-check-shield','color'=>'primary', 'label'=>'IQA Officers',      'desc'=>'Manage accreditation info and oversee the overall process'],
                                ['icon'=>'bx-buildings',   'color'=>'info',    'label'=>'Deans',              'desc'=>'Assign task forces to specific accreditation areas'],
                                ['icon'=>'bx-group',       'color'=>'success', 'label'=>'Task Force Members', 'desc'=>'Upload required accreditation documents'],
                                ['icon'=>'bx-analyse',     'color'=>'warning', 'label'=>'Internal Assessors', 'desc'=>'Evaluate and assess accreditation areas'],
                                ['icon'=>'bx-user-voice',  'color'=>'danger',  'label'=>'Accreditors',        'desc'=>'View documents and evaluations'],
                            ]; @endphp
                            @foreach($roles as $role)
                            <div class="col-sm-6">
                                <div class="d-flex align-items-start gap-3 p-3 rounded h-100"
                                     style="background:rgba(105,108,255,0.04);border:1px solid rgba(105,108,255,0.12);">
                                    <div style="width:36px;height:36px;background:rgba(105,108,255,0.1);border-radius:8px;
                                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="bx {{ $role['icon'] }} text-{{ $role['color'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.85rem;">{{ $role['label'] }}</div>
                                        <div class="text-muted" style="font-size:0.78rem;">{{ $role['desc'] }}</div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- ─ S3: Data Collected ─ -->
                <div class="card mb-4 section-card" id="s3">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">03</span>
                        <i class="bx bx-data text-primary"></i>
                        <h6 class="fw-bold mb-0">Personal Data We Collect</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <div class="row g-3">
                            @php $dataTypes = [
                                ['title'=>'Account Information',  'icon'=>'bx-user-circle','color'=>'primary',
                                 'items'=>['Full name and institutional email address','Role and designation within the institution','Username and encrypted password']],
                                ['title'=>'Documents & Files',    'icon'=>'bx-file',       'color'=>'success',
                                 'items'=>['Institutional reports, certificates, and evidence','Photos and scanned copies of official documents','Evaluation forms and assessor remarks']],
                                ['title'=>'System Activity Data', 'icon'=>'bx-bar-chart',  'color'=>'warning',
                                 'items'=>['Login timestamps and session records','File upload and download access logs','Task assignments and evaluation submissions']],
                            ]; @endphp
                            @foreach($dataTypes as $dt)
                            <div class="col-md-4">
                                <div class="h-100 p-3 rounded" style="border:1px solid #e4e6ea;">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <i class="bx {{ $dt['icon'] }} text-{{ $dt['color'] }}" style="font-size:1.1rem;"></i>
                                        <strong style="font-size:0.82rem;">{{ $dt['title'] }}</strong>
                                    </div>
                                    <ul class="mb-0 ps-3" style="font-size:0.81rem;color:#555;line-height:1.8;">
                                        @foreach($dt['items'] as $item)<li>{{ $item }}</li>@endforeach
                                    </ul>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- ─ S4: Purpose ─ -->
                <div class="card mb-4 section-card" id="s4">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">04</span>
                        <i class="bx bx-target-lock text-primary"></i>
                        <h6 class="fw-bold mb-0">Purpose of Data Collection</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <p class="text-muted mb-3">We collect and process personal data exclusively for these legitimate purposes:</p>
                        @php $purposes = [
                            'Authenticating and managing user access to WADMS',
                            'Facilitating the end-to-end accreditation document workflow',
                            'Enabling Dean-to-Task Force task assignment and tracking',
                            'Supporting Internal Assessor evaluations of accreditation areas',
                            'Providing Accreditors with access to documents and evaluations',
                            'Maintaining audit trails and activity logs for accountability',
                            'Improving system functionality, usability, and security',
                        ]; @endphp
                        <div class="row g-2">
                            @foreach($purposes as $i => $p)
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 py-2 px-3 rounded"
                                     style="background:#f8f8ff;border:1px solid rgba(105,108,255,0.1);">
                                    <span class="badge bg-label-primary mt-1 flex-shrink-0"
                                          style="font-size:0.65rem;min-width:20px;">{{ $i+1 }}</span>
                                    <span style="font-size:0.82rem;color:#444;">{{ $p }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- ─ S5: Legal Basis ─ -->
                <div class="card mb-4 section-card" id="s5">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">05</span>
                        <i class="bx bx-file text-primary"></i>
                        <h6 class="fw-bold mb-0">Legal Basis for Processing</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <p class="text-muted mb-3">Personal data is processed under the following legal bases recognized by RA 10173:</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:200px;">Legal Basis</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><span class="badge bg-label-primary">Consent</span></td>
                                        <td>Users provide informed consent upon account registration.</td></tr>
                                    <tr><td><span class="badge bg-label-success">Contractual Necessity</span></td>
                                        <td>Processing supports the institution's accreditation obligations.</td></tr>
                                    <tr><td><span class="badge bg-label-warning">Legitimate Interest</span></td>
                                        <td>Required to maintain accreditation standards and institutional integrity.</td></tr>
                                    <tr><td><span class="badge bg-label-info">Legal Compliance</span></td>
                                        <td>As required by CHED and applicable Philippine regulatory bodies.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ─ S6: Sharing ─ -->
                <div class="card mb-4 section-card" id="s6">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">06</span>
                        <i class="bx bx-share-alt text-primary"></i>
                        <h6 class="fw-bold mb-0">Data Sharing &amp; Disclosure</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p>We do not sell, trade, or rent your personal data. Data may only be shared in these limited circumstances:</p>
                        <ul class="mb-0" style="line-height:2.1;">
                            <li>With authorized PIT personnel directly involved in the accreditation process.</li>
                            <li>With accreditation bodies and external assessors as required by the process.</li>
                            <li>When compelled by law, court order, or a competent government authority.</li>
                        </ul>
                    </div>
                </div>

                <!-- ─ S7: Retention ─ -->
                <div class="card mb-4 section-card" id="s7">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">07</span>
                        <i class="bx bx-time-five text-primary"></i>
                        <h6 class="fw-bold mb-0">Data Retention</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p class="mb-0">
                            Personal data and uploaded documents are retained only for as long as necessary to fulfill the
                            purposes stated in this policy, or as required by applicable laws and accreditation standards.
                            Upon expiry of the retention period, data will be securely deleted or anonymized in accordance
                            with NPC guidelines.
                        </p>
                    </div>
                </div>

                <!-- ─ S8: Security ─ -->
                <div class="card mb-4 section-card" id="s8">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">08</span>
                        <i class="bx bx-lock-alt text-primary"></i>
                        <h6 class="fw-bold mb-0">Data Security</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <p class="text-muted mb-3">We implement the following technical and organizational security measures:</p>
                        <div class="row g-3 mb-3">
                            @php $measures = [
                                ['icon'=>'bx-key',        'color'=>'primary', 'text'=>'Secure user authentication with encrypted credentials'],
                                ['icon'=>'bx-shield',     'color'=>'success', 'text'=>'Role-based access controls limiting data access to authorized users only'],
                                ['icon'=>'bx-list-check', 'color'=>'info',    'text'=>'Comprehensive system activity logging for audit purposes'],
                                ['icon'=>'bx-revision',   'color'=>'warning', 'text'=>'Regular review and assessment of security practices'],
                            ]; @endphp
                            @foreach($measures as $m)
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                     style="background:#f8f9fa;border:1px solid #e4e6ea;">
                                    <i class="bx {{ $m['icon'] }} text-{{ $m['color'] }} mt-1 flex-shrink-0"></i>
                                    <span style="font-size:0.82rem;color:#444;">{{ $m['text'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="alert alert-warning d-flex gap-2 align-items-start mb-0 py-2" style="font-size:0.82rem;">
                            <i class="bx bx-error-circle mt-1 flex-shrink-0"></i>
                            While we apply commercially reasonable safeguards, no electronic system can guarantee absolute
                            security. Users are responsible for maintaining the confidentiality of their own credentials.
                        </div>
                    </div>
                </div>

                <!-- ─ S9: Rights ─ -->
                <div class="card mb-4 section-card" id="s9">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">09</span>
                        <i class="bx bx-id-card text-primary"></i>
                        <h6 class="fw-bold mb-0">Your Rights as a Data Subject</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <p class="text-muted mb-3">Under RA 10173, you are entitled to the following rights:</p>
                        <div class="row g-3">
                            @php $rights = [
                                ['icon'=>'bx-bell',          'color'=>'primary',   'title'=>'Right to be Informed',   'desc'=>'Know what personal data is collected and how it is used.'],
                                ['icon'=>'bx-search',        'color'=>'info',      'title'=>'Right to Access',        'desc'=>'Request a copy of your personal data held in the System.'],
                                ['icon'=>'bx-edit',          'color'=>'success',   'title'=>'Right to Rectification', 'desc'=>'Correct inaccurate or incomplete personal data.'],
                                ['icon'=>'bx-trash',         'color'=>'danger',    'title'=>'Right to Erasure',       'desc'=>'Request deletion or blocking of data under certain conditions.'],
                                ['icon'=>'bx-transfer',      'color'=>'warning',   'title'=>'Right to Portability',   'desc'=>'Obtain your data in a structured, commonly used format.'],
                                ['icon'=>'bx-comment-error', 'color'=>'secondary', 'title'=>'Right to Complain',      'desc'=>'File a complaint with the National Privacy Commission (NPC).'],
                            ]; @endphp
                            @foreach($rights as $r)
                            <div class="col-sm-6 col-lg-4">
                                <div class="h-100 p-3 rounded text-center" style="border:1px solid #e4e6ea;">
                                    <div style="width:44px;height:44px;background:rgba(105,108,255,0.1);border-radius:50%;
                                                display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                        <i class="bx {{ $r['icon'] }} text-{{ $r['color'] }}" style="font-size:1.2rem;"></i>
                                    </div>
                                    <div class="fw-semibold mb-1" style="font-size:0.82rem;">{{ $r['title'] }}</div>
                                    <div class="text-muted" style="font-size:0.78rem;">{{ $r['desc'] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-3 p-3 rounded d-flex align-items-center gap-2 flex-wrap"
                             style="background:#f0f0ff;border:1px solid rgba(105,108,255,0.2);font-size:0.85rem;">
                            <i class="bx bx-envelope text-primary"></i>
                            To exercise any of the above rights, contact us at:
                            <a href="mailto:pit@pit.edu.ph" class="fw-semibold">pit@pit.edu.ph</a>
                        </div>
                    </div>
                </div>

                <!-- ─ S10: Children ─ -->
                <div class="card mb-4 section-card" id="s10">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">10</span>
                        <i class="bx bx-group text-primary"></i>
                        <h6 class="fw-bold mb-0">Children's Privacy</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p class="mb-0">
                            WADMS is intended solely for institutional personnel and accreditation stakeholders.
                            We do not knowingly collect personal data from individuals under 18 years of age.
                        </p>
                    </div>
                </div>

                <!-- ─ S11: Amendments ─ -->
                <div class="card mb-4 section-card" id="s11">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">11</span>
                        <i class="bx bx-edit text-primary"></i>
                        <h6 class="fw-bold mb-0">Amendments</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p class="mb-0">
                            We reserve the right to update this Privacy Policy at any time. Users will be notified of
                            material changes through an in-system notice. Continued use of WADMS after changes are posted
                            constitutes acceptance of the revised policy.
                        </p>
                    </div>
                </div>

                <!-- ─ S12: Contact ─ -->
                <div class="card mb-4 section-card" id="s12">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">12</span>
                        <i class="bx bx-envelope text-primary"></i>
                        <h6 class="fw-bold mb-0">Contact Information</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3 p-3 rounded h-100"
                                     style="background:#f8f9fa;border:1px solid #e4e6ea;">
                                    <i class="bx bx-buildings text-primary mt-1" style="font-size:1.3rem;flex-shrink:0;"></i>
                                    <div>
                                        <div class="fw-bold mb-1">Palompon Institute of Technology</div>
                                        <div class="text-muted" style="font-size:0.82rem;">Office of the Data Protection Officer</div>
                                        <div class="text-muted" style="font-size:0.82rem;">Palompon, Leyte, Philippines</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3 p-3 rounded h-100"
                                     style="background:#f8f9fa;border:1px solid #e4e6ea;">
                                    <i class="bx bx-envelope text-primary mt-1" style="font-size:1.3rem;flex-shrink:0;"></i>
                                    <div>
                                        <div class="fw-bold mb-1">Email Us</div>
                                        <a href="mailto:pit@pit.edu.ph" class="text-primary" style="font-size:0.88rem;">pit@pit.edu.ph</a>
                                        <div class="text-muted mt-1" style="font-size:0.78rem;">For data subject requests and privacy concerns</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 pb-4 px-1"
                     style="border-top:1px solid #e4e6ea;font-size:0.8rem;color:#888;">
                    <span>&copy; {{ date('Y') }} Palompon Institute of Technology. All rights reserved.</span>
                    <a href="{{ route('terms') }}" class="text-primary fw-semibold">
                        View Terms &amp; Conditions <i class="bx bx-right-arrow-alt"></i>
                    </a>
                </div>

            </div>{{-- /.col-lg-9 --}}
        </div>{{-- /.row --}}
    </div>{{-- /.container-xxl --}}
    </div>{{-- /.content-wrapper --}}
</div>{{-- /.layout-page --}}
</div>{{-- /.layout-container --}}
</div>{{-- /.layout-wrapper --}}

<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

</body>
</html>