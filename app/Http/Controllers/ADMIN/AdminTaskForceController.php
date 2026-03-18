<?php

namespace App\Http\Controllers\ADMIN;

use App\Http\Controllers\Controller;
use App\Models\ADMIN\AccreditationAssignment;
use App\Models\User;
use App\Enums\UserType;
use App\Models\ADMIN\AccreditationDocuments;
use App\Models\ADMIN\InfoLevelProgramMapping;
use App\Models\AccreditationEvaluation;
use Illuminate\Http\JsonResponse;

class AdminTaskForceController extends Controller
{
    public function index()
    {
        $loggedInUser = auth()->user();
        $isAdmin = $loggedInUser->currentRole->name === UserType::ADMIN->value;
        $isDean = $loggedInUser->currentRole->name === UserType::DEAN->value;

        return view(
            'admin.users.taskforce', 
            compact('isAdmin', 'isDean')
        );
    }

    /**
     * Datatable data for Task Force users
     */

    public function data(): JsonResponse
    {
        $user = auth()->user();
        $isAdmin = $user->currentRole->name === UserType::ADMIN->value;
        $isDean = $user->currentRole->name === UserType::DEAN->value;

        $allowedRoles = [];
        
        if ($isAdmin) {
            $allowedRoles = [
                UserType::INTERNAL_ASSESSOR,
                UserType::ACCREDITOR,
            ];
        }

        if ($isDean) {
            $allowedRoles = [
                UserType::TASK_FORCE,
            ];
        }

        $users = User::query()
            ->where('status', 'Active')
            ->whereIn('user_type', $allowedRoles)
            ->latest()
            ->get();

        return response()->json([
            'data' => $users
        ]);
    }


    public function viewTaskForce($id)
    {
        $user = User::findOrFail($id);

        $isTaskForce = $user->user_type === UserType::TASK_FORCE;
        $isInternalAssessor = $user->user_type === UserType::INTERNAL_ASSESSOR;

        /* =====================================================
        | ASSIGNMENTS
        ===================================================== */
        $assignments = AccreditationAssignment::with([
            'accreditationInfo',
            'program',
            'area',
            'level',
        ])
        ->where('user_id', $user->id)
        ->get();

        if ($assignments->isEmpty()) {
            return view('admin.users.viewtaskforce', [
                'user' => $user,
                'assignmentHierarchy' => [],
                'isTaskForce' => $isTaskForce,
                'isInternalAssessor' => $isInternalAssessor,
            ]);
        }

        /* =====================================================
        | TASK FORCE → DOCUMENTS
        ===================================================== */
        $documents = collect();

        if ($isTaskForce) {
            $documents = AccreditationDocuments::with('uploader')
                ->where('upload_by', $user->id)
                ->get()
                ->groupBy(fn ($doc) => implode('-', [
                    $doc->accred_info_id,
                    $doc->level_id,
                    $doc->program_id,
                    $doc->area_id,
                    $doc->parameter_id,
                    $doc->subparameter_id,
                ]));
        }

        /* =====================================================
        | INTERNAL ASSESSOR → EVALUATIONS
        ===================================================== */
        $evaluations = collect();

        if ($isInternalAssessor) {
            $evaluations = AccreditationEvaluation::with([
                'subparameterRatings.subparameter',
                'areaRecommendations',
            ])
            ->where('evaluated_by', $user->id)
            ->get()
            ->keyBy(fn ($eval) => implode('-', [
                $eval->accred_info_id,
                $eval->level_id,
                $eval->program_id,
                $eval->area_id,
            ]));
        }

        /* =====================================================
        | INFO / LEVEL / PROGRAM MAP
        ===================================================== */
        $infoProgramMappings = InfoLevelProgramMapping::with([
            'programAreas.area',
            'programAreas.areaParameterMappings.parameter.sub_parameters',
        ])
        ->get()
        ->keyBy(fn ($map) =>
            $map->accreditation_info_id . '-' .
            $map->level_id . '-' .
            $map->program_id
        );

        $assignmentHierarchy = [];

        /* =====================================================
        | BUILD HIERARCHY
        ===================================================== */
        foreach ($assignments as $assignment) {

            $mapKey = implode('-', [
                $assignment->accred_info_id,
                $assignment->level_id,
                $assignment->program_id,
            ]);

            $infoMap = $infoProgramMappings[$mapKey] ?? null;
            if (!$infoMap) continue;

            foreach ($infoMap->programAreas as $programArea) {

                if ($programArea->area_id !== $assignment->area_id) continue;

                $accId  = $assignment->accreditationInfo->id;
                $progId = $assignment->program->id;
                $areaId = $programArea->area_id;

                /* ---------- BASE STRUCTURE ---------- */

                $assignmentHierarchy[$accId]['title']
                    = $assignment->accreditationInfo->title;
                
                $assignmentHierarchy[$accId]['year']
                    = $assignment->accreditationInfo->accreditation_year
                        ?? $assignment->accreditationInfo->year
                        ?? $assignment->accreditationInfo->created_at->year;

                $assignmentHierarchy[$accId]['level']
                    = $assignment->level->level_name; 

                $assignmentHierarchy[$accId]['programs'][$progId]['name']
                    = $assignment->program->program_name;

                $assignmentHierarchy[$accId]['programs'][$progId]['areas'][$areaId]['name']
                    = $programArea->area->area_name;

                /* =====================================================
                | TASK FORCE VIEW
                ===================================================== */
                if ($isTaskForce) {

                    foreach ($programArea->areaParameterMappings as $apm) {

                        if (!$apm->parameter) continue;

                        $paramId = $apm->parameter->id;

                        $assignmentHierarchy[$accId]['programs'][$progId]['areas'][$areaId]
                        ['parameters'][$paramId]['name']
                            = $apm->parameter->parameter_name;

                        foreach ($apm->subParameters as $sub) {

                            $docKey = implode('-', [
                                $assignment->accred_info_id,
                                $assignment->level_id,
                                $assignment->program_id,
                                $areaId,
                                $paramId,
                                $sub->id,
                            ]);

                            $uploadedDocs = $documents[$docKey] ?? collect();

                            $assignmentHierarchy[$accId]['programs'][$progId]['areas'][$areaId]
                            ['parameters'][$paramId]['sub_parameters'][$sub->id] = [
                                'name' => $sub->sub_parameter_name,
                                'documents' => $uploadedDocs->map(fn ($doc) => [
                                    'id' => $doc->id,
                                    'file_name' => $doc->file_name,
                                    'file_path' => $doc->file_path,
                                    'uploaded_by' => optional($doc->uploader)->name,
                                    'status' => 'Submitted',
                                ])->values(),
                            ];
                        }
                    }
                }

                /* =====================================================
                | INTERNAL ASSESSOR VIEW
                ===================================================== */
                if ($isInternalAssessor) {

                    $evalKey = implode('-', [
                        $assignment->accred_info_id,
                        $assignment->level_id,
                        $assignment->program_id,
                        $areaId,
                    ]);

                    $evaluation = $evaluations[$evalKey] ?? null;

                    if ($evaluation) {

                        $scores = $evaluation->subparameterRatings
                            ->pluck('score')
                            ->filter();

                        $areaMean = $scores->count()
                            ? round($scores->avg(), 2)
                            : null;

                        $assignmentHierarchy[$accId]['programs'][$progId]['areas'][$areaId]['evaluation'] = [
                            'status' => 'Evaluated',
                            'is_updated' => $evaluation->is_updated,
                            'updated_at' => $evaluation->updated_at,

                            'area_mean' => $areaMean,

                            'recommendations' => $evaluation->areaRecommendations
                                ->pluck('recommendation')
                                ->filter()
                                ->values(),
                        ];

                    } else {
                        $assignmentHierarchy[$accId]['programs'][$progId]['areas'][$areaId]['evaluation'] = [
                            'status' => 'Pending',
                            'area_mean' => null,
                            'recommendations' => [],
                        ];
                    }
                }
            }
        }

        /* =====================================================
        | RETURN VIEW
        ===================================================== */
        return view('admin.users.viewtaskforce', [
            'user' => $user,
            'assignmentHierarchy' => $assignmentHierarchy,
            'isTaskForce' => $isTaskForce,
            'isInternalAssessor' => $isInternalAssessor,
        ]);
    }
}
