<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terms &amp; Conditions — WADMS</title>

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
        html { scroll-behavior: smooth; }

        .toc-link { display:flex; align-items:center; gap:10px; padding:9px 20px;
                    font-size:0.82rem; color:#566a7f; text-decoration:none;
                    transition:background .15s, color .15s; border-left:3px solid transparent; }
        .toc-link:hover { background:rgba(105,108,255,0.08); color:#696cff;
                          border-left-color:#696cff; }

        .section-card { border-top:3px solid #696cff; }

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
                Terms &amp; Conditions
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
                        <i class="bx bx-file-blank text-primary" style="font-size:1.7rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Terms &amp; Conditions (Sample Draft)</h4>
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
                            ['id'=>'t1',  'icon'=>'bx-check-circle',  'label'=>'Acceptance of Terms'],
                            ['id'=>'t2',  'icon'=>'bx-book-open',      'label'=>'Definitions'],
                            ['id'=>'t3',  'icon'=>'bx-user-circle',    'label'=>'Accounts & Access'],
                            ['id'=>'t4',  'icon'=>'bx-shield-x',       'label'=>'Acceptable Use'],
                            ['id'=>'t5',  'icon'=>'bx-copyright',      'label'=>'Intellectual Property'],
                            ['id'=>'t6',  'icon'=>'bx-lock',           'label'=>'Data Privacy'],
                            ['id'=>'t7',  'icon'=>'bx-server',         'label'=>'System Availability'],
                            ['id'=>'t8',  'icon'=>'bx-error-circle',   'label'=>'Disclaimers'],
                            ['id'=>'t9',  'icon'=>'bx-block',          'label'=>'Sanctions & Termination'],
                            ['id'=>'t10', 'icon'=>'bx-edit',           'label'=>'Amendments'],
                            ['id'=>'t11', 'icon'=>'bx-globe',          'label'=>'Governing Law'],
                            ['id'=>'t12', 'icon'=>'bx-envelope',       'label'=>'Contact Us'],
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
                        <a href="{{ route('privacy') }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bx bx-shield-quarter me-1"></i> Privacy Policy
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
                        These Terms and Conditions govern your access to and use of <strong>WADMS</strong>,
                        developed and operated by <strong>Palompon Institute of Technology</strong>.
                        By accessing or using the System, you agree to be legally bound by these Terms.
                        If you do not agree, you must cease use of the System immediately.
                    </div>
                </div>

                <!-- ─ T1: Acceptance ─ -->
                <div class="card mb-4 section-card" id="t1">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">01</span>
                        <i class="bx bx-check-circle text-primary"></i>
                        <h6 class="fw-bold mb-0">Acceptance of Terms</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p class="mb-0">
                            These Terms constitute a binding legal agreement between you and Palompon Institute of
                            Technology ("the Institution"). Your continued use of WADMS constitutes ongoing acceptance
                            of these Terms and any updates that may be posted from time to time.
                        </p>
                    </div>
                </div>

                <!-- ─ T2: Definitions ─ -->
                <div class="card mb-4 section-card" id="t2">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">02</span>
                        <i class="bx bx-book-open text-primary"></i>
                        <h6 class="fw-bold mb-0">Definitions</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:160px;">Term</th>
                                        <th>Definition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><span class="badge bg-label-primary">"System"</span></td>
                                        <td>The WADMS web application operated by Palompon Institute of Technology.</td></tr>
                                    <tr><td><span class="badge bg-label-info">"User"</span></td>
                                        <td>Any individual granted access, including IQA Officers, Deans, Task Force members, Internal Assessors, and Accreditors.</td></tr>
                                    <tr><td><span class="badge bg-label-success">"Content"</span></td>
                                        <td>Documents, files, data, photos, or media uploaded to or generated within the System.</td></tr>
                                    <tr><td><span class="badge bg-label-warning">"Administrator"</span></td>
                                        <td>Users with elevated privileges responsible for managing accounts and system settings.</td></tr>
                                    <tr><td><span class="badge bg-label-secondary">"Institution"</span></td>
                                        <td>Palompon Institute of Technology, the owner and operator of WADMS.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ─ T3: Accounts & Access ─ -->
                <div class="card mb-4 section-card" id="t3">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">03</span>
                        <i class="bx bx-user-circle text-primary"></i>
                        <h6 class="fw-bold mb-0">User Accounts &amp; Access</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">

                        <p class="fw-semibold mb-2" style="font-size:0.84rem;color:#566a7f;">
                            <i class="bx bx-shield-alt-2 me-1"></i> Role-Based Access Permissions
                        </p>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-hover align-middle mb-0" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:175px;">Role</th>
                                        <th>Permissions &amp; Responsibilities</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td><span class="badge bg-label-primary">IQA Officer</span></td>
                                        <td>Manages accreditation information and oversees the overall process.</td></tr>
                                    <tr><td><span class="badge bg-label-info">Dean</span></td>
                                        <td>Assigns task forces to specific accreditation areas.</td></tr>
                                    <tr><td><span class="badge bg-label-success">Task Force</span></td>
                                        <td>Uploads required accreditation documents for assigned areas.</td></tr>
                                    <tr><td><span class="badge bg-label-warning">Internal Assessor</span></td>
                                        <td>Reviews and evaluates accreditation areas and submitted documents.</td></tr>
                                    <tr><td><span class="badge bg-label-danger">Accreditor</span></td>
                                        <td>Views documents and evaluations submitted by the Internal Assessor.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info d-flex gap-2 align-items-start py-2 mb-4" style="font-size:0.83rem;">
                            <i class="bx bx-info-circle mt-1 flex-shrink-0"></i>
                            Users must not attempt to access features, data, or areas beyond their assigned role and permissions.
                        </div>

                        <p class="fw-semibold mb-2" style="font-size:0.84rem;color:#566a7f;">
                            <i class="bx bx-check-double me-1"></i> User Responsibilities
                        </p>
                        <ul class="mb-0" style="font-size:0.88rem;line-height:2.1;color:#444;">
                            <li>Use the System only for its intended accreditation management purposes.</li>
                            <li>Keep login credentials confidential and never share them with any other person.</li>
                            <li>Immediately notify the administrator of any unauthorized access or suspected security breach.</li>
                            <li>Comply with all applicable laws, including the Data Privacy Act of 2012 (RA 10173).</li>
                            <li>Upload only accurate, relevant, and lawfully obtained documents and files.</li>
                        </ul>
                    </div>
                </div>

                <!-- ─ T4: Acceptable Use ─ -->
                <div class="card mb-4 section-card" id="t4">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">04</span>
                        <i class="bx bx-shield-x text-primary"></i>
                        <h6 class="fw-bold mb-0">Acceptable Use Policy</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <p class="text-muted mb-3">
                            Users must use WADMS in a lawful and ethical manner. The following actions are
                            <strong class="text-danger">strictly prohibited</strong>:
                        </p>
                        <div style="background:#fff8f8;border:1px solid #f1c0c0;border-left:4px solid #ea5455;
                                    border-radius:6px;padding:16px 20px;">
                            @php $prohibited = [
                                'Uploading malicious files, viruses, or harmful code to the System.',
                                'Attempting to gain unauthorized access to any part of the System or its data.',
                                'Using the System to harass, defraud, or harm any individual or entity.',
                                'Sharing or distributing confidential accreditation documents outside authorized channels.',
                                'Interfering with or disrupting the integrity or performance of the System.',
                                'Impersonating another user or misrepresenting your identity, role, or affiliation.',
                                'Circumventing or attempting to bypass role-based access controls.',
                            ]; @endphp
                            <ul class="mb-0 list-unstyled">
                                @foreach($prohibited as $p)
                                <li class="d-flex align-items-start gap-2 py-1" style="border-bottom:1px solid #fce4e4;font-size:0.87rem;color:#4a1818;">
                                    <i class="bx bx-x text-danger mt-1 flex-shrink-0" style="font-size:1rem;"></i>
                                    <span>{{ $p }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ─ T5: IP ─ -->
                <div class="card mb-4 section-card" id="t5">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">05</span>
                        <i class="bx bx-copyright text-primary"></i>
                        <h6 class="fw-bold mb-0">Intellectual Property</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p>
                            The System, including its design, features, source code, and underlying architecture, is the
                            intellectual property of Palompon Institute of Technology and its development team. All rights
                            are reserved. Users may not copy, modify, distribute, or create derivative works based on the
                            System without prior written consent from the Institution.
                        </p>
                        <p class="mb-0">
                            Documents uploaded by users remain the property of the respective uploader or the Institution
                            as applicable. By uploading content, users grant the Institution a non-exclusive license to
                            process, store, and display such content for accreditation purposes within the System.
                        </p>
                    </div>
                </div>

                <!-- ─ T6: Data Privacy ─ -->
                <div class="card mb-4 section-card" id="t6">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">06</span>
                        <i class="bx bx-lock text-primary"></i>
                        <h6 class="fw-bold mb-0">Data Privacy &amp; Security</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p class="mb-0">
                            The collection, use, and protection of personal data within WADMS is governed by our
                            <a href="{{ route('privacy') }}" class="text-primary fw-semibold">Privacy Policy</a>,
                            which is incorporated into these Terms by reference. By using the System, you consent to the
                            processing of your personal data in accordance with RA 10173 and the NPC's Implementing Rules
                            and Regulations.
                        </p>
                    </div>
                </div>

                <!-- ─ T7: Availability ─ -->
                <div class="card mb-4 section-card" id="t7">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">07</span>
                        <i class="bx bx-server text-primary"></i>
                        <h6 class="fw-bold mb-0">System Availability &amp; Maintenance</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p class="mb-0">
                            Palompon Institute of Technology will make reasonable efforts to keep WADMS available and
                            operational. However, the Institution does not guarantee uninterrupted access and may perform
                            maintenance, apply updates, or temporarily suspend access at any time without prior notice.
                            The Institution shall not be held liable for any inconvenience or loss resulting from system downtime.
                        </p>
                    </div>
                </div>

                <!-- ─ T8: Disclaimers ─ -->
                <div class="card mb-4 section-card" id="t8">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">08</span>
                        <i class="bx bx-error-circle text-primary"></i>
                        <h6 class="fw-bold mb-0">Disclaimers &amp; Limitation of Liability</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <p class="text-muted mb-3" style="line-height:1.85;">
                            WADMS is provided "as is" for institutional accreditation purposes. The Institution does not
                            warrant that the System will be error-free or that uploaded documents will automatically
                            satisfy accreditation requirements set by external bodies.
                        </p>
                        <p class="fw-semibold mb-2" style="font-size:0.85rem;">
                            To the fullest extent permitted by Philippine law, the Institution shall not be liable for:
                        </p>
                        <div class="row g-2">
                            @php $limits = [
                                ['icon'=>'bx-trending-down','color'=>'warning',  'text'=>'Indirect, incidental, or consequential damages arising from use of the System.'],
                                ['icon'=>'bx-cloud-rain',   'color'=>'info',     'text'=>'Loss of data due to technical failures beyond the Institution\'s reasonable control.'],
                                ['icon'=>'bx-lock-open',    'color'=>'danger',   'text'=>'Unauthorized access to user accounts resulting from failure to maintain credential security.'],
                                ['icon'=>'bx-spreadsheet',  'color'=>'secondary','text'=>'Decisions made by accreditation bodies based on content submitted through the System.'],
                            ]; @endphp
                            @foreach($limits as $l)
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2 p-3 rounded"
                                     style="background:#f8f9fa;border:1px solid #e4e6ea;">
                                    <i class="bx {{ $l['icon'] }} text-{{ $l['color'] }} mt-1 flex-shrink-0"></i>
                                    <span style="font-size:0.82rem;color:#444;">{{ $l['text'] }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- ─ T9: Sanctions ─ -->
                <div class="card mb-4 section-card" id="t9">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">09</span>
                        <i class="bx bx-block text-primary"></i>
                        <h6 class="fw-bold mb-0">Sanctions &amp; Termination</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p>
                            The Institution reserves the right to suspend or permanently terminate a user's access to
                            WADMS, without prior notice, if the user:
                        </p>
                        <ul class="mb-3" style="line-height:2.1;">
                            <li>Violates any provision of these Terms or the Privacy Policy.</li>
                            <li>Engages in conduct deemed harmful to the System, the Institution, or other users.</li>
                            <li>Misuses, abuses, or otherwise exploits system privileges.</li>
                        </ul>
                        <div class="alert alert-warning d-flex gap-2 align-items-start mb-0 py-2" style="font-size:0.82rem;">
                            <i class="bx bx-error mt-1 flex-shrink-0"></i>
                            Termination of access does not affect any rights or obligations that accrued prior to the
                            date of termination.
                        </div>
                    </div>
                </div>

                <!-- ─ T10: Amendments ─ -->
                <div class="card mb-4 section-card" id="t10">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">10</span>
                        <i class="bx bx-edit text-primary"></i>
                        <h6 class="fw-bold mb-0">Amendments</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p class="mb-0">
                            The Institution reserves the right to modify these Terms at any time. Users will be notified
                            of material changes through an in-system notice. Continued use of WADMS following notification
                            of any change constitutes acceptance of the revised Terms.
                        </p>
                    </div>
                </div>

                <!-- ─ T11: Governing Law ─ -->
                <div class="card mb-4 section-card" id="t11">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">11</span>
                        <i class="bx bx-globe text-primary"></i>
                        <h6 class="fw-bold mb-0">Governing Law</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;line-height:1.85;color:#444;">
                        <p class="mb-0">
                            These Terms shall be governed by and construed in accordance with the laws of the Republic
                            of the Philippines, including the Data Privacy Act of 2012 (RA 10173), applicable regulations
                            issued by the National Privacy Commission, and the Commission on Higher Education (CHED)
                            directives. Any disputes shall be resolved in the competent courts of Leyte, Philippines.
                        </p>
                    </div>
                </div>

                <!-- ─ T12: Contact ─ -->
                <div class="card mb-4 section-card" id="t12">
                    <div class="card-header d-flex align-items-center gap-2 py-3">
                        <span class="badge bg-primary" style="font-size:0.68rem;">12</span>
                        <i class="bx bx-envelope text-primary"></i>
                        <h6 class="fw-bold mb-0">Contact Us</h6>
                    </div>
                    <div class="card-body" style="font-size:0.9rem;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3 p-3 rounded h-100"
                                     style="background:#f8f9fa;border:1px solid #e4e6ea;">
                                    <i class="bx bx-buildings text-primary mt-1" style="font-size:1.3rem;flex-shrink:0;"></i>
                                    <div>
                                        <div class="fw-bold mb-1">Palompon Institute of Technology</div>
                                        <div class="text-muted" style="font-size:0.82rem;">Office of the Internal Quality Assurance</div>
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
                                        <div class="text-muted mt-1" style="font-size:0.78rem;">For legal inquiries and terms-related concerns</div>
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
                    <a href="{{ route('privacy') }}" class="text-primary fw-semibold">
                        <i class="bx bx-left-arrow-alt"></i> View Privacy Policy
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