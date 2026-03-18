@extends('admin.layouts.master')

@section('contents')
<div id="app" class="container-xxl container-p-y">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ url()->previous() }}" class="text-muted">Areas</a>
                    </li>
                    <li class="breadcrumb-item active">Area Evaluation</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">{{ $programArea->area->area_name }}</h4>
            <small class="text-muted">Program Area Evaluation</small>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
    </div>

    {{-- ===== ASSIGNED USERS ===== --}}
    <div class="card mb-4" style="border-radius: 10px; border-color: #e2e8f0;">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="bx bx-user-check me-2 text-primary"></i>Internal Assessors
                </h6>
            </div>

            @if ($programArea->users->count() > 0)
                <div class="tf-grid">
                    @foreach ($programArea->users as $user)
                        @php $isYou = $user->id === auth()->id(); @endphp
                        <div class="tf-card {{ $isYou ? 'is-you' : '' }}">
                            <x-initials-avatar :user="$user" size="sm" shape="circle" />
                            <div class="tf-info">
                                <div class="tf-name" title="{{ $user->name }}">
                                    {{ $user->name }}
                                </div>
                                @if ($isYou)
                                    <div class="tf-you-label">You</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="bx bx-user-x"></i>
                    No Internal Assessors assigned yet.
                </div>
            @endif

        </div>
    </div>

    {{-- ===== AREA EVALUATION ===== --}}
    <div class="card mb-4" style="border-radius: 10px; border-color: #e2e8f0;">
        <div class="card-header border-0 pb-0">
            <h6 class="fw-bold mb-0">
                <i class="bx bx-clipboard me-2 text-primary"></i>Area Evaluation
            </h6>
        </div>
        <div class="card-body">
            <area-evaluation
                :accred-info-id="{{ $infoId }}"
                :level-id="{{ $levelId }}"
                :program-id="{{ $programId }}"
                :program-area-id="{{ $programAreaId }}"
                :parameters="{{ json_encode($parametersArray) }}"
                :initial-evaluations="{{ json_encode($initialEvaluations) }}"
                :initial-recommendation="{{ json_encode($initialRecommendation) }}"
                :readonly="{{ $readonly ? 'true' : 'false' }}"
                :is-submitted="{{ $isSubmitted ? 'true' : 'false' }}"
                :is-finalized="{{ $isFinalized ? 'true' : 'false' }}"
                storage-key="area-eval-{{ auth()->id() }}-{{ $programAreaId }}-{{ $levelId }}-{{ $programId }}"
                submit-url="{{ route('accreditation-evaluations.store') }}"
                csrf-token="{{ csrf_token() }}"
            ></area-evaluation>
        </div>
    </div>

</div>
@endsection

@push('vue-components')
    {{-- Vue and Component --}}
<script src="{{ asset('assets/js/vue.js') }}"></script>
<script>
Vue.component('area-evaluation', {
    props: {
        accredInfoId: Number,
        levelId: Number,
        programId: Number,
        programAreaId: Number,
        parameters: Array,
        initialEvaluations: Object,
        initialRecommendation: String,
        readonly: Boolean,
        isSubmitted: Boolean,
        isFinalized: Boolean,
        storageKey: String,
        submitUrl: String,
        csrfToken: String
    },
    data() {
        return {
            evaluations: {},
            recommendation: '',
            isLocked: this.readonly,
            saving: false,
            loaded: false
        };
    },
    computed: {
        totalSubparameters() {
            return this.parameters.reduce((acc, p) => acc + p.sub_parameters.length, 0);
        },
        isComplete() {
            return Object.keys(this.evaluations).length === this.totalSubparameters;
        },
        totals() {
            let available = 0, inadequate = 0, notAvailable = 0, notApplicable = 0;
            Object.values(this.evaluations).forEach(item => {
                if (item.status === 'available')      available  += item.score;
                else if (item.status === 'inadequate') inadequate += item.score;
                else if (item.status === 'not_available')  notAvailable++;
                else if (item.status === 'not_applicable') notApplicable++;
            });
            return { available, inadequate, notAvailable, notApplicable };
        },
        mean() {
            let totalScore = 0;
            Object.values(this.evaluations).forEach(item => {
                if (item.status === 'available' || item.status === 'inadequate') {
                    totalScore += item.score;
                }
            });
            return this.totalSubparameters > 0
                ? (totalScore / this.totalSubparameters).toFixed(2)
                : '0.00';
        }
    },
    watch: {
        evaluations: { handler() { this.saveToLocalStorage(); }, deep: true },
        recommendation()  { this.saveToLocalStorage(); }
    },
    mounted() {
        this.loadData();
        window.addEventListener('beforeunload', this.saveScrollPosition);
    },
    beforeDestroy() {
        window.removeEventListener('beforeunload', this.saveScrollPosition);
    },
    methods: {
        loadData() {
        const saved = localStorage.getItem(this.storageKey);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                this.evaluations    = data.evaluations  || {};
                this.recommendation = data.recommendation || '';
                // Don't restore isLocked from storage — always derive from props
                this.isLocked = this.isFinalized ? true : (this.isSubmitted ? true : this.readonly);
            } catch (e) {
                console.warn('Failed to restore from localStorage', e);
                this.loadInitial();
            }
        } else {
            this.loadInitial();
        }

        this.$nextTick(() => {
            const savedScroll = localStorage.getItem(this.storageKey + '_scroll');
            if (savedScroll) window.scrollTo(0, parseInt(savedScroll));
            this.loaded = true;
        });
    },

        loadInitial() {
            this.evaluations    = { ...this.initialEvaluations };
            this.recommendation = this.initialRecommendation;
            this.isLocked       = this.readonly;
        },
        unlockForm() {
            if (!this.isSubmitted) return;
            this.isLocked = false;
            this.saveToLocalStorage();
            this.$nextTick(() => {
                document.querySelector('.table')?.scrollIntoView({ behavior: 'smooth' });
            });
        },
        saveToLocalStorage() {
            localStorage.setItem(this.storageKey, JSON.stringify({
                evaluations:    this.evaluations,
                recommendation: this.recommendation,
                isLocked:       this.isLocked
            }));
        },
        saveScrollPosition() {
            localStorage.setItem(this.storageKey + '_scroll', window.scrollY);
        },
        select(subId, status, score) {
            if (this.isLocked) return;
            if (status === 'not_applicable') {
                Vue.set(this.evaluations, subId, { status, score: null });
            } else if (status === 'not_available') {
                Vue.set(this.evaluations, subId, { status, score: 0 });
            } else if (score !== '') {
                Vue.set(this.evaluations, subId, { status, score: parseInt(score) });
            }
        },
        getStatus(id) { return this.evaluations[id]?.status || null; },
        getScore(id)  { return this.evaluations[id]?.score  || '';   },
        clearAll() {
            if (this.isLocked) return;
            if (!confirm('Clear all evaluations?')) return;
            this.evaluations    = {};
            this.recommendation = '';
            localStorage.removeItem(this.storageKey);
        },
        async submitEvaluation() {
            if (this.isLocked || !this.isComplete) return;
            this.saving = true;
            try {
                const res = await fetch(this.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        accred_info_id:  this.accredInfoId,
                        level_id:        this.levelId,
                        program_id:      this.programId,
                        program_area_id: this.programAreaId,
                        evaluations:     this.evaluations,
                        recommendation:  this.recommendation
                    })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Submission failed');
                localStorage.removeItem(this.storageKey);
                localStorage.removeItem(this.storageKey + '_scroll');
                window.location.href = data.redirect;
            } catch (err) {
                alert(err.message);
            } finally {
                this.saving = false;
            }
        }
    },
    template: `
        <div>
            <div v-if="!loaded" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            {{-- State alerts --}}
            <div v-if="isFinalized" class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-lock fs-5"></i>
                <span>You already finalized your evaluation. Editing is locked.</span>
            </div>
            <div v-else-if="isSubmitted && isLocked"
                 class="alert alert-warning d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-lock fs-5"></i>
                    <span>You already evaluated this area. Click <strong>Edit Evaluation</strong> to make changes.</span>
                </div>
                <button class="btn btn-sm btn-warning flex-shrink-0" @click="unlockForm">
                    <i class="bx bx-edit me-1"></i> Edit Evaluation
                </button>
            </div>

            {{-- Evaluation Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width:30%; vertical-align:middle;" class="fw-bold">Checklist Item</th>
                            <th style="width:18%; vertical-align:top;">
                                <div class="fw-bold">Available</div>
                                <div class="small text-start mt-1">
                                    <div>5 – Available and very adequate</div>
                                    <div>4 – Available and adequate</div>
                                    <div>3 – Available and fairly adequate</div>
                                </div>
                            </th>
                            <th style="width:18%; vertical-align:top;">
                                <div class="fw-bold">Available but Inadequate</div>
                                <div class="small text-start mt-1">
                                    <div>2 – Available but inadequate</div>
                                    <div>1 – Available but very inadequate</div>
                                </div>
                            </th>
                            <th style="width:14%; vertical-align:top;">
                                <div class="fw-bold">Not Available</div>
                                <div class="small mt-1">0 – No supporting document</div>
                            </th>
                            <th style="width:14%; vertical-align:top;">
                                <div class="fw-bold">Not Applicable</div>
                                <div class="small mt-1">N/A – Excluded from computation</div>
                            </th>
                            <th style="width:10%; vertical-align:middle;" class="fw-bold">Documents</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="parameter in parameters">
                            <tr class="table-secondary">
                                <td colspan="6" class="fw-semibold">@{{ parameter.name }}</td>
                            </tr>
                            <tr v-for="sub in parameter.sub_parameters" :key="sub.id">
                                <td style="padding-left:24px; font-size:13px;">@{{ sub.name }}</td>
                                <td class="text-center">
                                    <select class="form-select form-select-sm"
                                            :disabled="isLocked"
                                            :value="getStatus(sub.id) === 'available' ? getScore(sub.id) : ''"
                                            @change="select(sub.id, 'available', $event.target.value)">
                                        <option value="">—</option>
                                        <option value="5">5</option>
                                        <option value="4">4</option>
                                        <option value="3">3</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <select class="form-select form-select-sm"
                                            :disabled="isLocked"
                                            :value="getStatus(sub.id) === 'inadequate' ? getScore(sub.id) : ''"
                                            @change="select(sub.id, 'inadequate', $event.target.value)">
                                        <option value="">—</option>
                                        <option value="2">2</option>
                                        <option value="1">1</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="radio"
                                           :disabled="isLocked"
                                           :name="'eval_' + sub.id"
                                           :checked="getStatus(sub.id) === 'not_available'"
                                           @change="select(sub.id, 'not_available', 0)">
                                </td>
                                <td class="text-center">
                                    <input type="radio"
                                           :disabled="isLocked"
                                           :name="'eval_' + sub.id"
                                           :checked="getStatus(sub.id) === 'not_applicable'"
                                           @change="select(sub.id, 'not_applicable', null)">
                                </td>
                                <td class="text-center">
                                    <a v-if="sub.uploads_count > 0"
                                       :href="sub.uploads_url"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bx bxs-file-pdf me-1"></i>@{{ sub.uploads_count }}
                                    </a>
                                    <span v-else class="text-muted small">—</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="fw-semibold table-light">
                        <tr>
                            <td>Total</td>
                            <td class="text-center">@{{ totals.available }}</td>
                            <td class="text-center">@{{ totals.inadequate }}</td>
                            <td class="text-center">@{{ totals.notAvailable }}</td>
                            <td class="text-center">@{{ totals.notApplicable }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Area Mean</td>
                            <td colspan="5" class="text-center fw-bold" style="font-size:1.1rem;">
                                @{{ mean }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Recommendation --}}
            <div class="mt-4">
                <label class="form-label fw-semibold">Recommendations</label>
                <textarea class="form-control"
                          rows="4"
                          :disabled="isLocked"
                          v-model="recommendation"></textarea>
            </div>

            {{-- Actions --}}
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <div style="font-size:13px;" v-if="!isComplete && !isLocked">
                    <i class="bx bx-info-circle text-warning me-1"></i>
                    <span class="text-warning">Please evaluate all checklist items before submitting.</span>
                </div>
                <div v-else></div>

                <div class="d-flex gap-2">
                    <button type="button"
                            class="btn btn-outline-danger"
                            :disabled="isLocked"
                            @click="clearAll()">
                        <i class="bx bx-trash me-1"></i> Clear All
                    </button>
                    <button type="button"
                            class="btn btn-primary"
                            :disabled="isLocked || !isComplete || saving"
                            @click="submitEvaluation()">
                        <i class="bx bx-send me-1"></i>
                        @{{ saving ? 'Submitting...' : 'Submit Evaluation' }}
                    </button>
                </div>
            </div>
        </div>
    `
});

new Vue({ el: '#vue-app' });
</script
@endpush