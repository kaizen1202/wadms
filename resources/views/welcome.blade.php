<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'WADMS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */ (keep the existing huge style block as is)
        </style>
    @endif

    <!-- Custom styles for connected dots -->
    <style>
        .connected-dots-list {
            list-style: none;
            margin: 0;
            padding: 0;
            position: relative;
        }
        .connected-dots-list li {
            position: relative;
            padding-left: 1.75rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .connected-dots-list li:last-child {
            margin-bottom: 0;
        }
        .connected-dots-list li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.4rem;
            width: 0.75rem;
            height: 0.75rem;
            background-color: #696CFF; /* primary color */
            border-radius: 50%;
            z-index: 2;
        }
        .connected-dots-list li:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 0.35rem;
            top: 1rem;
            width: 2px;
            height: calc(100% + 0.5rem);
            background: repeating-linear-gradient(
                to bottom,
                #696CFF 0px,
                #696CFF 4px,
                transparent 4px,
                transparent 8px
            );
            z-index: 1;
        }
    </style>
</head>
<body class="bg-white text-gray-800 font-sans">

@php
    $primary = '#696CFF'; // Define primary color

    // System Users data with bullet points for everyone
    $users = [
        [
            'title' => 'Internal Quality Assurance',
            'bullets' => [
                'Manage necessary accreditation information',
                'Verify internal assessors and accreditors accounts',
                'Assign internal assessors to specific areas',
                'View internal assessor ratings and summary of ratings (area mean and grand mean)',
            ],
        ],
        [
            'title' => 'College Dean',
            'bullets' => [
                'Verify task forces accounts',
                'Assign task forces to an area',
                'Track who uploads document in an area',
                'View ratings and summary of ratings (area mean and grand mean)',
                'View archived or completed accreditations'
            ],
        ],
        [
            'title' => 'Accreditation Task Force',
            'bullets' => [
                'Upload documentary evidence for assigned areas',
                'Categorize files by parameter and sub-parameter',
                'Maintain organized document structure',
                'Ensure completeness of accreditation requirements',
            ],
        ],
        [
            'title' => 'Internal Assessors',
            'bullets' => [
                'Review submitted documents against standards',
                'Assign preliminary ratings',
                'Record findings and observations',
                'Provide recommendations for improvement',
            ],
        ],
        [
            'title' => 'Accreditors',
            'bullets' => [
                'View uploaded documents in each areas',
                'View internal assessor ratings and summary of ratings (area mean and grand mean)',
                'Record findings and observations',
                'Provide recommendations for improvement',
            ],
        ],
        
    ];

    // Core Capabilities data
    $capabilities = [
        ['title' => 'Role-Based Access Control', 'description' => 'Grants access based on assigned roles to ensure secure handling.'],
        ['title' => 'Structured Document Categorization', 'description' => 'Organizes files by level, area, parameter, and sub-parameter.'],
        ['title' => 'Internal Evaluation Monitoring', 'description' => 'Enables evaluators to submit ratings and findings digitally.'],
        ['title' => 'Centralized Secure Repository', 'description' => 'Maintains consolidated accreditation documentation.'],
    ];

    // Workflow steps data
    $workflow = [
        ['step' => 1, 'title' => 'Accreditation Setup', 'desc' => 'Administrator defines cycle, areas, and assignments.'],
        ['step' => 2, 'title' => 'Document Submission', 'desc' => 'Task force uploads documentary evidence.'],
        ['step' => 3, 'title' => 'Internal Evaluation', 'desc' => 'Evaluators review and record ratings.'],
        ['step' => 4, 'title' => 'Accreditation Readiness', 'desc' => 'Reports and documents prepared for survey visits.'],
    ];
@endphp

<!-- ================= NAVBAR ================= -->
<header class="absolute top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <!-- Logo + Title -->
        <div class="flex items-center space-x-3">
            <img src="{{ asset('assets/img/wdms/pit-logo-outlined.png') }}"
                 alt="University Logo"
                 class="h-12 w-auto object-contain">
            <div class="leading-tight">
                <!-- Changed text color to dark for light mode -->
                <h1 class="text-lg font-bold text-gray-100">PALOMPON INSTITUTE OF TECHNOLOGY</h1>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
            <a href="{{ route('login') }}" class="text-gray-100 hover:bg-[#696CFF] rounded-lg px-5 py-2 transition">Login</a>
            <a href="{{ route('register') }}"
               class="bg-[#696CFF] hover:bg-[#5F61E6] text-white px-5 py-2 rounded-lg shadow transition">
                Register
            </a>
        </nav>
    </div>
</header>

<!-- ================= HERO SECTION ================= -->
<section class="relative min-h-screen flex items-center justify-center pt-24 bg-cover bg-center"
         style="background-image: url('{{ asset('assets/img/wdms/pit-img.jpg') }}');">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative max-w-7xl mx-auto px-6 text-center text-white">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
            WEB-BASED ACCREDITATION
            <span class="text-[#696CFF] block">DOCUMENT MANAGEMENT SYSTEM</span>
        </h1>
        <p class="mt-6 text-lg md:text-xl text-gray-200 max-w-3xl mx-auto">
            A web-based accreditation management platform designed to streamline
            task force assignment, document storage, and internal evaluation
            processes for Preliminary Survey Visits at Palompon Institute of Technology.
        </p>
        <div class="mt-10 flex justify-center gap-4 flex-wrap">
            <a href="{{ route('login') }}"
               class="bg-[#696CFF] hover:bg-[#5F61E6] text-white px-6 py-3 rounded-lg shadow-lg transition">
                Get Started
            </a>
            <a href="#users"
               class="bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-lg backdrop-blur transition">
                Learn More
            </a>
        </div>
    </div>
</section>

<!-- ================= SYSTEM USERS ================= -->
<section id="users" class="py-24 bg-gradient-to-b from-white to-gray-50 text-gray-800">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <span class="inline-block bg-[#696CFF]/10 text-[#696CFF] px-4 py-1.5 rounded-full text-sm font-semibold tracking-wide uppercase mb-4">
            Who We Serve
        </span>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">System Users</h2>
        <div class="w-24 h-1 bg-[#696CFF] mx-auto rounded-full mb-6"></div>
        <p class="text-lg max-w-2xl mx-auto text-gray-600">
            The system enables collaboration among academic personnel responsible for accreditation preparation and evaluation.
        </p>

        <div class="mt-16 flex flex-wrap justify-center gap-6">
            @foreach($users as $user)
                <div class="group bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:border-[#696CFF] hover:shadow-xl transition-all duration-300 hover:-translate-y-1 text-left w-full sm:w-80 md:w-72 lg:w-80">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-[#696CFF]/10 rounded-lg flex items-center justify-center group-hover:bg-[#696CFF] transition-colors">
                            <svg class="w-5 h-5 text-[#696CFF] group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-[#696CFF] group-hover:text-[#5F61E6] transition-colors">
                            {{ $user['title'] }}
                        </h3>
                    </div>
                    <ul class="connected-dots-list text-gray-600">
                        @foreach($user['bullets'] as $bullet)
                            <li>{{ $bullet }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ================= CORE CAPABILITIES ================= -->
<section id="core-capabilities" class="py-24 bg-gradient-to-b from-gray-50 to-white text-gray-800">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <span class="inline-block bg-[#696CFF]/10 text-[#696CFF] px-4 py-1.5 rounded-full text-sm font-semibold tracking-wide uppercase mb-4">
            What We Offer
        </span>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Core Capabilities</h2>
        <div class="w-24 h-1 bg-[#696CFF] mx-auto rounded-full mb-6"></div>
        <p class="text-lg max-w-2xl mx-auto text-gray-600">
            Designed to ensure systematic accreditation preparation through
            structured document control and evaluation monitoring.
        </p>

        <div class="mt-16 grid md:grid-cols-2 lg:grid-cols-4 gap-8 text-left">
            @foreach($capabilities as $capability)
                <div class="group bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:border-[#696CFF] hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="w-12 h-12 bg-[#696CFF]/10 rounded-lg flex items-center justify-center mb-4 group-hover:bg-[#696CFF] group-hover:text-white transition-colors">
                        <svg class="w-6 h-6 text-[#696CFF] group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold text-[#696CFF] mb-3">{{ $capability['title'] }}</h4>
                    <p class="text-gray-600 leading-relaxed">{{ $capability['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ================= SYSTEM WORKFLOW ================= -->
<section id="workflow" class="py-24 bg-white text-gray-800">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <span class="inline-block bg-[#696CFF]/10 text-[#696CFF] px-4 py-1.5 rounded-full text-sm font-semibold tracking-wide uppercase mb-4">
            How It Works
        </span>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">System Workflow</h2>
        <div class="w-24 h-1 bg-[#696CFF] mx-auto rounded-full mb-6"></div>
        <p class="text-lg max-w-2xl mx-auto text-gray-600">
            A structured process ensuring transparency and accountability throughout the accreditation cycle.
        </p>

        <div class="mt-20 relative">
            <!-- Desktop connector line (hidden on mobile) -->
            <div class="hidden lg:block absolute top-24 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-[#696CFF]/30 to-transparent"></div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 text-left relative">
                @foreach($workflow as $index => $step)
                    <div class="relative group">
                        <!-- Step number badge -->
                        <div class="absolute -top-12 left-6 w-12 h-12 bg-[#696CFF] rounded-2xl rotate-45 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <span class="absolute inset-0 flex items-center justify-center -rotate-45 text-white font-bold text-lg">
                                {{ $step['step'] }}
                            </span>
                        </div>
                        
                        <!-- Content card -->
                        <div class="bg-gray-50 p-8 rounded-2xl border border-gray-100 hover:border-[#696CFF] hover:shadow-xl transition-all duration-300 group-hover:-translate-y-2">
                            <h4 class="text-xl font-bold text-[#696CFF] mb-3 mt-2">
                                {{ $step['title'] }}
                            </h4>
                            <p class="text-gray-600 leading-relaxed">{{ $step['desc'] }}</p>
                            
                            <!-- Optional arrow for visual flow (except last) -->
                            @if(!$loop->last)
                                <div class="hidden lg:block absolute -right-6 top-1/2 transform -translate-y-1/2 text-[#696CFF]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- ================= ABOUT ================= -->
<section id="about" class="py-24 bg-gradient-to-br from-gray-900 to-gray-800 text-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <!-- Left Content -->
            <div data-aos="fade-right" class="space-y-6">
                <span class="inline-block bg-[#696CFF]/20 text-[#696CFF] px-4 py-1.5 rounded-full text-sm font-semibold tracking-wide uppercase">
                    About the System
                </span>
                <h2 class="text-4xl md:text-5xl font-bold leading-tight">
                    Advancing Institutional
                    <span class="text-[#696CFF]">Quality Assurance</span>
                </h2>
                <div class="w-24 h-1 bg-[#696CFF] rounded-full"></div>
                
                <p class="text-lg text-gray-300 leading-relaxed">
                    The Web-Based Accreditation Document Management System
                    strengthens academic governance at Palompon Institute of Technology
                    by digitizing accreditation workflows and centralizing
                    institutional documentation.
                </p>
                
                <p class="text-lg text-gray-300 leading-relaxed">
                    Through structured task force designation, secure document management,
                    and systematic evaluation processes, the platform ensures
                    transparency, accountability, and continuous improvement.
                </p>

                <!-- Stats or key metrics -->
                <div class="grid grid-cols-3 gap-6 pt-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-[#696CFF]">100%</div>
                        <div class="text-sm text-gray-400">Digital Workflow</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-[#696CFF]">24/7</div>
                        <div class="text-sm text-gray-400">System Access</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-[#696CFF]">5+</div>
                        <div class="text-sm text-gray-400">User Roles</div>
                    </div>
                </div>
            </div>

            <!-- Right Visual Card -->
            <div data-aos="fade-left" class="relative">
                <!-- Decorative elements -->
                <div class="absolute -top-4 -right-4 w-32 h-32 bg-[#696CFF]/20 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-blue-500/20 rounded-full blur-2xl"></div>
                
                <div class="relative bg-gray-800/50 backdrop-blur-sm shadow-2xl rounded-3xl p-10 border border-gray-700">
                    <h3 class="text-2xl font-bold text-white mb-6 flex items-center">
                        <svg class="w-8 h-8 text-[#696CFF] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Institutional Impact
                    </h3>
                    
                    <ul class="space-y-4 text-gray-300">
                        <li class="flex items-start group hover:text-[#696CFF] transition-colors">
                            <svg class="w-5 h-5 text-[#696CFF] mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Centralized accreditation documentation</span>
                        </li>
                        <li class="flex items-start group hover:text-[#696CFF] transition-colors">
                            <svg class="w-5 h-5 text-[#696CFF] mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Structured internal evaluation process</span>
                        </li>
                        <li class="flex items-start group hover:text-[#696CFF] transition-colors">
                            <svg class="w-5 h-5 text-[#696CFF] mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Enhanced administrative oversight</span>
                        </li>
                        <li class="flex items-start group hover:text-[#696CFF] transition-colors">
                            <svg class="w-5 h-5 text-[#696CFF] mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Compliance with accreditation standards</span>
                        </li>
                    </ul>

                    <!-- Testimonial or quote -->
                    <div class="mt-8 pt-6 border-t border-gray-700">
                        <p class="text-sm text-gray-400 italic">
                            "Streamlining accreditation processes for institutional excellence"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="bg-gray-900 text-gray-300">
    <!-- Main footer -->
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12">
            <!-- System Info -->
            <div class="space-y-4">
                <h3 class="text-white font-bold text-lg flex items-center">
                    <span class="w-8 h-8 bg-[#696CFF] rounded-lg flex items-center justify-center mr-3">
                        <span class="text-white font-bold text-sm">PIT</span>
                    </span>
                    WADMS
                </h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    A centralized digital platform supporting accreditation
                    preparedness, structured documentation, and institutional
                    quality assurance processes.
                </p>
                <div class="flex space-x-4 pt-2">
                    <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-[#696CFF] transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-[#696CFF] transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.937 4.937 0 004.604 3.417 9.868 9.868 0 01-6.102 2.104c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 0021.82-12.272c0-.213 0-.425-.015-.636.96-.695 1.795-1.56 2.455-2.55z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-[#696CFF] transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451c.979 0 1.771-.773 1.771-1.729V1.729C24 .774 23.203 0 22.225 0z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Institution Info -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-lg">Institution</h4>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#696CFF] mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="text-gray-400">Palompon Institute of Technology</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-[#696CFF] mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-gray-400">Evangelista Street, Palompon, Leyte</span>
                    </li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-lg">Quick Links</h4>
                <ul class="space-y-3">
                    <li><a href="#about" class="text-gray-400 hover:text-[#696CFF] transition-colors flex items-center group">
                        <span class="w-0 group-hover:w-2 h-0.5 bg-[#696CFF] mr-0 group-hover:mr-2 transition-all"></span>
                        About the System
                    </a></li>
                    <li><a href="#core-capabilities" class="text-gray-400 hover:text-[#696CFF] transition-colors flex items-center group">
                        <span class="w-0 group-hover:w-2 h-0.5 bg-[#696CFF] mr-0 group-hover:mr-2 transition-all"></span>
                        Capabilities
                    </a></li>
                    <li><a href="#users" class="text-gray-400 hover:text-[#696CFF] transition-colors flex items-center group">
                        <span class="w-0 group-hover:w-2 h-0.5 bg-[#696CFF] mr-0 group-hover:mr-2 transition-all"></span>
                        System Users
                    </a></li>
                    <li><a href="#workflow" class="text-gray-400 hover:text-[#696CFF] transition-colors flex items-center group">
                        <span class="w-0 group-hover:w-2 h-0.5 bg-[#696CFF] mr-0 group-hover:mr-2 transition-all"></span>
                        Workflow
                    </a></li>
                    <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-[#696CFF] transition-colors flex items-center group">
                        <span class="w-0 group-hover:w-2 h-0.5 bg-[#696CFF] mr-0 group-hover:mr-2 transition-all"></span>
                        Login
                    </a></li>
                </ul>
            </div>

            <!-- Help / Support -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-lg">Help & Support</h4>
                <div class="bg-gray-800/50 rounded-xl p-5 border border-gray-700">
                    <p class="text-gray-400 text-sm leading-relaxed mb-3">
                        Experiencing issues or found a bug?
                    </p>
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=casinillojoefrancis@gmail.com&su=Issue%20Report&body=Please%20describe%20the%20problem"
                       target="_blank"
                       class="inline-flex items-center bg-[#696CFF] hover:bg-[#5F61E6] text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Message the Developer
                    </a>
                    <p class="mt-3 text-xs text-gray-500">
                        Please provide details and screenshots for faster support.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-6 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                <p>© {{ date('Y') }} Palompon Institute of Technology. All Rights Reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-[#696CFF] transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-[#696CFF] transition-colors">Terms of Service</a>
                    <a href="#" class="hover:text-[#696CFF] transition-colors">Contact</a>
                </div>
            </div>
        </div>
    </div>
</footer>

</body>
</html>