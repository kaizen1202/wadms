@extends('admin.layouts.master')

@section('contents')
<div class="container-xxl container-p-y">

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
                :is-draft="{{ $isDraft ? 'true' : 'false' }}"
                submit-url="{{ route('accreditation-evaluations.store') }}"
                draft-url="{{ route('accreditation-evaluations.draft') }}"
                csrf-token="{{ csrf_token() }}"
            ></area-evaluation>
        </div>
    </div>

</div>
@endsection

@push('vue-components')
<script>
Vue.component('area-evaluation', {
    props: {
        accredInfoId:          Number,
        levelId:               Number,
        programId:             Number,
        programAreaId:         Number,
        parameters:            Array,
        initialEvaluations:    Object,
        initialRecommendation: String,
        readonly:              Boolean,
        isSubmitted:           Boolean,
        isFinalized:           Boolean,
        isDraft:               Boolean,
        submitUrl:             String,
        draftUrl:              String,
        csrfToken:             String,
    },

    data() {
        return {
            evaluations:      {},
            recommendation:   '',
            isLocked:         this.readonly,
            saving:           false,
            draftSaving:      false,
            lastSaved:        null,
            loaded:           false,
            autoSaveInterval: null,
        };
    },

    computed: {

        // Count only rateable items:
        // - sub-params without children → key: "sub_{id}"
        // - sub-sub-params              → key: "ss_{id}"
        totalRateable() {
            let count = 0;
            this.parameters.forEach(p => {
                p.sub_parameters.forEach(sub => {
                    count += sub.has_sub_sub ? sub.sub_sub_parameters.length : 1;
                });
            });
            return count;
        },

        isComplete() {
            return Object.keys(this.evaluations).length === this.totalRateable;
        },

        totals() {
            let available = 0, inadequate = 0, notAvailable = 0, notApplicable = 0;
            Object.values(this.evaluations).forEach(item => {
                if      (item.status === 'available')    available    += item.score;
                else if (item.status === 'inadequate')   inadequate   += item.score;
                else if (item.status === 'not_available') notAvailable++;
                else if (item.status === 'not_applicable') notApplicable++;
            });
            return { available, inadequate, notAvailable, notApplicable };
        },

        // Exclude not_applicable from both numerator and denominator
        mean() {
            let totalScore    = 0;
            let notApplicable = 0;

            // Count how many are marked not_applicable
            Object.values(this.evaluations).forEach(item => {
                if (item.status === 'not_applicable') {
                    notApplicable++;
                } else if (item.status === 'available' || item.status === 'inadequate') {
                    totalScore += item.score;
                }
                // not_available contributes 0 to score, already handled
            });

            const denominator = this.totalRateable - notApplicable;

            return denominator > 0
                ? (totalScore / denominator).toFixed(2)
                : '0.00';
        },
    },

    mounted() {
        this.loadInitial();
        this.loaded = true;
        this.autoSaveInterval = setInterval(() => {
            if (!this.isLocked) this.saveDraft();
        }, 30000);
    },

    beforeDestroy() {
        clearInterval(this.autoSaveInterval);
    },

    methods: {

        // ─── Load & normalize keys from server ───────────────────────────────
        // Server sends keys already prefixed ("sub_5", "ss_12") if controller
        // was updated, or plain numeric ("5") for legacy data → we keep both.
        loadInitial() {
            this.evaluations    = { ...this.initialEvaluations };
            this.recommendation = this.initialRecommendation;
            this.isLocked       = this.isFinalized ? true
                                : this.isSubmitted  ? true
                                : this.readonly;
        },

        unlockForm() {
            if (!this.isSubmitted) return;
            this.isLocked = false;
            this.$nextTick(() => {
                document.querySelector('.table')?.scrollIntoView({ behavior: 'smooth' });
            });
        },

        // ─── Unified select — namespaced by type ('sub' | 'ss') ─────────────
        select(id, status, score, type = 'sub') {
            if (this.isLocked) return;
            const key     = type + '_' + String(id);
            const current = this.evaluations[key];

            // Toggle off: clicking an already-selected radio deselects it
            if (current && current.status === status &&
                (status === 'not_available' || status === 'not_applicable')) {
                Vue.delete(this.evaluations, key);
                return;
            }

            if (status === 'not_applicable') {
                Vue.set(this.evaluations, key, { status, score: null });
            } else if (status === 'not_available') {
                Vue.set(this.evaluations, key, { status, score: 0 });
            } else if (score !== '') {
                Vue.set(this.evaluations, key, { status, score: parseInt(score) });
            } else {
                // Dropdown reset to '—' → clear entry
                Vue.delete(this.evaluations, key);
            }
        },

        // ─── Namespaced getters ───────────────────────────────────────────────
        getStatus(id, type = 'sub') {
            return this.evaluations[type + '_' + id]?.status || null;
        },
        getScore(id, type = 'sub') {
            return this.evaluations[type + '_' + id]?.score ?? '';
        },

        clearAll() {
            if (this.isLocked) return;
            if (!confirm('Clear all evaluations?')) return;
            this.evaluations    = {};
            this.recommendation = '';
        },

        // Sub-mean excludes not_applicable from denominator too
        getSubMean(sub) {
            if (!sub.has_sub_sub) return '—';
            let total         = 0;
            let notApplicable = 0;
            const total_count = sub.sub_sub_parameters.length;

            sub.sub_sub_parameters.forEach(ss => {
                const item = this.evaluations['ss_' + ss.id];
                if (!item) return;
                if (item.status === 'not_applicable') {
                    notApplicable++;
                } else if (item.status === 'available' || item.status === 'inadequate') {
                    total += item.score;
                }
            });

            const denominator = total_count - notApplicable;
            return denominator > 0 ? (total / denominator).toFixed(2) : '0.00';
        },

        async saveDraft() {
            if (this.isLocked || this.draftSaving) return;
            this.draftSaving = true;
            try {
                await fetch(this.draftUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({
                        accred_info_id:  this.accredInfoId,
                        level_id:        this.levelId,
                        program_id:      this.programId,
                        program_area_id: this.programAreaId,
                        evaluations:     this.evaluations,
                        recommendation:  this.recommendation,
                    }),
                });
                this.lastSaved = new Date().toLocaleTimeString();
            } catch (err) {
                console.warn('Auto-save failed:', err);
            } finally {
                this.draftSaving = false;
            }
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
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify({
                        accred_info_id:  this.accredInfoId,
                        level_id:        this.levelId,
                        program_id:      this.programId,
                        program_area_id: this.programAreaId,
                        evaluations:     this.evaluations,
                        recommendation:  this.recommendation,
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Submission failed');
                window.location.href = data.redirect;
            } catch (err) {
                alert(err.message);
            } finally {
                this.saving = false;
            }
        },
    },

    template: `
        <div>

            {{-- Loading --}}
            <div v-if="!loaded" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            {{-- Draft indicator --}}
            <div v-if="!isLocked" class="d-flex justify-content-end mb-2">
                <small class="text-muted fst-italic">
                    <span v-if="draftSaving">
                        <i class="bx bx-loader-alt bx-spin me-1"></i>Saving draft...
                    </span>
                    <span v-else-if="lastSaved">
                        <i class="bx bx-check me-1 text-success"></i>Draft saved at @{{ lastSaved }}
                    </span>
                    <span v-else-if="isDraft">
                        <i class="bx bx-info-circle me-1 text-warning"></i>Draft in progress
                    </span>
                </small>
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

                            {{-- Parameter header row --}}
                            <tr class="table-secondary">
                                <td colspan="6" class="fw-semibold">@{{ parameter.name }}</td>
                            </tr>

                            <template v-for="sub in parameter.sub_parameters">

                                {{-- ── Sub-parameter row ── --}}
                                <tr>
                                    <td style="padding-left:24px; font-size:13px; font-weight:600;">
                                        @{{ sub.name }}
                                    </td>

                                    {{-- Has sub-sub: show mean, no inputs here --}}
                                    <template v-if="sub.has_sub_sub">
                                        <td colspan="4" class="text-center text-muted small fst-italic">
                                            Rated via sub-items below
                                            — Mean: <strong>@{{ getSubMean(sub) }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted small">—</span>
                                        </td>
                                    </template>

                                    {{-- No sub-sub: normal rating inputs, type='sub' --}}
                                    <template v-else>
                                        <td class="text-center">
                                            <select class="form-select form-select-sm"
                                                    :disabled="isLocked"
                                                    :value="getStatus(sub.id, 'sub') === 'available' ? getScore(sub.id, 'sub') : ''"
                                                    @change="select(sub.id, 'available', $event.target.value, 'sub')">
                                                <option value="">—</option>
                                                <option value="5">5</option>
                                                <option value="4">4</option>
                                                <option value="3">3</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <select class="form-select form-select-sm"
                                                    :disabled="isLocked"
                                                    :value="getStatus(sub.id, 'sub') === 'inadequate' ? getScore(sub.id, 'sub') : ''"
                                                    @change="select(sub.id, 'inadequate', $event.target.value, 'sub')">
                                                <option value="">—</option>
                                                <option value="2">2</option>
                                                <option value="1">1</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio"
                                                   :disabled="isLocked"
                                                   :name="'eval_sub_' + sub.id"
                                                   value="not_available"
                                                   :checked="getStatus(sub.id, 'sub') === 'not_available'"
                                                   @click="select(sub.id, 'not_available', 0, 'sub')">
                                        </td>
                                        <td class="text-center">
                                            <input type="radio"
                                                   :disabled="isLocked"
                                                   :name="'eval_sub_' + sub.id"
                                                   value="not_applicable"
                                                   :checked="getStatus(sub.id, 'sub') === 'not_applicable'"
                                                   @click="select(sub.id, 'not_applicable', null, 'sub')">
                                        </td>
                                        <td class="text-center">
                                            <a v-if="sub.uploads_count > 0"
                                               :href="sub.uploads_url"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bx bxs-file-pdf me-1"></i>@{{ sub.uploads_count }}
                                            </a>
                                            <span v-else class="text-muted small">—</span>
                                        </td>
                                    </template>
                                </tr>

                                {{-- ── Sub-sub-parameter rows, type='ss' ── --}}
                                <template v-if="sub.has_sub_sub">
                                    <tr v-for="subSub in sub.sub_sub_parameters" :key="'ss_' + subSub.id">
                                        <td style="padding-left:48px; font-size:12px; color:#666;">
                                            <i class="bx bx-subdirectory-right me-1"></i>@{{ subSub.name }}
                                        </td>
                                        <td class="text-center">
                                            <select class="form-select form-select-sm"
                                                    :disabled="isLocked"
                                                    :value="getStatus(subSub.id, 'ss') === 'available' ? getScore(subSub.id, 'ss') : ''"
                                                    @change="select(subSub.id, 'available', $event.target.value, 'ss')">
                                                <option value="">—</option>
                                                <option value="5">5</option>
                                                <option value="4">4</option>
                                                <option value="3">3</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <select class="form-select form-select-sm"
                                                    :disabled="isLocked"
                                                    :value="getStatus(subSub.id, 'ss') === 'inadequate' ? getScore(subSub.id, 'ss') : ''"
                                                    @change="select(subSub.id, 'inadequate', $event.target.value, 'ss')">
                                                <option value="">—</option>
                                                <option value="2">2</option>
                                                <option value="1">1</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <input type="radio"
                                                   :disabled="isLocked"
                                                   :name="'eval_ss_' + subSub.id"
                                                   value="not_available"
                                                   :checked="getStatus(subSub.id, 'ss') === 'not_available'"
                                                   @click="select(subSub.id, 'not_available', 0, 'ss')">
                                        </td>
                                        <td class="text-center">
                                            <input type="radio"
                                                   :disabled="isLocked"
                                                   :name="'eval_ss_' + subSub.id"
                                                   value="not_applicable"
                                                   :checked="getStatus(subSub.id, 'ss') === 'not_applicable'"
                                                   @click="select(subSub.id, 'not_applicable', null, 'ss')">
                                        </td>
                                        <td class="text-center">
                                            <a v-if="subSub.uploads_count > 0"
                                               :href="subSub.uploads_url"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bx bxs-file-pdf me-1"></i>@{{ subSub.uploads_count }}
                                            </a>
                                            <span v-else class="text-muted small">—</span>
                                        </td>
                                    </tr>
                                </template>

                            </template>
                        </template>
                    </tbody>
                    <tfoot class="fw-semibold table-light">
                        <tr>
                            <td>Total</td>
                            <td class="text-center">@{{ totals.available }}</td>
                            <td class="text-center">@{{ totals.inadequate }}</td>
                            <td class="text-center">0</td>
                            <td class="text-center">N/A</td>
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
                            class="btn btn-outline-secondary"
                            :disabled="isLocked || draftSaving"
                            @click="saveDraft()">
                        <i class="bx bx-save me-1"></i>
                        @{{ draftSaving ? 'Saving...' : 'Save Draft' }}
                    </button>

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
</script>
@endpush