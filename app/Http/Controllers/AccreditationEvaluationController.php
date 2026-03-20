<?php

namespace App\Http\Controllers;

use App\Enums\EvaluationStatus;
use App\Models\AccreditationEvaluation;
use App\Models\ADMIN\SubSubparameter;
use App\Models\AreaRecommendation;
use App\Models\ADMIN\Parameter;
use App\Models\ADMIN\Area;
use App\Models\ADMIN\ProgramAreaMapping;
use App\Models\ADMIN\AccreditationAssignment;
use App\Models\RatingOptions;
use App\Models\Role;
use App\Models\SubparameterRating;
use App\Enums\UserType;
use App\Models\SubSubParameterRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccreditationEvaluationController extends Controller
{
    /* =========================================================
     | INDEX – LIST ALL EVALUATIONS
     ========================================================= */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->currentRole->name === UserType::ADMIN->value;
        $isDean = $user->currentRole->name === UserType::DEAN->value;
        $isTaskForce = $user->currentRole->name === UserType::TASK_FORCE->value;
        $isInternalAssessor = $user->currentRole->name === UserType::INTERNAL_ASSESSOR->value;
        $isAccreditor = $user->currentRole->name === UserType::ACCREDITOR->value;

        $query = AccreditationEvaluation::with([
            'accreditationInfo',
            'level',
            'program',
            'evaluator',
            'areaRecommendations.area',
            'subparameterRatings.ratingOption',
            'subparameterRatings.subparameter.parameter',
        ]);

        $user = auth()->user();

        if ($user->currentRole->name === UserType::TASK_FORCE->value) {
            $query->whereHas('areaRecommendations', function ($q) use ($user) {
                $q->whereHas('area', function ($areaQ) use ($user) {
                    $areaQ->whereIn('id', function ($sub) use ($user) {
                        $sub->select('area_id')
                            ->from('accreditation_assignments')
                            ->where('user_id', $user->id);
                    });
                });
            });
        }

        if ($user->currentRole->name === UserType::INTERNAL_ASSESSOR->value) {
            $query->where('evaluated_by', $user->id);
        }

        $internalAssessorRoleId = Role::where('name', UserType::INTERNAL_ASSESSOR->value)->value('id');
        $accreditorRoleId = Role::where('name', UserType::ACCREDITOR->value)->value('id');

        if (in_array($user->currentRole->name, [
            UserType::ACCREDITOR->value,
            UserType::ADMIN->value,
            UserType::DEAN->value
        ])) {
            $query->where('status', EvaluationStatus::FINALIZED)
                ->where('role_id', $internalAssessorRoleId);
        }

        $evaluations = $query->get()->groupBy(fn ($e) =>
            $e->accred_info_id.'-'.$e->level_id.'-'.$e->program_id
        );

        $grandMeans  = [];
        $signatories = [];

        foreach ($evaluations as $key => $group) {

            $grandMeans[$key]  = [];
            $signatories[$key] = [];

            foreach ([
                'internal'   => $internalAssessorRoleId,
                'accreditor' => $accreditorRoleId,
            ] as $type => $roleId) {

                $areaMeans = [];

                $filteredEvaluations = $group->filter(
                    fn ($e) => $e->role_id === $roleId
                );

                foreach ($filteredEvaluations as $evaluation) {

                    if (in_array($user->currentRole->name, [
                        UserType::ACCREDITOR->value,
                        UserType::ADMIN->value,
                        UserType::DEAN->value
                    ]) && $evaluation->status !== EvaluationStatus::FINALIZED) {
                        continue;
                    }

                    $ratingsByArea = $evaluation->subparameterRatings
                        ->groupBy(fn ($r) => $r->subparameter->parameter->area_id);

                    foreach ($ratingsByArea as $areaId => $ratings) {

                        $totalScore      = 0;
                        $applicableCount = 0;

                        foreach ($ratings as $rating) {
                            $label = $rating->ratingOption->label;

                            if (in_array($label, ['Available', 'Available but Inadequate'])) {
                                $totalScore += $rating->score;
                                $applicableCount++;
                            } elseif ($label === 'Not Available') {
                                $applicableCount++;
                            }
                        }

                        if ($applicableCount > 0) {
                            $areaMeans[$areaId][] = round($totalScore / $applicableCount, 2);
                        }
                    }
                }

                $finalAreaMeans = collect($areaMeans)
                    ->map(fn ($means) => round(collect($means)->avg(), 2));

                $allAreaIds = Area::pluck('id');
                $areas      = Area::whereIn('id', $allAreaIds)->orderBy('id')->get();

                $finalAreaMeansMap = [];
                foreach ($areas as $area) {
                    $finalAreaMeansMap[$area->id] = $finalAreaMeans[$area->id] ?? 0;
                }

                $grandMeans[$key][$type] = [
                    'areaModels' => $areas,
                    'areas'      => collect($finalAreaMeansMap),
                    'total'      => collect($finalAreaMeansMap)->sum(),
                    'grand'      => $areas->count()
                                    ? collect($finalAreaMeansMap)->sum() / $areas->count()
                                    : 0,
                ];

                $signatories[$key][$type] = $filteredEvaluations
                    ->pluck('evaluator.name')
                    ->unique()
                    ->values();
            }
        }

        return view('admin.accreditors.evaluations', compact(
            'evaluations',
            'grandMeans',
            'signatories',
            'isAdmin',
            'isDean',
            'isTaskForce',
            'isInternalAssessor',
            'isAccreditor',
            'internalAssessorRoleId',
            'accreditorRoleId'
        ));
    }

    /* =========================================================
     | SHOW AREA EVALUATION FORM (GET)
     ========================================================= */
    public function evaluateArea(
        int $infoId,
        int $levelId,
        int $programId,
        int $programAreaId
    ) {
        $user = auth()->user();

        $programArea = Area::with(['area', 'users'])->findOrFail($programAreaId);

        $currentUserEvaluation = AccreditationEvaluation::where([
            'accred_info_id' => $infoId,
            'level_id'       => $levelId,
            'program_id'     => $programId,
            'area_id'        => $programAreaId,
            'evaluated_by'   => $user->id,
        ])->first();

        $isEvaluated = false;

        if ($currentUserEvaluation) {
            $isEvaluated = $currentUserEvaluation->evaluated_by !== $user->id;
        }

        $parameters = Parameter::with([
            'sub_parameters.uploads' => function ($q) use ($infoId, $levelId, $programId, $programAreaId) {
                $q->where('accred_info_id', $infoId)
                  ->where('level_id', $levelId)
                  ->where('program_id', $programId)
                  ->where('program_area_id', $programAreaId);
            }
        ])
        ->where('area_id', $programArea->area_id)
        ->get();

        return view('admin.accreditors.internal-accessor-parameter', [
            'programArea'   => $programArea,
            'parameters'    => $parameters,
            'infoId'        => $infoId,
            'levelId'       => $levelId,
            'programId'     => $programId,
            'programAreaId' => $programAreaId,
            'isEvaluated'   => $isEvaluated,
            'evaluatedBy'   => $currentUserEvaluation?->evaluator?->name,
        ]);
    }

    /* =========================================================
     | STORE – SAVE AREA EVALUATION (POST)
     ========================================================= */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'accred_info_id'  => ['required', 'exists:accreditation_infos,id'],
            'level_id'        => ['required', 'exists:accreditation_levels,id'],
            'program_id'      => ['required', 'exists:programs,id'],
            'program_area_id' => ['required', 'exists:program_area_mappings,id'],
            'evaluations'     => ['required', 'array'],
            'recommendation'  => ['nullable', 'string'],
        ]);

        $programArea  = ProgramAreaMapping::findOrFail($validated['program_area_id']);
        $areaId       = $programArea->area_id;
        $user         = auth()->user();
        $isIA         = $user->currentRole->name === UserType::INTERNAL_ASSESSOR->value;
        $isAccreditor = $user->currentRole->name === UserType::ACCREDITOR->value;

        if ($isIA) {
            $alreadyEvaluated = AccreditationEvaluation::query()
                ->where('accred_info_id', $validated['accred_info_id'])
                ->where('level_id', $validated['level_id'])
                ->where('program_id', $validated['program_id'])
                ->where('area_id', $areaId)
                ->where('evaluated_by', $user->id)
                ->exists();
        }

        if ($isAccreditor) {
            $alreadyEvaluated = AreaRecommendation::query()
                ->where('area_id', $areaId)
                ->whereHas('evaluation', function ($q) use ($validated, $user) {
                    $q->where('accred_info_id', $validated['accred_info_id'])
                      ->where('level_id', $validated['level_id'])
                      ->where('program_id', $validated['program_id'])
                      ->where('evaluated_by', $user->id);
                })
                ->exists();

            if ($alreadyEvaluated) {
                return response()->json([
                    'message' => 'You have already evaluated this area.'
                ], 409);
            }
        }

        $evaluation = DB::transaction(function () use ($validated, $areaId) {

            $evaluation = AccreditationEvaluation::updateOrCreate(
                [
                    'accred_info_id' => $validated['accred_info_id'],
                    'level_id'       => $validated['level_id'],
                    'program_id'     => $validated['program_id'],
                    'area_id'        => $areaId,
                    'evaluated_by'   => auth()->id(),
                ],
                [
                    'role_id' => auth()->user()->currentRole->id,
                    'status'  => EvaluationStatus::SUBMITTED,
                ]
            );

            foreach ($validated['evaluations'] as $key => $data) {

                if (!$data || !isset($data['status'])) {
                    continue;
                }

                // ✅ Strip prefix: "sub_5" → type=sub, id=5 | "ss_12" → type=ss, id=12
                [$type, $id] = explode('_', $key, 2);
                $id             = (int) $id;
                $ratingOptionId = $this->mapStatusToRatingOption($data['status']);
                $score          = $data['score'] ?? 0;

                if ($type === 'ss') {
                    SubSubParameterRating::updateOrCreate(
                        [
                            'evaluation_id'       => $evaluation->id,
                            'sub_subparameter_id' => $id,
                        ],
                        [
                            'rating_option_id' => $ratingOptionId,
                            'score'            => $score,
                        ]
                    );
                } else {
                    // type === 'sub'
                    SubparameterRating::updateOrCreate(
                        [
                            'evaluation_id'   => $evaluation->id,
                            'subparameter_id' => $id,
                        ],
                        [
                            'rating_option_id' => $ratingOptionId,
                            'score'            => $score,
                        ]
                    );
                }
            }

            AreaRecommendation::updateOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'area_id'       => $areaId,
                ],
                [
                    'recommendation' => $validated['recommendation'],
                ]
            );

            $evaluation->touch();

            return $evaluation;
        });

        return response()->json([
            'message'  => 'Evaluation saved successfully.',
            'redirect' => route(
                'program.areas.evaluations.summary',
                [
                    'evaluation' => $evaluation->id,
                    'area'       => $areaId,
                ]
            )
        ]);
    }

    /* =========================================================
     | SAVE DRAFT
     ========================================================= */
    public function saveDraft(Request $request)
    {
        $validated = $request->validate([
            'accred_info_id'  => ['required', 'exists:accreditation_infos,id'],
            'level_id'        => ['required', 'exists:accreditation_levels,id'],
            'program_id'      => ['required', 'exists:programs,id'],
            'program_area_id' => ['required', 'exists:program_area_mappings,id'],
            'evaluations'     => ['nullable', 'array'],
            'recommendation'  => ['nullable', 'string'],
        ]);

        $programArea = ProgramAreaMapping::findOrFail($validated['program_area_id']);
        $areaId      = $programArea->area_id;
        $user        = auth()->user();

        DB::transaction(function () use ($validated, $areaId, $user) {

            // ✅ Use firstOrCreate so existing submitted/finalized records
            //    are never overwritten with DRAFT status
            $evaluation = AccreditationEvaluation::firstOrCreate(
                [
                    'accred_info_id' => $validated['accred_info_id'],
                    'level_id'       => $validated['level_id'],
                    'program_id'     => $validated['program_id'],
                    'area_id'        => $areaId,
                    'evaluated_by'   => $user->id,
                ],
                [
                    'role_id' => $user->currentRole->id,
                    'status'  => EvaluationStatus::DRAFT,
                ]
            );

            // Only update status to DRAFT if not already at a further stage
            if (!in_array($evaluation->status, [
                EvaluationStatus::SUBMITTED,
                EvaluationStatus::UPDATED,
                EvaluationStatus::FINALIZED,
            ])) {
                $evaluation->update(['status' => EvaluationStatus::DRAFT]);
            }

            foreach ($validated['evaluations'] ?? [] as $key => $data) {

                if (!$data || !isset($data['status'])) continue;

                // ✅ Strip prefix: "sub_5" → type=sub, id=5 | "ss_12" → type=ss, id=12
                [$type, $id] = explode('_', $key, 2);
                $id             = (int) $id;
                $ratingOptionId = $this->mapStatusToRatingOption($data['status']);
                $score          = $data['score'] ?? 0;

                if ($type === 'ss') {
                    SubSubParameterRating::updateOrCreate(
                        [
                            'evaluation_id'       => $evaluation->id,
                            'sub_subparameter_id' => $id,
                        ],
                        [
                            'rating_option_id' => $ratingOptionId,
                            'score'            => $score,
                        ]
                    );
                } else {
                    // type === 'sub'
                    SubparameterRating::updateOrCreate(
                        [
                            'evaluation_id'   => $evaluation->id,
                            'subparameter_id' => $id,
                        ],
                        [
                            'rating_option_id' => $ratingOptionId,
                            'score'            => $score,
                        ]
                    );
                }
            }

            if (!empty($validated['recommendation'])) {
                AreaRecommendation::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'area_id'       => $areaId,
                    ],
                    [
                        'recommendation' => $validated['recommendation'],
                    ]
                );
            }
        });

        return response()->json(['message' => 'Draft saved.']);
    }

    /* =========================================================
     | SHOW SINGLE EVALUATION
     ========================================================= */
    public function show(
        AccreditationEvaluation $evaluation,
        Area $area
    ) {
        $user = auth()->user();

        $isAdmin      = $user->currentRole->name === UserType::ADMIN->value;
        $isDean       = $user->currentRole->name === UserType::DEAN->value;
        $isTaskForce  = $user->currentRole->name === UserType::TASK_FORCE->value;
        $isIA         = $user->currentRole->name === UserType::INTERNAL_ASSESSOR->value;
        $isAccreditor = $user->currentRole->name === UserType::ACCREDITOR->value;

        $internalAssessorRole   = Role::where('name', UserType::INTERNAL_ASSESSOR->value)->first();
        $internalAssessorRoleId = $internalAssessorRole?->id;

        if ($user->currentRole->name === UserType::ACCREDITOR->value) {
            $isOwnEvaluation              = $evaluation->evaluated_by === $user->id;
            $isInternalAssessorEvaluation = $evaluation->role_id === $internalAssessorRoleId;

            if (!$isOwnEvaluation && !$isInternalAssessorEvaluation) {
                abort(403, 'You are not allowed to view this evaluation.');
            }
        }

        if ($user->currentRole->name === UserType::TASK_FORCE->value) {
            $assigned = AccreditationAssignment::where('user_id', $user->id)
                ->where('area_id', $area->id)
                ->exists();

            if (!$assigned) {
                abort(403, 'You are not assigned to this area.');
            }
        }

        if (!($isAdmin || $isDean || $isTaskForce || $isIA || $isAccreditor)) {
            abort(403);
        }

        $evaluation->load([
            'accreditationInfo',
            'level',
            'program',
            'evaluator',
            'subparameterRatings.ratingOption',
            'subSubParameterRatings.ratingOption',
            'areaRecommendations.area'
        ]);

        $areaRecommendation = $evaluation->areaRecommendations()
            ->where('area_id', $area->id)
            ->firstOrFail();

        $areaEvaluator = $evaluation->evaluator;

        $parameters = Parameter::with(['sub_parameters.subSubParameters'])
            ->where('area_id', $area->id)
            ->get();

        $subparameterIds = $parameters
            ->flatMap(fn ($p) => $p->sub_parameters->pluck('id'))
            ->values();

        $subSubparameterIds = $parameters
            ->flatMap(fn ($p) => $p->sub_parameters)
            ->flatMap(fn ($sub) => $sub->subSubParameters->pluck('id'))
            ->values();

        $ratings = $evaluation->subparameterRatings
            ->whereIn('subparameter_id', $subparameterIds)
            ->keyBy('subparameter_id');

        $subSubRatings = $evaluation->subSubParameterRatings
            ->whereIn('sub_subparameter_id', $subSubparameterIds)
            ->keyBy('sub_subparameter_id');

        $totals = [
            'available'      => 0,
            'inadequate'     => 0,
            'not_available'  => 0,
            'not_applicable' => 'N/A',
        ];

        $totalScore      = 0;
        $applicableCount = 0;

        foreach ($parameters->flatMap->sub_parameters as $sub) {
            if ($sub->subSubParameters->isNotEmpty()) continue;

            $rating = $ratings[$sub->id] ?? null;
            if (!$rating) continue;

            $label = $rating->ratingOption->label;

            if (in_array($label, ['Available', 'Available but Inadequate'])) {
                $totalScore += $rating->score;
                $applicableCount++;
                if ($label === 'Available') {
                    $totals['available'] += $rating->score;
                } else {
                    $totals['inadequate'] += $rating->score;
                }
            } elseif ($label === 'Not Available') {
                $applicableCount++;
            }
        }

        foreach ($subSubRatings as $rating) {
            $label = $rating->ratingOption->label;

            if (in_array($label, ['Available', 'Available but Inadequate'])) {
                $totalScore += $rating->score;
                $applicableCount++;
                if ($label === 'Available') {
                    $totals['available'] += $rating->score;
                } else {
                    $totals['inadequate'] += $rating->score;
                }
            } elseif ($label === 'Not Available') {
                $applicableCount++;
            }
        }

        $mean = $applicableCount
            ? number_format($totalScore / $applicableCount, 2)
            : '0.00';

        $internalAssessorRoleId = Role::where('name', UserType::INTERNAL_ASSESSOR->value)->value('id');

        $query = AccreditationEvaluation::query()
            ->where('accred_info_id', $evaluation->accred_info_id)
            ->where('level_id', $evaluation->level_id)
            ->where('program_id', $evaluation->program_id);

        if ($user->currentRole->name === UserType::INTERNAL_ASSESSOR->value) {
            $query->where('evaluated_by', $user->id);
        }

        if (in_array($user->currentRole->name, [
            UserType::ACCREDITOR->value,
            UserType::ADMIN->value,
            UserType::DEAN->value,
        ])) {
            $query->where('status', EvaluationStatus::FINALIZED)
                ->where('role_id', $internalAssessorRoleId);
        }

        if ($user->currentRole->name === UserType::TASK_FORCE->value) {
            $query->whereHas('areaRecommendations', function ($q) use ($user) {
                $q->whereHas('area', function ($areaQ) use ($user) {
                    $areaQ->whereIn('id', function ($sub) use ($user) {
                        $sub->select('area_id')
                            ->from('accreditation_assignments')
                            ->where('user_id', $user->id);
                    });
                });
            });
        }

        $prevEvaluation = (clone $query)
            ->where('id', '<', $evaluation->id)
            ->orderBy('id', 'desc')
            ->first();

        $nextEvaluation = (clone $query)
            ->where('id', '>', $evaluation->id)
            ->orderBy('id', 'asc')
            ->first();

        return view('admin.accreditors.show-evaluation', compact(
            'evaluation',
            'area',
            'parameters',
            'ratings',
            'subSubRatings',
            'totals',
            'mean',
            'prevEvaluation',
            'nextEvaluation',
            'isAccreditor',
            'areaEvaluator'
        ));
    }

    /* =========================================================
     | EDIT
     ========================================================= */
    public function edit(AccreditationEvaluation $accreditationEvaluation)
    {
        return view(
            'accreditation_evaluations.edit',
            compact('accreditationEvaluation')
        );
    }

    /* =========================================================
     | UPDATE
     ========================================================= */
    public function update(
        Request $request,
        AccreditationEvaluation $accreditationEvaluation
    ) {
        $validated = $request->validate([
            'level_id'   => ['required', 'exists:accreditation_levels,id'],
            'program_id' => ['required', 'exists:programs,id'],
        ]);

        $validated['status'] = EvaluationStatus::UPDATED;

        $accreditationEvaluation->update($validated);

        return redirect()
            ->route('accreditation-evaluations.show', $accreditationEvaluation)
            ->with('success', 'Evaluation updated successfully.');
    }

    /* =========================================================
     | MARK AS FINAL
     ========================================================= */
    public function markAsFinal(AccreditationEvaluation $evaluation)
    {
        $user = auth()->user();

        if (
            $user->currentRole->name !== UserType::INTERNAL_ASSESSOR->value ||
            $evaluation->evaluated_by !== $user->id
        ) {
            abort(403, 'You are not allowed to finalize this evaluation.');
        }

        if ($evaluation->status === EvaluationStatus::FINALIZED) {
            return back()->with('error', 'Evaluation is already finalized.');
        }

        $evaluation->update([
            'status' => EvaluationStatus::FINALIZED,
        ]);

        return redirect()
            ->route('program.areas.evaluations.summary', [
                'evaluation' => $evaluation->id,
                'area'       => $evaluation->area_id,
            ])
            ->with('success', 'Evaluation marked as Final.');
    }

    /* =========================================================
     | DELETE
     ========================================================= */
    public function destroy(AccreditationEvaluation $accreditationEvaluation)
    {
        $accreditationEvaluation->delete();

        return redirect()
            ->route('accreditation-evaluations.index')
            ->with('success', 'Evaluation deleted successfully.');
    }

    /* =========================================================
     | HELPER – MAP UI STATUS TO RATING OPTION ID
     ========================================================= */
    private function mapStatusToRatingOption(string $status): int
    {
        return match ($status) {
            'available'      => RatingOptions::where('label', 'Available')->value('id'),
            'inadequate'     => RatingOptions::where('label', 'Available but Inadequate')->value('id'),
            'not_available'  => RatingOptions::where('label', 'Not Available')->value('id'),
            'not_applicable' => RatingOptions::where('label', 'Not Applicable')->value('id'),
            default          => throw new \InvalidArgumentException("Unknown status: {$status}"),
        };
    }
}