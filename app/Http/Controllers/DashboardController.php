<?php

namespace App\Http\Controllers;

use App\Enums\AccreditationStatus;
use App\Enums\EvaluationStatus;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\AccreditationEvaluation;
use App\Models\ADMIN\AccreditationAssignment;
use App\Models\ADMIN\AccreditationDocuments;
use App\Models\ADMIN\AccreditationInfo;
use App\Models\ADMIN\InfoLevelProgramMapping;
use App\Models\Role;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $roleName = $user->currentRole?->name;

        $roleView = match ($roleName) {
            'ADMIN'              => 'admin',
            'DEAN'               => 'dean',
            'TASK FORCE'         => 'taskforce',
            'INTERNAL ASSESSOR'  => 'assessor',
            'ACCREDITOR'         => 'accreditor',
            default              => '',
        };

        $data = match($roleView) {
            'admin' => $this->adminDashboardData(),
            'dean'  => $this->deanDashboardData(),
            'taskforce' => $this->taskForceDashboardData(),
            'assessor'  => $this->assessorDashboardData(),
            'accreditor' => $this->accreditorDashboardData(),
            default => [],
        };

        return view('admin.dashboard.index', compact('roleView') + $data);
    }

    private function adminDashboardData(): array
    {
        $internalAssessorRoleId = Role::where('name', UserType::INTERNAL_ASSESSOR->value)->value('id');

        // ── STAT CARDS ──
        $assessorAccreditorRoleIds = Role::whereIn('name', [
            UserType::INTERNAL_ASSESSOR->value,
            UserType::ACCREDITOR->value,
        ])->pluck('id');

        $totalAssessorsAccreditors = User::whereHas('roles', function ($q) use ($assessorAccreditorRoleIds) {
            $q->whereIn('roles.id', $assessorAccreditorRoleIds);
        })->count();

        $ongoingCount   = AccreditationInfo::where('status', AccreditationStatus::ONGOING)->count();
        $completedCount = AccreditationInfo::where('status', AccreditationStatus::COMPLETED)->count();
        $programsCount  = InfoLevelProgramMapping::distinct('program_id')->count('program_id');
        $pendingAccounts    = User::where('status', UserStatus::PENDING->value)->count();
        $pendingEvaluations = AccreditationEvaluation::where('status', '!=', EvaluationStatus::FINALIZED->value)
            ->where('role_id', $internalAssessorRoleId)
            ->count();

        $finalizedAreas = AccreditationEvaluation::where('status', EvaluationStatus::FINALIZED->value)
            ->where('role_id', $internalAssessorRoleId)
            ->distinct('area_id')
            ->count('area_id');

        // ── ACCREDITATION OVERVIEW ──
        $ongoingAccreditation = AccreditationInfo::where('status', AccreditationStatus::ONGOING)
            ->with([
                'infoLevelProgramMappings.programAreas.area',
                'infoLevelProgramMappings.level',
            ])
            ->latest()
            ->first();

        $levelName = null;
        if ($ongoingAccreditation) {
            $rawLevel = $ongoingAccreditation->infoLevelProgramMappings->first()?->level?->level_name;

            $levelName = match(true) {
                str_contains(strtolower($rawLevel ?? ''), 'preliminary') => 'Preliminary Survey Visit (PSV)',
                str_contains(strtolower($rawLevel ?? ''), 'level i')     => 'Level I',
                str_contains(strtolower($rawLevel ?? ''), 'level ii')    => 'Level II',
                str_contains(strtolower($rawLevel ?? ''), 'level iii')   => 'Level III',
                str_contains(strtolower($rawLevel ?? ''), 'level iv')    => 'Level IV',
                default => $rawLevel ?? 'N/A',
            };
        }

        $overviewAreas      = collect();
        $totalAreas         = 0;
        $finalizedAreaCount = 0;

        if ($ongoingAccreditation) {
            $allProgramAreas = $ongoingAccreditation
                ->infoLevelProgramMappings
                ->flatMap(fn ($m) => $m->programAreas)
                ->unique('area_id');

            $totalAreas = $allProgramAreas->count();

            $overviewAreas = $allProgramAreas->map(function ($programArea) use ($ongoingAccreditation, $internalAssessorRoleId) {

                $finalizedEvaluations = AccreditationEvaluation::where([
                    'accred_info_id' => $ongoingAccreditation->id,
                    'area_id'        => $programArea->area_id,
                    'role_id'        => $internalAssessorRoleId,
                ])->where('status', EvaluationStatus::FINALIZED->value)->get();

                $status = $finalizedEvaluations->isNotEmpty() ? 'finalized' : 'pending';

                $mapping = $programArea->infoLevelProgramMapping;

                // ← get assigned internal assessors for this area
                $assignedAssessors = User::whereHas('roles', function ($q) use ($internalAssessorRoleId) {
                        $q->where('roles.id', $internalAssessorRoleId);
                    })
                    ->whereHas('assignments', function ($q) use ($programArea) {
                        $q->where('area_id', $programArea->area_id);
                    })
                    ->pluck('name');

                return [
                    'area_id'          => $programArea->area_id,
                    'area_name'        => $programArea->area->area_name ?? 'N/A',
                    'status'           => $status,
                    'assigned_count'   => $assignedAssessors->count(),
                    'assigned_names'   => $assignedAssessors->join(', '),
                    'evaluation'       => $finalizedEvaluations->sortByDesc('updated_at')->first(),
                    'info_id'          => $ongoingAccreditation->id,
                    'level_id'         => $mapping?->level_id,
                    'program_id'       => $mapping?->program_id,
                    'program_area_id'  => $programArea->id,
                ];
            });

            $finalizedAreaCount = $overviewAreas->where('status', 'finalized')->count();
        }

        // ── RECENT ACTIVITIES ──
        $recentEvaluations = AccreditationEvaluation::with(['evaluator', 'area'])
            ->where('role_id', $internalAssessorRoleId)
            ->whereIn('status', [EvaluationStatus::SUBMITTED->value, EvaluationStatus::FINALIZED->value])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn ($e) => [
                'icon'  => $e->status === EvaluationStatus::FINALIZED ? 'bx-check-shield' : 'bx-file',
                'color' => $e->status === EvaluationStatus::FINALIZED ? 'text-success'    : 'text-primary',
                'text'  => ($e->evaluator?->name ?? 'Someone') 
                            . ($e->status === EvaluationStatus::FINALIZED ? ' finalized ' : ' submitted ')
                            . 'evaluation for ' . ($e->area?->area_name ?? 'an area') . '.',
                'time'  => $e->updated_at->diffForHumans(),
            ]);

        return compact(
            'totalAssessorsAccreditors',
            'ongoingCount',
            'completedCount',
            'programsCount',
            'pendingAccounts',
            'pendingEvaluations',
            'finalizedAreas',
            'ongoingAccreditation',
            'levelName',
            'overviewAreas',
            'totalAreas',
            'finalizedAreaCount',
            'recentEvaluations',
        );
    }

    private function deanDashboardData(): array
    {
        $taskForceRoleId = Role::where('name', UserType::TASK_FORCE->value)->value('id');

        // ── STAT CARDS ──
        $totalTaskForces = User::whereHas('roles', function ($q) use ($taskForceRoleId) {
            $q->where('roles.id', $taskForceRoleId);
        })->count();

        $ongoingCount   = AccreditationInfo::where('status', AccreditationStatus::ONGOING)->count();
        $completedCount = AccreditationInfo::where('status', AccreditationStatus::COMPLETED)->count();
        $programsCount  = InfoLevelProgramMapping::distinct('program_id')->count('program_id');

        // ── ACCREDITATION OVERVIEW ──
        $ongoingAccreditation = AccreditationInfo::where('status', AccreditationStatus::ONGOING)
            ->with([
                'infoLevelProgramMappings.programAreas.area',
                'infoLevelProgramMappings.level',
            ])
            ->latest()
            ->first();

        $overviewAreas      = collect();
        $totalAreas         = 0;

        if ($ongoingAccreditation) {
            $allProgramAreas = $ongoingAccreditation
                ->infoLevelProgramMappings
                ->flatMap(fn ($m) => $m->programAreas)
                ->unique('area_id');

            $totalAreas = $allProgramAreas->count();

            $overviewAreas = $allProgramAreas->map(function ($programArea) use ($taskForceRoleId, $ongoingAccreditation) {
                $mapping = $programArea->infoLevelProgramMapping;

                $assignedTaskForces = User::whereHas('roles', function ($q) use ($taskForceRoleId) {
                        $q->where('roles.id', $taskForceRoleId);
                    })
                    ->whereHas('assignments', function ($q) use ($programArea) {
                        $q->where('area_id', $programArea->area_id);
                    })
                    ->pluck('name');

                return [
                    'area_id'        => $programArea->area_id,
                    'area_name'      => $programArea->area->area_name ?? 'N/A',
                    'assigned_count' => $assignedTaskForces->count(),
                    'assigned_names' => $assignedTaskForces->join(', '),
                    'info_id'        => $ongoingAccreditation->id,
                    'level_id'       => $mapping?->level_id,
                    'program_id'     => $mapping?->program_id,
                    'program_area_id' => $programArea->id,
                ];
            });

            $levelName = null;
            $rawLevel  = $ongoingAccreditation->infoLevelProgramMappings->first()?->level?->level_name;
            $levelName = match(true) {
                str_contains(strtolower($rawLevel ?? ''), 'preliminary') => 'Preliminary Survey Visit (PSV)',
                str_contains(strtolower($rawLevel ?? ''), 'level i')     => 'Level I',
                str_contains(strtolower($rawLevel ?? ''), 'level ii')    => 'Level II',
                str_contains(strtolower($rawLevel ?? ''), 'level iii')   => 'Level III',
                str_contains(strtolower($rawLevel ?? ''), 'level iv')    => 'Level IV',
                default => $rawLevel ?? 'N/A',
            };
        }

        // ── RECENT ACTIVITIES ──
        $recentEvaluations = AccreditationEvaluation::with(['evaluator', 'area'])
            ->whereHas('evaluator', function ($q) use ($taskForceRoleId) {
                $q->whereHas('roles', function ($q2) use ($taskForceRoleId) {
                    $q2->where('roles.id', $taskForceRoleId);
                });
            })
            ->whereIn('status', [EvaluationStatus::SUBMITTED->value, EvaluationStatus::FINALIZED->value])
            ->latest('updated_at')
            ->get()
            ->map(fn ($e) => [
                'icon'      => $e->status === EvaluationStatus::FINALIZED ? 'bx-check-shield' : 'bx-file',
                'color'     => $e->status === EvaluationStatus::FINALIZED ? 'text-success'    : 'text-primary',
                'text'      => ($e->evaluator?->name ?? 'Someone')
                                . ($e->status === EvaluationStatus::FINALIZED ? ' finalized ' : ' submitted ')
                                . 'evaluation for ' . ($e->area?->area_name ?? 'an area') . '.',
                'sort_date' => $e->updated_at,
                'time'      => $e->updated_at->diffForHumans(),
            ]);

        $recentUploads = AccreditationDocuments::with(['uploader', 'area'])
            ->whereHas('uploader', function ($q) use ($taskForceRoleId) {
                $q->whereHas('roles', function ($q2) use ($taskForceRoleId) {
                    $q2->where('roles.id', $taskForceRoleId);
                });
            })
            ->latest()
            ->get()
            ->map(fn ($d) => [
                'icon'      => 'bx-upload',
                'color'     => 'text-info',
                'text'      => ($d->uploader?->name ?? 'Someone')
                                . ' uploaded a document'
                                . ($d->area ? ' for ' . $d->area->area_name : '')
                                . ($d->file_name ? ' (' . $d->file_name . ')' : '') . '.',
                'sort_date' => $d->created_at,
                'time'      => $d->created_at->diffForHumans(),
            ]);

        $recentActivities = $recentEvaluations
            ->concat($recentUploads)
            ->sortByDesc('sort_date')
            ->values();

        return compact(
            'totalTaskForces',
            'ongoingCount',
            'completedCount',
            'programsCount',
            'ongoingAccreditation',
            'overviewAreas',
            'totalAreas',
            'levelName',
            'recentActivities',
        );
    }

    private function taskForceDashboardData(): array
    {
        $user = auth()->user();
        $internalAssessorRoleId = Role::where('name', UserType::INTERNAL_ASSESSOR->value)->value('id');

        // Get assigned area IDs for this task force
        $assignments = AccreditationAssignment::with(['area', 'accreditationInfo', 'level', 'program'])
            ->where('user_id', $user->id)
            ->get();

        $assignedAreaIds = $assignments->pluck('area_id')->unique()->values();

        // ── STAT CARDS ──
        $totalAssignedAreas = $assignedAreaIds->count();

        $finalizedEvaluations = AccreditationEvaluation::whereIn('area_id', $assignedAreaIds)
            ->where('role_id', $internalAssessorRoleId)
            ->where('status', EvaluationStatus::FINALIZED->value)
            ->count();

        $submittedEvaluations = AccreditationEvaluation::whereIn('area_id', $assignedAreaIds)
            ->where('role_id', $internalAssessorRoleId)
            ->where('status', EvaluationStatus::SUBMITTED->value)
            ->count();

        $totalDocuments = AccreditationDocuments::whereIn('area_id', $assignedAreaIds)
            ->count();

        // ── ASSIGNED AREAS OVERVIEW ──
        $assignedAreas = $assignments->map(function ($assignment) use ($internalAssessorRoleId) {
            $evaluations = AccreditationEvaluation::where([
                'area_id' => $assignment->area_id,
                'role_id' => $internalAssessorRoleId,
            ])->get();

            $finalized = $evaluations->where('status', EvaluationStatus::FINALIZED)->count();
            $total     = $evaluations->count();

            return [
                'area_id'        => $assignment->area_id,
                'area_name'      => $assignment->area?->area_name ?? 'N/A',
                'info_id'        => $assignment->accred_info_id,
                'info_title'     => $assignment->accreditationInfo?->title . ' ' . $assignment->accreditationInfo?->year,
                'level_id'       => $assignment->level_id,
                'level_name'     => $assignment->level?->level_name ?? 'N/A',
                'program_id'     => $assignment->program_id,
                'program_name'   => $assignment->program?->program_name ?? 'N/A',
                'finalized'      => $finalized,
                'total'          => $total,
                'assigned_at'    => $assignment->created_at,
            ];
        })->unique('area_id')->values();

        // ── RECENT ACTIVITIES (assignments) ──
        $recentActivities = AccreditationAssignment::with(['area'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn ($a) => [
                'icon'  => 'bx-folder',
                'color' => 'text-primary',
                'text'  => 'Dean assigned you to ' . ($a->area?->area_name ?? 'an area') . '.',
                'time'  => $a->created_at->diffForHumans(),
            ]);

        return compact(
            'totalAssignedAreas',
            'finalizedEvaluations',
            'submittedEvaluations',
            'totalDocuments',
            'assignedAreas',
            'recentActivities',
        );
    }

    private function assessorDashboardData(): array
    {
        $user = auth()->user();
        $internalAssessorRoleId = Role::where('name', UserType::INTERNAL_ASSESSOR->value)->value('id');
        $taskForceRoleId        = Role::where('name', UserType::TASK_FORCE->value)->value('id');

        // Get assigned areas for this internal assessor
        $assignments = AccreditationAssignment::with(['area', 'accreditationInfo', 'level', 'program'])
            ->where('user_id', $user->id)
            ->where('role_id', $internalAssessorRoleId)
            ->get();

        $assignedAreaIds = $assignments->pluck('area_id')->unique()->values();

        // ── STAT CARDS ──
        $totalAssignedAreas = $assignedAreaIds->count();

        $submittedEvaluations = AccreditationEvaluation::where('evaluated_by', $user->id)
            ->where('status', EvaluationStatus::SUBMITTED->value)
            ->count();

        $finalizedEvaluations = AccreditationEvaluation::where('evaluated_by', $user->id)
            ->where('status', EvaluationStatus::FINALIZED->value)
            ->count();

        $totalDocuments = AccreditationDocuments::whereIn('area_id', $assignedAreaIds)
            ->whereHas('uploader', function ($q) use ($taskForceRoleId) {
                $q->whereHas('roles', function ($q2) use ($taskForceRoleId) {
                    $q2->where('roles.id', $taskForceRoleId);
                });
            })
            ->count();

        // ── ASSIGNED AREAS OVERVIEW ──
        $assignedAreas = $assignments
            ->unique(fn ($a) => $a->area_id . '-' . $a->program_id . '-' . $a->level_id)
            ->groupBy('accred_info_id')
            ->map(function ($byAccred) use ($user, $internalAssessorRoleId) {

                $first = $byAccred->first();

                return [
                    'accreditation' => trim(($first->accreditationInfo?->title ?? '') . ' ' . ($first->accreditationInfo?->year ?? '')),
                    'info_id'       => $first->accred_info_id,
                    'levels'        => $byAccred->groupBy('level_id')->map(function ($byLevel) use ($user, $internalAssessorRoleId) {

                        $firstLevel = $byLevel->first();

                        return [
                            'level_id'   => $firstLevel->level_id,
                            'level_name' => $firstLevel->level?->level_name ?? 'N/A',
                            'programs'   => $byLevel->groupBy('program_id')->map(function ($byProgram) use ($user, $internalAssessorRoleId) {

                                $firstProgram = $byProgram->first();

                                $areas = $byProgram->map(function ($assignment) use ($user, $internalAssessorRoleId) {
                                    $evaluation = AccreditationEvaluation::where([
                                        'area_id'      => $assignment->area_id,
                                        'evaluated_by' => $user->id,
                                        'role_id'      => $internalAssessorRoleId,
                                        'program_id'   => $assignment->program_id,
                                        'level_id'     => $assignment->level_id,
                                    ])->first();

                                    return [
                                        'area_id'      => $assignment->area_id,
                                        'area_name'    => $assignment->area?->area_name ?? 'N/A',
                                        'info_id'      => $assignment->accred_info_id,
                                        'level_id'     => $assignment->level_id,
                                        'program_id'   => $assignment->program_id,
                                        'status'       => $evaluation?->status,
                                        'evaluation'   => $evaluation,
                                    ];
                                })->values();

                                $total     = $areas->count();
                                $finalized = $areas->filter(fn ($a) => $a['status'] === EvaluationStatus::FINALIZED)->count();

                                return [
                                    'program_id'         => $firstProgram->program_id,
                                    'program_name'       => $firstProgram->program?->program_name ?? 'N/A',
                                    'areas'              => $areas,
                                    'totalAreas'         => $total,
                                    'finalizedAreaCount' => $finalized,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })->values();

        // ── RECENT ACTIVITIES ──
        // Own evaluations
        $ownEvaluations = AccreditationEvaluation::with(['area', 'accreditationInfo', 'level', 'program'])
            ->where('evaluated_by', $user->id)
            ->whereIn('status', [EvaluationStatus::SUBMITTED->value, EvaluationStatus::FINALIZED->value])
            ->latest('updated_at')
            ->get()
            ->map(fn ($e) => [
                'icon'          => $e->status === EvaluationStatus::FINALIZED ? 'bx-check-shield' : 'bx-file',
                'color'         => $e->status === EvaluationStatus::FINALIZED ? 'text-success' : 'text-primary',
                'text' => 'You ' . ($e->status === EvaluationStatus::FINALIZED ? 'finalized' : 'submitted')
                    . ' evaluation for ' . trim(explode(':', $e->area?->area_name ?? 'an area')[0]) . '.',
                'sort_date'     => $e->updated_at,
                'time'          => $e->updated_at->diffForHumans(),
                'date'          => $e->updated_at->format('M d, Y h:i A'),  // ← add
                'accreditation' => trim(($e->accreditationInfo?->title ?? '') . ' ' . ($e->accreditationInfo?->year ?? '')),  // ← add
                'level'         => $e->level?->level_name ?? null,  // ← add
                'program'       => $e->program?->program_name ?? null,  // ← add
            ]);

        $taskForceUploads = AccreditationDocuments::with([
                'uploader',
                'area',
                'accredInfo',
                'level',
                'program',
                'subParameter.parameter',  // ← subparameter with its parent parameter
            ])
            ->whereIn('area_id', $assignedAreaIds)
            ->whereHas('uploader', function ($q) use ($taskForceRoleId) {
                $q->whereHas('roles', function ($q2) use ($taskForceRoleId) {
                    $q2->where('roles.id', $taskForceRoleId);
                });
            })
            ->latest()
            ->get()
            ->map(fn ($d) => [
                'icon'          => 'bx-upload',
                'color'         => 'text-info',
                'text' => ($d->uploader?->name ?? 'Someone')
                    . ' uploaded ' . ($d->file_name ?? 'a document')
                    . ' in ' . trim(explode(':', $d->area?->area_name ?? 'an area')[0])
                    . ($d->subParameter ? ' - ' . explode(' ', trim($d->subParameter->sub_parameter_name))[0] : '') . '.',
                'sort_date'     => $d->created_at,
                'time'          => $d->created_at->diffForHumans(),
                'date'          => $d->created_at->format('M d, Y h:i A'),
                'accreditation' => trim(($d->accredInfo?->title ?? '') . ' ' . ($d->accredInfo?->year ?? '')),
                'level'         => $d->level?->level_name ?? null,
                'program'       => $d->program?->program_name ?? null,
            ]);

        $recentActivities = $ownEvaluations
            ->concat($taskForceUploads)
            ->sortByDesc('sort_date')
            ->values();

        return compact(
            'totalAssignedAreas',
            'submittedEvaluations',
            'finalizedEvaluations',
            'totalDocuments',
            'assignedAreas',
            'recentActivities',
        );
    }

    private function accreditorDashboardData(): array
    {
        $internalAssessorRoleId = Role::where('name', UserType::INTERNAL_ASSESSOR->value)->value('id');

        // ── STAT CARDS ──
        $ongoingCount   = AccreditationInfo::where('status', AccreditationStatus::ONGOING)->count();
        $completedCount = AccreditationInfo::where('status', AccreditationStatus::COMPLETED)->count();
        $programsCount  = InfoLevelProgramMapping::distinct('program_id')->count('program_id');

        $finalizedEvaluations = AccreditationEvaluation::where('status', EvaluationStatus::FINALIZED->value)
            ->where('role_id', $internalAssessorRoleId)
            ->count();

        // ── ACCREDITATION OVERVIEW ──
        $ongoingAccreditations = AccreditationInfo::where('status', AccreditationStatus::ONGOING)
            ->with([
                'infoLevelProgramMappings.program',
                'infoLevelProgramMappings.programAreas.area',
                'infoLevelProgramMappings.level',
            ])
            ->latest()
            ->get(); // ← get() instead of first()

        $accreditationOverviews = $ongoingAccreditations->map(function ($accreditation) use ($internalAssessorRoleId) {

            // Group mappings by level
            $levelGroups = $accreditation->infoLevelProgramMappings->groupBy('level_id');

            $levels = $levelGroups->map(function ($mappings) use ($accreditation, $internalAssessorRoleId) {

                $rawLevel  = $mappings->first()?->level?->level_name;
                $levelName = match(true) {
                    str_contains(strtolower($rawLevel ?? ''), 'preliminary') => 'Preliminary Survey Visit (PSV)',
                    str_contains(strtolower($rawLevel ?? ''), 'level i')     => 'Level I',
                    str_contains(strtolower($rawLevel ?? ''), 'level ii')    => 'Level II',
                    str_contains(strtolower($rawLevel ?? ''), 'level iii')   => 'Level III',
                    str_contains(strtolower($rawLevel ?? ''), 'level iv')    => 'Level IV',
                    default => $rawLevel ?? 'N/A',
                };

                // Group by program within this level
                $programs = $mappings->map(function ($mapping) use ($accreditation, $internalAssessorRoleId) {

                    $overviewAreas = $mapping->programAreas->map(function ($programArea) use ($accreditation, $internalAssessorRoleId, $mapping) {

                        $finalizedEvals = AccreditationEvaluation::where([
                            'accred_info_id' => $accreditation->id,
                            'area_id'        => $programArea->area_id,
                            'role_id'        => $internalAssessorRoleId,
                        ])->where('status', EvaluationStatus::FINALIZED->value)->get();

                        $allEvals = AccreditationEvaluation::where([
                            'accred_info_id' => $accreditation->id,
                            'area_id'        => $programArea->area_id,
                            'role_id'        => $internalAssessorRoleId,
                        ])->get();

                        return [
                            'area_id'         => $programArea->area_id,
                            'area_name'       => $programArea->area->area_name ?? 'N/A',
                            'status'          => $finalizedEvals->isNotEmpty() ? 'finalized' : 'pending',
                            'assigned_count'  => $allEvals->pluck('evaluated_by')->unique()->count(),
                            'evaluation'      => $finalizedEvals->sortByDesc('updated_at')->first(),
                            'info_id'         => $accreditation->id,
                            'level_id'        => $mapping->level_id,
                            'program_id'      => $mapping->program_id,
                            'program_area_id' => $programArea->id,
                        ];
                    });

                    $finalizedAreaCount = $overviewAreas->where('status', 'finalized')->count();

                    return [
                        'program_name'       => $mapping->program->program_name ?? 'N/A',
                        'overviewAreas'      => $overviewAreas,
                        'totalAreas'         => $overviewAreas->count(),
                        'finalizedAreaCount' => $finalizedAreaCount,
                    ];
                });

                return [
                    'levelName' => $levelName,
                    'programs'  => $programs,
                ];
            });

            return [
                'accreditation' => $accreditation,
                'levels'        => $levels,
            ];
        });

        // ── RECENT ACTIVITIES ──
        $recentActivities = AccreditationEvaluation::with([
            'evaluator',
            'area',
            'accreditationInfo',
            'level',
            'program',
        ])
        ->where('role_id', $internalAssessorRoleId)
        ->whereIn('status', [EvaluationStatus::SUBMITTED->value, EvaluationStatus::FINALIZED->value])
        ->latest('updated_at')
        ->get()
        ->groupBy('accred_info_id')
        ->map(fn ($byAccred) => [
            'accreditation' => trim(($byAccred->first()->accreditationInfo?->title ?? '') . ' ' . ($byAccred->first()->accreditationInfo?->year ?? '')),
            'levels' => $byAccred->groupBy('level_id')->map(fn ($byLevel) => [
                'level'    => $byLevel->first()->level?->level_name ?? 'N/A',
                'programs' => $byLevel->groupBy('program_id')->map(fn ($byProgram) => [
                    'program'    => $byProgram->first()->program?->program_name ?? 'N/A',
                    'activities' => $byProgram->map(fn ($e) => [
                        'icon'  => $e->status === EvaluationStatus::FINALIZED ? 'bx-check-shield' : 'bx-file',
                        'color' => $e->status === EvaluationStatus::FINALIZED ? 'text-success'    : 'text-primary',
                        'text' => ($e->evaluator?->name ?? 'Someone')
                            . ($e->status === EvaluationStatus::FINALIZED ? ' finalized ' : ' submitted ')
                            . 'evaluation for ' . (explode(':', $e->area?->area_name ?? 'an area')[0]) . '.',
                        'area'  => $e->area?->area_name ?? null,
                        'time'  => $e->updated_at->diffForHumans(),
                        'date'  => $e->updated_at->format('M d, Y h:i A'),
                    ]),
                ]),
            ]),
        ]);

        return compact(
            'ongoingCount',
            'completedCount',
            'programsCount',
            'finalizedEvaluations',
            'accreditationOverviews',
            'recentActivities',
        );
    }
}