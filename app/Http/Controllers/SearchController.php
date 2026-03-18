<?php

namespace App\Http\Controllers;

use App\Enums\AccreditationStatus;
use App\Models\AccreditationEvaluation;
use App\Models\ADMIN\AccreditationDocuments;
use App\Models\ADMIN\AccreditationInfo;
use App\Models\ADMIN\AreaParameterMapping;
use App\Models\ADMIN\InfoLevelProgramMapping;
use App\Models\ADMIN\ProgramAreaMapping;
use App\Models\ADMIN\SubParameter;
use App\Models\Role;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function global(Request $request)
    {
        $query = trim($request->get('q', ''));
        $user  = auth()->user();

        if (!$user || strlen($query) < 1) {
            return response()->json([]);
        }

        $role           = $user->currentRole?->name;
        $scopedAreaIds  = $this->getScopedProgramAreaIds($user, $role);

        $results = collect()
            ->merge($this->searchByRole($query, $role))
            ->merge($this->searchAssignedUsers($query, $user, $role, $scopedAreaIds))
            ->merge($this->searchAccreditations($query, $scopedAreaIds))
            ->merge($this->searchPrograms($query, $scopedAreaIds))
            ->merge($this->searchAreas($query, $scopedAreaIds))
            ->merge($this->searchParameters($query, $scopedAreaIds))
            ->merge($this->searchSubParameters($query, $scopedAreaIds))
            ->merge($this->searchDocuments($query, $scopedAreaIds))
            ->merge($this->searchEvaluations($query, $scopedAreaIds));

        return response()->json($results->take(30)->values());
    }

    // -------------------------------------------------------------------------
    // Role-scoped user search
    // -------------------------------------------------------------------------

    private function searchByRole(string $query, ?string $role): Collection
    {
        $roleMap = [
            UserType::ADMIN->value => [
                UserType::INTERNAL_ASSESSOR->value,
                UserType::ACCREDITOR->value,
            ],
            UserType::DEAN->value => [
                UserType::TASK_FORCE->value,
            ],
        ];

        $searchableRoles = $roleMap[$role] ?? [];

        if (empty($searchableRoles)) {
            return collect();
        }

        return $this->searchUsers($query, $searchableRoles);
    }

    private function searchUsers(string $query, array $roleNames): Collection
    {
        $roleIds = Role::whereIn('name', $roleNames)->pluck('id');

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return User::with([
                'currentRole',
                'areas',
                'assignments',
                'assignments.area',
            ])
            ->whereIn('current_role_id', $roleIds)
            ->where(fn($q) =>
                $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->orWhereHas('areas', fn($q) =>
                    $q->where('name', 'LIKE', "%{$query}%") // ← was 'area_name'
                )
            )
            ->limit(15)
            ->get()
            ->map(fn(User $u) => $this->item(
                type:       'user',
                id:         $u->id,
                title:      $u->name,
                subtitle:   $u->email,
                badge:      $u->currentRole?->name,
                badgeColor: $this->roleColor($u->currentRole?->name),
                url:        $this->userUrl($u),
                icon:       $this->userIcon($u->currentRole?->name),
                meta: [
                    'email'             => $u->email,
                    'role'              => $u->currentRole?->name ?? 'No Role',
                    'status'            => $u->status ?? 'Active',
                    'created_at'        => $u->created_at?->format('M d, Y'),
                    'areas'             => $u->areas
                                            ->map(fn($a) => [
                                                'id'   => $a->id,
                                                'name' => trim(explode(':', $a->area_name)[0]),
                                            ])
                                            ->values()
                                            ->toArray(),
                    'areas_count'       => $u->areas->count(),
                    'assignments'       => $u->assignments
                                            ->map(fn($a) => [
                                                'id'          => $a->id,
                                                'area_id'     => $a->area_id,
                                                'area_name'   => $a->area?->area_name ?? null,
                                                'status'      => $a->status ?? null,
                                                'assigned_at' => $a->created_at?->format('M d, Y'),
                                            ])
                                            ->values()
                                            ->toArray(),
                    'assignments_count' => $u->assignments->count(),
                ],
            ));
    }

    // -------------------------------------------------------------------------
    // Assigned users in the same areas (for Task Force & Internal Assessor)
    // -------------------------------------------------------------------------

    private function searchAssignedUsers(string $query, User $user, ?string $role, ?array $scopedAreaIds): Collection
    {
        // Only for Task Force and Internal Assessor
        $allowedRoles = [
            UserType::TASK_FORCE->value,
            UserType::INTERNAL_ASSESSOR->value,
        ];

        if (!in_array($role, $allowedRoles) || empty($scopedAreaIds)) {
            return collect();
        }

        return User::with(['currentRole', 'areas', 'assignments', 'assignments.area'])
            ->where('id', '!=', $user->id) // exclude themselves
            ->whereHas('assignments', fn($q) =>
                $q->whereIn('area_id', $scopedAreaIds)
            )
            ->where(fn($q) =>
                $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
            )
            ->limit(10)
            ->get()
            ->map(fn(User $u) => $this->item(
                type:       'user',
                id:         $u->id,
                title:      $u->name,
                subtitle:   $u->email,
                badge:      $u->currentRole?->name,
                badgeColor: $this->roleColor($u->currentRole?->name),
                url:        $this->userUrl($u),
                icon:       $this->userIcon($u->currentRole?->name),
                meta: [
                    'email'             => $u->email,
                    'role'              => $u->currentRole?->name ?? 'No Role',
                    'status'            => $u->status ?? 'Active',
                    'created_at'        => $u->created_at?->format('M d, Y'),
                    'areas'             => $u->areas
                                            ->map(fn($a) => [
                                                'id'   => $a->id,
                                                'name' => trim(explode(':', $a->area_name)[0]),
                                            ])
                                            ->values()
                                            ->toArray(),
                    'areas_count'       => $u->areas->count(),
                    'assignments'       => $u->assignments
                                            ->map(fn($a) => [
                                                'id'          => $a->id,
                                                'area_id'     => $a->area_id,
                                                'area_name'   => $a->area?->area_name ?? null,
                                                'status'      => $a->status ?? null,
                                                'assigned_at' => $a->created_at?->format('M d, Y'),
                                            ])
                                            ->values()
                                            ->toArray(),
                    'assignments_count' => $u->assignments->count(),
                ],
            ));
    }

    // -------------------------------------------------------------------------
    // Accreditation search
    // -------------------------------------------------------------------------

    private function searchAccreditations(string $query, ?array $scopedAreaIds): Collection
    {
        return AccreditationInfo::with([
                'accreditationBody',
                'levels',
                'levels.programs',
            ])
            ->when($scopedAreaIds !== null, fn($q) =>
                $q->whereHas('infoLevelProgramMappings', fn($q) =>
                    $q->whereHas('programAreas', fn($q) =>
                        $q->whereIn('id', $scopedAreaIds)
                    )
                )
            )
            ->where(fn($q) =>
                $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('year', 'LIKE', "%{$query}%")
                ->orWhere('visit_type', 'LIKE', "%{$query}%")
                ->orWhereHas('accreditationBody', fn($q) =>
                    $q->where('name', 'LIKE', "%{$query}%")
                )
            )
            ->limit(10)
            ->get()
            ->map(fn(AccreditationInfo $a) => $this->item(
                type:       'accreditation',
                id:         $a->id,
                title:      $a->title,
                subtitle:   ($a->accreditationBody?->name ?? 'No Body')
                            . ' · ' . $a->year
                            . ' · ' . ($a->visit_type ?? ''),
                badge:      $a->status?->value ?? null,
                badgeColor: $this->accreditationStatusColor($a->status?->value),
                url:        route('accreditation.show', ['id' => $a->id]),
                icon:       'bx-certification',
                meta: [
                    'year'           => $a->year,
                    'status'         => $a->status?->value,
                    'visit_type'     => $a->visit_type,
                    'date'           => $a->accreditation_date?->format('M d, Y'),
                    'body'           => [
                        'id'   => $a->accreditationBody?->id,
                        'name' => $a->accreditationBody?->name,
                    ],
                    'levels'         => $a->levels->map(fn($l) => [
                        'id'       => $l->id,
                        'name'     => $l->level_name,
                        'programs' => $l->programs->map(fn($p) => [
                            'id'   => $p->id,
                            'name' => $p->program_name,
                        ])->toArray(),
                    ])->toArray(),
                    'levels_count'   => $a->levels->count(),
                    'programs_count' => $a->levels->flatMap->programs->count(),
                ],
            ));
    }


    // -------------------------------------------------------------------------
    // Program search
    // -------------------------------------------------------------------------

    private function searchPrograms(string $query, ?array $scopedAreaIds): Collection
    {
        return InfoLevelProgramMapping::with([
                'program',
                'level',
                'accreditationInfo',
                'accreditationInfo.accreditationBody',
                'areas',
            ])
            ->when($scopedAreaIds !== null, fn($q) =>
                $q->whereHas('programAreas', fn($q) =>
                    $q->whereIn('id', $scopedAreaIds)
                )
            )
            ->whereHas('program', fn($q) =>
                $q->where('program_name', 'LIKE', "%{$query}%")
                ->orWhere('specialization', 'LIKE', "%{$query}%")
            )
            ->limit(10)
            ->get()
            ->map(fn(InfoLevelProgramMapping $m) => $this->item(
                type:       'program',
                id:         $m->program->id,
                title:      $m->program->program_name,
                subtitle:   ($m->accreditationInfo?->title ?? 'Unknown Accreditation')
                            . ' · ' . ($m->level?->level_name ?? 'No Level')
                            . ($m->program->specialization ? ' · ' . $m->program->specialization : ''),
                badge:      $m->level?->level_name ?? 'Program',
                badgeColor: 'primary',
                url:        route('admin.accreditations.program', [
                                'infoId'      => $m->accreditation_info_id,
                                'programName' => $m->program->program_name,
                                'levelId'     => $m->level_id,
                            ]),
                icon:       'bx-book',
                meta: [
                    'specialization' => $m->program->specialization,
                    'accreditation'  => [
                        'id'   => $m->accreditationInfo?->id,
                        'name' => $m->accreditationInfo?->title,
                        'body' => $m->accreditationInfo?->accreditationBody?->name,
                        'year' => $m->accreditationInfo?->year,
                    ],
                    'level'          => [
                        'id'   => $m->level?->id,
                        'name' => $m->level?->level_name,
                    ],
                    'areas_count'    => $m->areas->count(),
                    'areas'          => $m->areas->map(fn($a) => [
                        'id'   => $a->id,
                        'name' => trim(explode(':', $a->area_name)[0]),
                    ])->toArray(),
                ],
            ));
    }

    // -------------------------------------------------------------------------
    // Area search
    // -------------------------------------------------------------------------

    private function searchAreas(string $query, ?array $scopedAreaIds): Collection
    {
        return ProgramAreaMapping::with([
                'area',
                'area.level',
                'infoLevelProgramMapping',
                'infoLevelProgramMapping.program',
                'infoLevelProgramMapping.level',
                'infoLevelProgramMapping.accreditationInfo',
                'infoLevelProgramMapping.accreditationInfo.accreditationBody',
            ])
            ->when($scopedAreaIds !== null, fn($q) =>
                $q->whereIn('id', $scopedAreaIds)
            )
            ->whereHas('area', fn($q) =>
                $q->where('area_name', 'LIKE', "%{$query}%")
                ->orWhere('area_description', 'LIKE', "%{$query}%")
            )
            ->limit(10)
            ->get()
            ->map(fn(ProgramAreaMapping $m) => $this->item(
                type:       'area',
                id:         $m->area->id,
                title:      trim(explode(':', $m->area->area_name)[0]),
                subtitle:   ($m->infoLevelProgramMapping?->program?->program_name ?? 'Unknown Program')
                            . ' · ' . ($m->infoLevelProgramMapping?->level?->level_name ?? 'No Level')
                            . ' · ' . ($m->infoLevelProgramMapping?->accreditationInfo?->title ?? 'Unknown Accreditation'),
                badge:      $m->infoLevelProgramMapping?->level?->level_name ?? null,
                badgeColor: 'warning',
                url:        route('program.areas.parameters', [
                                'infoId'        => $m->infoLevelProgramMapping?->accreditation_info_id,
                                'levelId'       => $m->infoLevelProgramMapping?->level_id,
                                'programId'     => $m->infoLevelProgramMapping?->program_id,
                                'programAreaId' => $m->id,
                            ]),
                icon:       'bx-layer',
                meta: [
                    'full_name'               => $m->area->area_name,
                    'description'             => $m->area->area_description,
                    'evaluated'               => $m->area->evaluated,
                    'program_area_mapping_id' => $m->id,
                    'program'                 => [
                        'id'   => $m->infoLevelProgramMapping?->program?->id,
                        'name' => $m->infoLevelProgramMapping?->program?->program_name,
                    ],
                    'level'                   => [
                        'id'   => $m->infoLevelProgramMapping?->level?->id,
                        'name' => $m->infoLevelProgramMapping?->level?->level_name,
                    ],
                    'accreditation'           => [
                        'id'   => $m->infoLevelProgramMapping?->accreditationInfo?->id,
                        'name' => $m->infoLevelProgramMapping?->accreditationInfo?->title,
                        'body' => $m->infoLevelProgramMapping?->accreditationInfo?->accreditationBody?->name,
                        'year' => $m->infoLevelProgramMapping?->accreditationInfo?->year,
                    ],
                ],
            ));
    }

    // -------------------------------------------------------------------------
    // Parameter search
    // -------------------------------------------------------------------------

    private function searchParameters(string $query, ?array $scopedAreaIds): Collection
    {
        return AreaParameterMapping::with([
                'parameter',
                'parameter.sub_parameters',
                'programArea',
                'programArea.area',
                'programArea.infoLevelProgramMapping',
                'programArea.infoLevelProgramMapping.program',
                'programArea.infoLevelProgramMapping.level',
                'programArea.infoLevelProgramMapping.accreditationInfo',
                'programArea.infoLevelProgramMapping.accreditationInfo.accreditationBody',
            ])
            ->when($scopedAreaIds !== null, fn($q) =>
                $q->whereIn('program_area_mapping_id', $scopedAreaIds)
            )
            ->whereHas('parameter', fn($q) =>
                $q->where('parameter_name', 'LIKE', "%{$query}%")
            )
            ->limit(10)
            ->get()
            ->map(fn(AreaParameterMapping $m) => $this->item(
                type:       'parameter',
                id:         $m->parameter->id,
                title:      $m->parameter->parameter_name,
                subtitle:   'Area: '         . trim(explode(':', $m->programArea?->area?->area_name ?? 'Unknown')[0])
                            . ' · Program: ' . ($m->programArea?->infoLevelProgramMapping?->program?->program_name ?? 'Unknown')
                            . ' · Level: '   . ($m->programArea?->infoLevelProgramMapping?->level?->level_name ?? 'Unknown'),
                badge:      'Parameter',
                badgeColor: 'info',
                url:        route('program.areas.parameters', [
                                'infoId'        => $m->programArea?->infoLevelProgramMapping?->accreditation_info_id,
                                'levelId'       => $m->programArea?->infoLevelProgramMapping?->level_id,
                                'programId'     => $m->programArea?->infoLevelProgramMapping?->program_id,
                                'programAreaId' => $m->program_area_mapping_id,
                            ]),
                icon:       'bx-list-ul',
                meta: [
                    'area_parameter_mapping_id' => $m->id,
                    'sub_parameters_count'      => $m->parameter->sub_parameters->count(),
                    'sub_parameters'            => $m->parameter->sub_parameters->map(fn($s) => [
                        'id'   => $s->id,
                        'name' => $s->sub_parameter_name,
                    ])->toArray(),
                    'area'          => [
                        'id'   => $m->programArea?->area?->id,
                        'name' => trim(explode(':', $m->programArea?->area?->area_name ?? '')[0]),
                    ],
                    'program'       => [
                        'id'   => $m->programArea?->infoLevelProgramMapping?->program?->id,
                        'name' => $m->programArea?->infoLevelProgramMapping?->program?->program_name,
                    ],
                    'level'         => [
                        'id'   => $m->programArea?->infoLevelProgramMapping?->level?->id,
                        'name' => $m->programArea?->infoLevelProgramMapping?->level?->level_name,
                    ],
                    'accreditation' => [
                        'id'   => $m->programArea?->infoLevelProgramMapping?->accreditationInfo?->id,
                        'name' => $m->programArea?->infoLevelProgramMapping?->accreditationInfo?->title,
                        'body' => $m->programArea?->infoLevelProgramMapping?->accreditationInfo?->accreditationBody?->name,
                        'year' => $m->programArea?->infoLevelProgramMapping?->accreditationInfo?->year,
                    ],
                ],
            ));
    }


    // -------------------------------------------------------------------------
    // Sub-parameter search
    // -------------------------------------------------------------------------
    private function searchSubParameters(string $query, ?array $scopedAreaIds): Collection
    {
        return AreaParameterMapping::with([
                'parameter',
                'subParameters',
                'programArea',
                'programArea.area',
                'programArea.infoLevelProgramMapping',
                'programArea.infoLevelProgramMapping.program',
                'programArea.infoLevelProgramMapping.level',
                'programArea.infoLevelProgramMapping.accreditationInfo',
                'programArea.infoLevelProgramMapping.accreditationInfo.accreditationBody',
            ])
            ->when($scopedAreaIds !== null, fn($q) =>
                $q->whereIn('program_area_mapping_id', $scopedAreaIds)
            )
            ->whereHas('subParameters', fn($q) =>
                $q->where('sub_parameter_name', 'LIKE', "%{$query}%")
            )
            ->limit(10)
            ->get()
            ->flatMap(fn(AreaParameterMapping $m) =>
                $m->subParameters
                    ->filter(fn($s) => str_contains(
                        strtolower($s->sub_parameter_name),
                        strtolower($query)
                    ))
                    ->map(fn(SubParameter $s) => $this->item(
                        type:       'sub_parameter',
                        id:         $s->id,
                        title:      $s->sub_parameter_name,
                        subtitle:   'Parameter: '  . ($m->parameter?->parameter_name ?? 'Unknown')
                                    . ' · Area: '  . trim(explode(':', $m->programArea?->area?->area_name ?? 'Unknown')[0])
                                    . ' · Program: ' . ($m->programArea?->infoLevelProgramMapping?->program?->program_name ?? 'Unknown'),
                        badge:      'Sub-Parameter',
                        badgeColor: 'secondary',
                        url:        route('subparam.uploads.index', [
                                        'infoId'        => $m->programArea?->infoLevelProgramMapping?->accreditation_info_id,
                                        'levelId'       => $m->programArea?->infoLevelProgramMapping?->level_id,
                                        'programId'     => $m->programArea?->infoLevelProgramMapping?->program_id,
                                        'programAreaId' => $m->program_area_mapping_id,
                                        'subParameter'  => $s->id,
                                    ]),
                        icon:       'bx-subdirectory-right',
                        meta: [
                            'parameter'     => [
                                'id'   => $m->parameter?->id,
                                'name' => $m->parameter?->parameter_name,
                            ],
                            'area'          => [
                                'id'   => $m->programArea?->area?->id,
                                'name' => trim(explode(':', $m->programArea?->area?->area_name ?? '')[0]),
                            ],
                            'program'       => [
                                'id'   => $m->programArea?->infoLevelProgramMapping?->program?->id,
                                'name' => $m->programArea?->infoLevelProgramMapping?->program?->program_name,
                            ],
                            'level'         => [
                                'id'   => $m->programArea?->infoLevelProgramMapping?->level?->id,
                                'name' => $m->programArea?->infoLevelProgramMapping?->level?->level_name,
                            ],
                            'accreditation' => [
                                'id'   => $m->programArea?->infoLevelProgramMapping?->accreditationInfo?->id,
                                'name' => $m->programArea?->infoLevelProgramMapping?->accreditationInfo?->title,
                                'body' => $m->programArea?->infoLevelProgramMapping?->accreditationInfo?->accreditationBody?->name,
                                'year' => $m->programArea?->infoLevelProgramMapping?->accreditationInfo?->year,
                            ],
                        ],
                    ))
            );
    }

    // -------------------------------------------------------------------------
    // Documents search
    // -------------------------------------------------------------------------
    private function searchDocuments(string $query, ?array $scopedAreaIds): Collection
    {
        return AccreditationDocuments::with([
                'subParameter',
                'uploader',
                'uploaderRole',
                'accredInfo',
                'accredInfo.accreditationBody',
                'level',
                'program',
                'area',
                'parameter',
            ])
            ->when($scopedAreaIds !== null, fn($q) =>
                $q->whereIn('area_id', $scopedAreaIds)
            )
            ->where(fn($q) =>
                $q->where('file_name', 'LIKE', "%{$query}%")
                ->orWhere('file_type', 'LIKE', "%{$query}%")
                ->orWhereHas('uploader', fn($q) =>
                    $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                )
                ->orWhereHas('subParameter', fn($q) =>
                    $q->where('sub_parameter_name', 'LIKE', "%{$query}%")
                )
                ->orWhereHas('area', fn($q) =>
                    $q->where('area_name', 'LIKE', "%{$query}%")
                )
                ->orWhereHas('parameter', fn($q) =>
                    $q->where('parameter_name', 'LIKE', "%{$query}%")
                )
                ->orWhereHas('accredInfo', fn($q) =>
                    $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhereHas('accreditationBody', fn($q) =>
                        $q->where('name', 'LIKE', "%{$query}%")
                    )
                )
                ->orWhereHas('program', fn($q) =>
                    $q->where('program_name', 'LIKE', "%{$query}%")
                )
            )
            ->limit(10)
            ->get()
            ->map(fn(AccreditationDocuments $d) => $this->item(
                type:       'document',
                id:         $d->id,
                title:      $d->file_name,
                subtitle:   ($d->parameter?->parameter_name ?? 'No Parameter')
                            . ' · ' . ($d->subParameter?->sub_parameter_name ?? 'No Sub-Parameter')
                            . ' · ' . ($d->program?->program_name ?? 'No Program'),
                badge:      $d->file_type ?? 'File',
                badgeColor: $this->documentTypeColor($d->file_type),
                url:        route('subparam.uploads.index', [
                                'infoId'        => $d->accred_info_id,
                                'levelId'       => $d->level_id,
                                'programId'     => $d->program_id,
                                'programAreaId' => $d->area_id,
                                'subParameter'  => $d->subparameter_id,
                            ]),
                icon:       $this->documentIcon($d->file_type),
                meta: [
                    'file_name'     => $d->file_name,
                    'file_type'     => $d->file_type,
                    'file_path'     => $d->file_path,
                    'uploaded_at'   => $d->created_at?->format('M d, Y'),
                    'uploader'      => [
                        'id'    => $d->uploader?->id,
                        'name'  => $d->uploader?->name,
                        'email' => $d->uploader?->email,
                        'role'  => $d->uploaderRole?->name,
                    ],
                    'sub_parameter' => [
                        'id'   => $d->subParameter?->id,
                        'name' => $d->subParameter?->sub_parameter_name,
                    ],
                    'parameter'     => [
                        'id'   => $d->parameter?->id,
                        'name' => $d->parameter?->parameter_name,
                    ],
                    'area'          => [
                        'id'   => $d->area?->id,
                        'name' => trim(explode(':', $d->area?->area_name ?? '')[0]),
                    ],
                    'program'       => [
                        'id'   => $d->program?->id,
                        'name' => $d->program?->program_name,
                    ],
                    'level'         => [
                        'id'   => $d->level?->id,
                        'name' => $d->level?->level_name ?? null,
                    ],
                    'accreditation' => [
                        'id'   => $d->accredInfo?->id,
                        'name' => $d->accredInfo?->title,
                        'body' => $d->accredInfo?->accreditationBody?->name,
                        'year' => $d->accredInfo?->year,
                    ],
                ],
            ));
    }

    // -------------------------------------------------------------------------
    // Evaluation search
    // -------------------------------------------------------------------------

    private function searchEvaluations(string $query, ?array $scopedAreaIds): Collection
    {
        return AccreditationEvaluation::with([
                'accreditationInfo',
                'accreditationInfo.accreditationBody',
                'level',
                'program',
                'area',
                'evaluator',
                'evaluator.currentRole',
                'subparameterRatings',
            ])
            ->when($scopedAreaIds !== null, fn($q) =>
                $q->whereIn('area_id', $scopedAreaIds)
            )
            ->where(fn($q) =>
                $q->whereHas('evaluator', fn($q) =>
                    $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                )
                ->orWhereHas('area', fn($q) =>
                    $q->where('area_name', 'LIKE', "%{$query}%")
                )
                ->orWhereHas('program', fn($q) =>
                    $q->where('program_name', 'LIKE', "%{$query}%")
                )
                ->orWhereHas('accreditationInfo', fn($q) =>
                    $q->where('title', 'LIKE', "%{$query}%")
                )
                ->orWhere('status', 'LIKE', "%{$query}%")
            )
            ->limit(10)
            ->get()
            ->map(fn(AccreditationEvaluation $e) => $this->item(
                type:       'evaluation',
                id:         $e->id,
                title:      trim(explode(':', $e->area?->area_name ?? 'Unknown Area')[0]),
                subtitle:   ($e->program?->program_name ?? 'Unknown Program')
                            . ' · ' . ($e->level?->level_name ?? 'No Level')
                            . ' · ' . ($e->accreditationInfo?->title ?? 'Unknown Accreditation'),
                badge:      $e->status?->value ?? null,
                badgeColor: $this->evaluationStatusColor($e->status?->value),
                url:        route('program.areas.evaluations.summary', [
                                'evaluation' => $e->id,
                                'area' => $e->area_id,
                            ]),
                icon:       'bxs-notepad',
                meta: [
                    'status'        => $e->status?->value,
                    'is_final'      => $e->is_final,
                    'is_updated'    => $e->is_updated,
                    'evaluated_at'  => $e->updated_at?->format('M d, Y'),
                    'evaluator'     => [
                        'id'    => $e->evaluator?->id,
                        'name'  => $e->evaluator?->name,
                        'email' => $e->evaluator?->email,
                        'role'  => $e->evaluator?->currentRole?->name,
                    ],
                    'area'          => [
                        'id'   => $e->area?->id,
                        'name' => trim(explode(':', $e->area?->area_name ?? '')[0]),
                    ],
                    'program'       => [
                        'id'   => $e->program?->id,
                        'name' => $e->program?->program_name,
                    ],
                    'level'         => [
                        'id'   => $e->level?->id,
                        'name' => $e->level?->level_name,
                    ],
                    'accreditation' => [
                        'id'   => $e->accreditationInfo?->id,
                        'name' => $e->accreditationInfo?->title,
                        'body' => $e->accreditationInfo?->accreditationBody?->name,
                        'year' => $e->accreditationInfo?->year,
                    ],
                    'ratings_count' => $e->subparameterRatings->count(),
                ],
            ));
    }


    // -------------------------------------------------------------------------
    // Item builder — single consistent shape for all result types
    // -------------------------------------------------------------------------

    private function item(
        string  $type,
        int     $id,
        string  $title,
        string  $subtitle,
        ?string $badge,
        ?string $badgeColor,
        string  $url,
        string  $icon,
        array   $meta = [],
    ): array {
        return [
            'id'          => "{$type}-{$id}",
            'type'        => $type,
            'title'       => $title,
            'subtitle'    => $subtitle,
            'badge'       => $badge,
            'badge_color' => $badgeColor,
            'url'         => $url,
            'icon'        => $icon,
            'meta'        => $meta,
        ];
    }

    // -------------------------------------------------------------------------
    // URL / icon / color resolvers
    // -------------------------------------------------------------------------

    private function userUrl(User $u): string
    {
        return match ($u->currentRole?->name) {
            UserType::INTERNAL_ASSESSOR->value  => route('taskforce.view', $u),
            UserType::TASK_FORCE->value         => route('taskforce.view', $u),
            UserType::ACCREDITOR->value         => route('taskforce.view', $u),
            UserType::DEAN->value               => route('taskforce.view', $u),
            default                             => route('taskforce.show', $u),
        };
    }

    private function userIcon(?string $role): string
    {
        return match ($role) {
            UserType::ADMIN->value             => 'bx-crown',
            UserType::DEAN->value              => 'bx-building',
            UserType::INTERNAL_ASSESSOR->value => 'bx-user-check',
            UserType::TASK_FORCE->value        => 'bx-group',
            UserType::ACCREDITOR->value        => 'bx-shield',
            default                            => 'bx-user',
        };
    }

    private function roleColor(?string $role): string
    {
        return match ($role) {
            UserType::ADMIN->value             => 'danger',
            UserType::DEAN->value              => 'warning',
            UserType::INTERNAL_ASSESSOR->value => 'info',
            UserType::TASK_FORCE->value        => 'primary',
            UserType::ACCREDITOR->value        => 'success',
            default                            => 'secondary',
        };
    }

    private function accreditationStatusColor(?string $status): string
    {
        return match ($status) {
            AccreditationStatus::ONGOING->value   => 'warning',
            AccreditationStatus::COMPLETED->value => 'success',
            default     => 'secondary',
        };
    }

    // -------------------------------------------------------------------------
    // Resolve which program_area_mapping IDs the user can see.
    // Returns null = no restriction (see everything).
    // Returns array = restricted to these program_area_mapping IDs only.
    // -------------------------------------------------------------------------

    private function getScopedProgramAreaIds(User $user, ?string $role): ?array
    {
        $restrictedRoles = [
            UserType::TASK_FORCE->value,
            UserType::INTERNAL_ASSESSOR->value,
        ];

        if (!in_array($role, $restrictedRoles)) {
            return null; // Admin, Dean, Accreditor see everything
        }

        return $user->assignments()->pluck('area_id')->unique()->values()->toArray();
    }

    // -------------------------------------------------------------------------
    // Document icon / color resolvers
    // -------------------------------------------------------------------------

    private function documentIcon(?string $fileType): string
    {
        if (!$fileType) return 'bx-file';

        $type = strtolower($fileType);

        return match (true) {
            str_contains($type, 'pdf')                          => 'bxs-file-pdf',
            str_contains($type, 'word') || $type === 'docx'
                || $type === 'doc'                              => 'bxs-file-doc',
            str_contains($type, 'sheet') || $type === 'xlsx'
                || $type === 'xls' || $type === 'csv'          => 'bxs-spreadsheet',
            str_contains($type, 'image') || $type === 'png'
                || $type === 'jpg' || $type === 'jpeg'         => 'bxs-image',
            str_contains($type, 'zip') || $type === 'rar'      => 'bxs-archive',
            default                                             => 'bxs-file-blank',
        };
    }

    private function documentTypeColor(?string $fileType): string
    {
        if (!$fileType) return 'secondary';

        $type = strtolower($fileType);

        return match (true) {
            str_contains($type, 'pdf')                          => 'danger',
            str_contains($type, 'word') || $type === 'docx'
                || $type === 'doc'                              => 'primary',
            str_contains($type, 'sheet') || $type === 'xlsx'
                || $type === 'xls' || $type === 'csv'          => 'success',
            str_contains($type, 'image') || $type === 'png'
                || $type === 'jpg' || $type === 'jpeg'         => 'warning',
            str_contains($type, 'zip') || $type === 'rar'      => 'info',
            default                                             => 'secondary',
        };
    }

    private function evaluationStatusColor(?string $status): string
    {
        return match ($status) {
            'draft'     => 'secondary',
            'updated'   => 'warning',
            'finalized' => 'success',
            default     => 'secondary',
        };
    }
}