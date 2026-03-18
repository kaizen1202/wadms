<!DOCTYPE html>

<html lang="en"
      class="light-style layout-menu-fixed layout-compact"
      dir="ltr"
      data-theme="theme-default"
      data-assets-path="{{ asset('assets/') }}"
      data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no,
                   minimum-scale=1.0, maximum-scale=1.0" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title></title>

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    @vite('resources/css/accreditation.css')
    @vite('resources/css/global-search.css')

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
        .swal2-container { z-index: 99999 !important; }
        .swal2-backdrop-show { background: rgba(0,0,0,0.6) !important; }

        #layout-menu .menu-item.active > .menu-link { background-color: rgba(0,0,0,0.2); }
        #layout-menu .menu-item:hover > .menu-link  { background-color: rgba(0,0,0,0.1); }
        #layout-menu .menu-sub .menu-item > .menu-link { padding-left: 2rem; }
        #layout-menu .menu-item .badge { font-size: 0.7rem; }
    </style>
</head>

<body>

<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container" id="vue-app">
        <div class="layout-page">

            <!-- Navbar -->
            <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                 id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)" @click.prevent="toggleSidebar">
                        <i class="bx bx-menu bx-sm"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                    <div class="navbar-nav align-items-center">
                        <div class="nav-item d-flex align-items-center fw-semibold" style="font-size:0.85rem; letter-spacing:0.3px;">
                            WEB-BASED ACCREDITATION DOCUMENT MANAGEMENT SYSTEM
                        </div>
                    </div>

                    <div class="navbar-nav align-items-center ms-auto">
                        <div class="nav-item d-flex align-items-center">
                            <button type="button"
                                class="btn btn-primary d-flex align-items-center gap-2 px-3"
                                style="border-radius:8px; min-width:220px; justify-content:space-between;"
                                @click="$refs.globalSearch.open()">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bx-search"></i>
                                    <span style="font-size:0.875rem;">Search...</span>
                                </div>
                                <kbd style="font-size:0.65rem; padding:2px 7px; border-radius:4px;
                                            background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); color:#fff;">
                                    Ctrl K
                                </kbd>
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Content wrapper -->
            <div class="content-wrapper">
                @yield('contents')
                <div class="content-backdrop fade"></div>
            </div>
        </div>

        <!-- Global Search -->
        <global-search ref="globalSearch" :user-role="'{{ $user->currentRole->name }}'"></global-search>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
    @include('admin.layouts.sidebar')
</div>

<!-- Core JS -->
<script src="{{ asset('assets/js/vue.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/js/axios.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
<script async defer src="{{ asset('assets/js/buttons.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>
<script src="{{ asset('assets/js/alpine.js') }}" defer></script>

<script>
Vue.component('global-search', {
    props: {
        userRole: { type: String, required: true }
    },
    data() {
        return {
            query: '',
            results: [],
            loading: false,
            modalInstance: null,
            searchTimeout: null,
            _searchShortcut: null,
        };
    },
    computed: {
        placeholderText() {
            switch (this.userRole) {
                @php use App\Enums\UserType; @endphp
                case '{{ UserType::ADMIN->value }}':
                    return 'Search assessors, accreditors, programs, areas, parameters...';
                case '{{ UserType::DEAN->value }}':
                    return 'Search task forces, programs, areas, parameters...';
                case '{{ UserType::TASK_FORCE->value }}':
                case '{{ UserType::INTERNAL_ASSESSOR->value }}':
                    return 'Search your assigned programs, areas, parameters...';
                case '{{ UserType::ACCREDITOR->value }}':
                    return 'Search programs, areas, parameters, sub-parameters...';
                default:
                    return 'Type to search...';
            }
        },
        groupedResults() {
            const order  = ['user', 'accreditation', 'program', 'area', 'parameter', 'sub_parameter', 'document', 'evaluation'];
            const labels = {
                user: 'People', accreditation: 'Accreditations',
                program: 'Programs', area: 'Areas',
                parameter: 'Parameters', sub_parameter: 'Sub-Parameters',
                document: 'Documents', evaluation: 'Evaluations'
            };
            const groups = {};
            this.results.forEach(item => {
                if (!groups[item.type]) groups[item.type] = [];
                groups[item.type].push(item);
            });
            return order
                .filter(t => groups[t]?.length)
                .map(t => ({ type: t, label: labels[t] || t, items: groups[t] }));
        }
    },
    mounted() {
        this.modalInstance = new bootstrap.Modal(this.$refs.modal);

        this._searchShortcut = (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.open();
            }
        };

        window.addEventListener('keydown', this._searchShortcut);
    },
    beforeDestroy() {
        window.removeEventListener('keydown', this._searchShortcut);
    },
    methods: {
        open() {
            this.query = '';
            this.results = [];
            this.modalInstance.show();
            this.$nextTick(() => this.$refs.input.focus());
        },
        close() {
            this.modalInstance.hide();
        },
        clearQuery() {
            this.query = '';
            this.results = [];
            this.$refs.input.focus();
        },
        debouncedSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout);
            if (this.query.length < 1) { this.results = []; return; }
            this.searchTimeout = setTimeout(() => this.performSearch(), 300);
        },
        performSearch() {
            this.loading = true;
            axios.get('{{ route('global.search') }}', { params: { q: this.query } })
                .then(r  => { this.results = r.data; })
                .catch(() => { this.results = [];})
                .finally(() => { this.loading = false; });
        },
        highlight(text) {
            if (!text || !this.query) return text;
            const esc = this.query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return String(text).replace(new RegExp(`(${esc})`, 'gi'), '<mark>$1</mark>');
        },
        iconColor(type) {
            const map = {
                user: 'text-primary', accreditation: 'text-warning',
                program: 'text-success', area: 'text-info',
                parameter: 'text-danger', sub_parameter: 'text-secondary',
                document: 'text-primary',
                evaluation: 'text-success',
            };
            return map[type] || 'text-primary';
        },
        iconBg(type) {
            const map = {
                user: '#eef2ff', accreditation: '#fffbea',
                program: '#edfaf3', area: '#e8f7fb',
                parameter: '#fdeef0', sub_parameter: '#f4f4f4',
                document: '#f3f0ff',
                evaluation: '#edfaf3',
            };
            return map[type] || '#eef0f8';
        }
    },
    template: `
        <div class="modal fade" id="globalSearchModal" ref="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" style="margin-top: 40px; max-width: 1100px;">
                <div class="modal-content">

                    <!-- ── Input Row ── -->
                    <div class="gs-input-row">
                        <i v-if="!loading" class="bx bx-search gs-search-icon"></i>
                        <div v-else class="spinner-border spinner-border-sm text-muted gs-search-icon"
                             style="width:1.4rem; height:1.4rem; flex-shrink:0; margin-right:1rem;"></div>

                        <input type="text"
                            ref="input"
                            :placeholder="placeholderText"
                            v-model="query"
                            @input="debouncedSearch"
                            @keydown.esc="close"
                            autocomplete="off">

                        <span v-if="query.length > 0" class="gs-clear-btn" @click="clearQuery" title="Clear">
                            <i class="bx bx-x"></i>
                        </span>
                        <span v-else class="gs-esc-kbd">Esc</span>
                    </div>

                    <!-- ── Body ── -->
                    <div style="max-height: 620px; overflow-y: auto;">

                        <!-- Loading -->
                        <div v-if="loading" class="gs-state">
                            <div class="spinner-border text-primary" style="width:2.2rem; height:2.2rem;"></div>
                            <p>Searching...</p>
                        </div>

                        <!-- Empty -->
                        <div v-else-if="query.length === 0" class="gs-state">
                            <i class="bx bx-search-alt"></i>
                            <p>Start typing to search</p>
                            <small>Search users, programs, areas, parameters and more</small>
                        </div>

                        <!-- No results -->
                        <div v-else-if="results.length === 0" class="gs-state">
                            <i class="bx bx-folder-open"></i>
                            <p>No results for <strong>"@{{ query }}"</strong></p>
                            <small>Try different keywords or check your spelling</small>
                        </div>

                        <!-- Grouped Results -->
                        <template v-else>
                            <div v-for="group in groupedResults" :key="group.type">

                                <!-- Group label -->
                                <div class="gs-type-label">
                                    @{{ group.label }}
                                    <span class="ms-1" style="font-weight:400; opacity:0.7;">(@{{ group.items.length }})</span>
                                </div>

                                <a v-for="item in group.items"
                                    :key="item.id"
                                    :href="item.url"
                                    class="gs-result">

                                    <!-- Icon box -->
                                    <div class="gs-icon-box" :style="{ background: iconBg(item.type) }">
                                        <i :class="'bx ' + item.icon + ' ' + iconColor(item.type)"></i>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-grow-1" style="min-width:0;">

                                        <!-- Title + Badge -->
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="gs-title" v-html="highlight(item.title)"></span>
                                            <span v-if="item.badge"
                                                :class="'badge bg-label-' + item.badge_color"
                                                style="font-size:0.7rem; padding:3px 9px; white-space:nowrap;">
                                                @{{ item.badge }}
                                            </span>
                                        </div>

                                        <!-- Subtitle -->
                                        <div class="gs-subtitle" v-html="highlight(item.subtitle)"></div>

                                        <!-- ── USER meta ── -->
                                        <template v-if="item.type === 'user'">
                                            <div v-if="item.meta.areas_count > 0" class="gs-meta text-truncate">
                                                <i class="bx bx-layer bx-xs"></i>
                                                @{{ item.meta.areas_count }} area(s): @{{ item.meta.areas.map(a => a.name).join(', ') }}
                                            </div>
                                            <div v-if="item.meta.status" class="gs-meta">
                                                <i class="bx bx-circle bx-xs"></i>
                                                @{{ item.meta.status }}
                                                <span v-if="item.meta.created_at"> · Joined @{{ item.meta.created_at }}</span>
                                            </div>
                                        </template>

                                        <!-- ── ACCREDITATION meta ── -->
                                        <template v-else-if="item.type === 'accreditation'">
                                            <div class="gs-meta">
                                                <i class="bx bx-buildings bx-xs"></i>
                                                @{{ item.meta.body?.name ?? 'No Body' }}
                                                <span v-if="item.meta.date"> · @{{ item.meta.date }}</span>
                                                <span v-if="item.meta.visit_type"> · @{{ item.meta.visit_type }}</span>
                                            </div>
                                            <div v-if="item.meta.levels_count > 0" class="gs-meta text-truncate">
                                                <i class="bx bx-bar-chart-alt-2 bx-xs"></i>
                                                @{{ item.meta.levels_count }} level(s): @{{ item.meta.levels.map(l => l.name).join(', ') }}
                                                · @{{ item.meta.programs_count }} program(s)
                                            </div>
                                        </template>

                                        <!-- ── PROGRAM meta ── -->
                                        <template v-else-if="item.type === 'program'">
                                            <div class="gs-meta">
                                                <i class="bx bx-certification bx-xs"></i>
                                                @{{ item.meta.accreditation.name ?? 'Unknown' }}
                                                <span v-if="item.meta.accreditation.year"> · @{{ item.meta.accreditation.year }}</span>
                                                <span v-if="item.meta.accreditation.body"> · @{{ item.meta.accreditation.body }}</span>
                                            </div>
                                            <div class="gs-meta text-truncate">
                                                <i class="bx bx-layer bx-xs"></i>
                                                @{{ item.meta.areas_count }} area(s)
                                                <span v-if="item.meta.areas.length > 0">: @{{ item.meta.areas.map(a => a.name).join(', ') }}</span>
                                            </div>
                                        </template>

                                        <!-- ── AREA meta ── -->
                                        <template v-else-if="item.type === 'area'">
                                            <div class="gs-meta">
                                                <i class="bx bx-certification bx-xs"></i>
                                                @{{ item.meta.accreditation.name ?? 'Unknown' }}
                                                <span v-if="item.meta.accreditation.year"> · @{{ item.meta.accreditation.year }}</span>
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-book bx-xs"></i>
                                                @{{ item.meta.program.name ?? 'Unknown' }}
                                                <span v-if="item.meta.level?.name">
                                                    · <i class="bx bx-bar-chart-alt-2 bx-xs"></i> @{{ item.meta.level.name }}
                                                </span>
                                            </div>
                                            <div v-if="item.meta.description" class="gs-meta text-truncate">
                                                <i class="bx bx-info-circle bx-xs"></i> @{{ item.meta.description }}
                                            </div>
                                        </template>

                                        <!-- ── PARAMETER meta ── -->
                                        <template v-else-if="item.type === 'parameter'">
                                            <div class="gs-meta">
                                                <i class="bx bx-layer bx-xs"></i> @{{ item.meta.area.name ?? 'Unknown' }}
                                                · <i class="bx bx-book bx-xs"></i> @{{ item.meta.program.name ?? 'Unknown' }}
                                                · <i class="bx bx-bar-chart-alt-2 bx-xs"></i> @{{ item.meta.level.name ?? 'Unknown' }}
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-certification bx-xs"></i>
                                                @{{ item.meta.accreditation.name ?? 'Unknown' }}
                                                <span v-if="item.meta.accreditation.year"> · @{{ item.meta.accreditation.year }}</span>
                                            </div>
                                            <div v-if="item.meta.sub_parameters_count > 0" class="gs-meta text-truncate">
                                                <i class="bx bx-subdirectory-right bx-xs"></i>
                                                @{{ item.meta.sub_parameters_count }} sub-parameter(s):
                                                @{{ item.meta.sub_parameters.map(s => s.name).join(', ') }}
                                            </div>
                                        </template>

                                        <!-- ── SUB-PARAMETER meta ── -->
                                        <template v-else-if="item.type === 'sub_parameter'">
                                            <div class="gs-meta">
                                                <i class="bx bx-list-ul bx-xs"></i> @{{ item.meta.parameter.name ?? 'Unknown' }}
                                                · <i class="bx bx-layer bx-xs"></i> @{{ item.meta.area.name ?? 'Unknown' }}
                                                · <i class="bx bx-book bx-xs"></i> @{{ item.meta.program.name ?? 'Unknown' }}
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-bar-chart-alt-2 bx-xs"></i> @{{ item.meta.level.name ?? 'Unknown' }}
                                                · <i class="bx bx-certification bx-xs"></i>
                                                @{{ item.meta.accreditation.name ?? 'Unknown' }}
                                                <span v-if="item.meta.accreditation.year"> · @{{ item.meta.accreditation.year }}</span>
                                            </div>
                                        </template>

                                        <!-- ── DOCUMENT meta ── -->
                                        <template v-else-if="item.type === 'document'">
                                            <div class="gs-meta">
                                                <i class="bx bx-list-ul bx-xs"></i> @{{ item.meta.parameter.name ?? 'Unknown' }}
                                                · <i class="bx bx-subdirectory-right bx-xs"></i> @{{ item.meta.sub_parameter.name ?? 'Unknown' }}
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-layer bx-xs"></i> @{{ item.meta.area.name ?? 'Unknown' }}
                                                · <i class="bx bx-book bx-xs"></i> @{{ item.meta.program.name ?? 'Unknown' }}
                                                · <i class="bx bx-bar-chart-alt-2 bx-xs"></i> @{{ item.meta.level.name ?? 'Unknown' }}
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-certification bx-xs"></i>
                                                @{{ item.meta.accreditation.name ?? 'Unknown' }}
                                                <span v-if="item.meta.accreditation.year"> · @{{ item.meta.accreditation.year }}</span>
                                                <span v-if="item.meta.accreditation.body"> · @{{ item.meta.accreditation.body }}</span>
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-user bx-xs"></i>
                                                @{{ item.meta.uploader.name ?? 'Unknown' }}
                                                <span v-if="item.meta.uploader.role"> · @{{ item.meta.uploader.role }}</span>
                                                <span v-if="item.meta.uploaded_at"> · @{{ item.meta.uploaded_at }}</span>
                                            </div>
                                        </template>

                                        <!-- ── EVALUATION meta ── -->
                                        <template v-else-if="item.type === 'evaluation'">
                                            <div class="gs-meta">
                                                <i class="bx bx-user bx-xs"></i> @{{ item.meta.evaluator.name ?? 'Unknown' }}
                                                <span v-if="item.meta.evaluator.role"> · @{{ item.meta.evaluator.role }}</span>
                                                <span v-if="item.meta.evaluated_at"> · @{{ item.meta.evaluated_at }}</span>
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-layer bx-xs"></i> @{{ item.meta.area.name ?? 'Unknown' }}
                                                · <i class="bx bx-book bx-xs"></i> @{{ item.meta.program.name ?? 'Unknown' }}
                                                · <i class="bx bx-bar-chart-alt-2 bx-xs"></i> @{{ item.meta.level.name ?? 'Unknown' }}
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-certification bx-xs"></i>
                                                @{{ item.meta.accreditation.name ?? 'Unknown' }}
                                                <span v-if="item.meta.accreditation.year"> · @{{ item.meta.accreditation.year }}</span>
                                                <span v-if="item.meta.accreditation.body"> · @{{ item.meta.accreditation.body }}</span>
                                            </div>
                                            <div class="gs-meta">
                                                <i class="bx bx-star bx-xs"></i> @{{ item.meta.ratings_count }} rating(s)
                                                <span v-if="item.meta.is_final">
                                                    · <i class="bx bx-check-circle bx-xs text-success"></i> Finalized
                                                </span>
                                                <span v-else-if="item.meta.is_updated">
                                                    · <i class="bx bx-edit bx-xs text-warning"></i> Updated
                                                </span>
                                                <span v-else>
                                                    · <i class="bx bx-time bx-xs text-secondary"></i> Draft
                                                </span>
                                            </div>
                                        </template>

                                        <!-- ── FALLBACK ── -->
                                        <template v-else>
                                            <div v-if="item.subtitle" class="gs-meta text-truncate">@{{ item.subtitle }}</div>
                                        </template>

                                    </div>

                                    <!-- Chevron -->
                                    <i class="bx bx-chevron-right gs-chevron"></i>

                                </a>
                            </div>
                        </template>
                    </div>

                    <!-- ── Footer ── -->
                    <div class="gs-footer">
                        <div class="d-flex gap-3">
                            <span><kbd>Esc</kbd> close</span>
                        </div>
                        <span v-if="results.length > 0">@{{ results.length }} result(s)</span>
                    </div>

                </div>
            </div>
        </div>
    `
});

@stack('vue-components')

new Vue({
    el: '#vue-app',
    methods: {
        toggleSidebar() {
            document.body.classList.toggle('layout-menu-expanded');
        }
    }
});
</script>

<script>
function showToast(message, type = 'success') {
    const container = document.getElementById('globalToastContainer');
    const toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-white border-0 fade';
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    const cls = { success:'bg-success', error:'bg-danger', warning:'bg-warning', info:'bg-info' };
    toastEl.classList.add(cls[type] || 'bg-primary');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>`;

    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

$(document).on('click', '.btn-terminate', function () {
    const button = $(this);
    const url = button.data('url');

    Swal.fire({
        title: "Are you sure?",
        text: "This user will be terminated and cannot access the system!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, terminate user"
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: url,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            beforeSend: () => button.prop('disabled', true),
            success(res) {
                if ($.fn.DataTable.isDataTable('#taskforce-table')) {
                    $('#taskforce-table').DataTable().ajax.reload(null, false);
                }
                Swal.fire({
                    icon: 'success', title: 'Terminated!',
                    text: res.message ?? 'User terminated successfully',
                    timer: 2000, showConfirmButton: false
                });
            },
            error: () => Swal.fire('Error', 'Failed to terminate user.', 'error'),
            complete: () => button.prop('disabled', false)
        });
    });
});
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => showToast("{{ session('success') }}", 'success'));
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => showToast("{{ session('error') }}", 'error'));
</script>
@endif

@stack('scripts')

<div class="toast-container position-fixed top-0 end-0 p-3" id="globalToastContainer" style="z-index:2000;"></div>

</body>
</html>