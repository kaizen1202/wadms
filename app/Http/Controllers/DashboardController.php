<?php

namespace App\Http\Controllers;

use App\Enums\AccreditationStatus;
use App\Enums\EvaluationStatus;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\AccreditationEvaluation;
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

        $data = [];

        if ($roleView === 'admin') {
            $data = $this->adminDashboardData();
        }

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
            ->with(['infoLevelProgramMappings.programAreas.area'])
            ->latest()
            ->first();

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
                $evaluations = AccreditationEvaluation::where([
                    'accred_info_id' => $ongoingAccreditation->id,
                    'area_id'        => $programArea->area_id,
                    'role_id'        => $internalAssessorRoleId,
                ])->where('status', EvaluationStatus::FINALIZED->value) // ← only finalized
                ->get();

                $status = $evaluations->isNotEmpty() ? 'finalized' : 'pending';
                if ($evaluations->isNotEmpty()) {
                    $status = $evaluations->every(fn ($e) => $e->status === EvaluationStatus::FINALIZED)
                        ? 'finalized'
                        : 'submitted';
                }

                // Pull IDs from the mapping relationship
                $mapping = $programArea->infoLevelProgramMapping;

                return [
                    'area_id'    => $programArea->area_id,
                    'area_name'  => $programArea->area->area_name ?? 'N/A',
                    'status'     => $status,
                    'evaluators' => $evaluations->pluck('evaluated_by')->unique()->count(),
                    'evaluation' => $evaluations->sortByDesc('updated_at')->first(),
                    'info_id'       => $ongoingAccreditation->id,
                    'level_id'      => $mapping?->level_id,
                    'program_id'    => $mapping?->program_id,
                    'program_area_id' => $programArea->id,
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
            'overviewAreas',
            'totalAreas',
            'finalizedAreaCount',
            'recentEvaluations',
        );
    }
}