<?php

namespace App\Http\Controllers\ADMIN;

use App\Enums\EvaluationStatus;
use App\Enums\TaskForceRole;
use App\Enums\UserStatus;
use App\Enums\VisitType;
use App\Http\Controllers\Controller;
use App\Models\AccreditationEvaluation;
use App\Models\ADMIN\AccreditationAssignment;
use App\Models\ADMIN\AccreditationBody;
use App\Models\ADMIN\AccreditationDocuments;
use App\Models\ADMIN\AccreditationInfo;
use App\Models\ADMIN\AccreditationLevel;
use App\Models\ADMIN\Area;
use App\Models\ADMIN\AreaParameterMapping;
use App\Models\ADMIN\InfoLevelProgramMapping;
use App\Models\ADMIN\Parameter;
use App\Models\ADMIN\Program;
use App\Models\ADMIN\ProgramAreaMapping;
use App\Models\ADMIN\SubParameter;
use App\Models\AreaEvaluation;
use App\Models\Role;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class AdminAcreditationController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        $isAdmin = $user?->user_type === UserType::ADMIN;
        $isInternalAssessor = $user?->user_type === UserType::INTERNAL_ASSESSOR;

        return view(
            'admin.accreditors.acrreditation',
            compact('isAdmin', 'isInternalAssessor')
        );
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'date' => 'required|date',
            'accreditation_body' => 'required',
            'visit_type' => ['required', Rule::enum(VisitType::class)]
        ]);

        DB::transaction(function () use ($request) {

            // Accreditation Body
            $body = AccreditationBody::firstOrCreate([
                'name' => $request->accreditation_body
            ]);

            // Accreditation Info
            $accreditation = AccreditationInfo::create([
                'title' => $request->title,
                'year' => Carbon::parse($request->date)->year,
                'status' => 'ongoing',
                'visit_type' => $request->visit_type,
                'accreditation_body_id' => $body->id,
                'accreditation_date' => $request->date
            ]);

            // Level (SINGLE)
            $level = AccreditationLevel::firstOrCreate([
                'level_name' => $request->level
            ]);

            // Programs (MULTIPLE)
            foreach ($request->programs as $programName) {

                $program = Program::firstOrCreate([
                    'program_name' => $programName
                ]);

                InfoLevelProgramMapping::create([
                    'accreditation_info_id' => $accreditation->id,
                    'level_id' => $level->id,
                    'program_id' => $program->id,
                ]);
            }
        });

        return back()->with('success', 'Accreditation saved successfully.');
    }

    public function show($id)
    {
        $user = auth()->user();
        $isAdmin = $user->user_type === UserType::ADMIN;
        $accreditation = AccreditationInfo::with('accreditationBody')->findOrFail($id);

        $levels = InfoLevelProgramMapping::with(['level', 'program'])
            ->where('accreditation_info_id', $id)
            ->get()
            ->groupBy('level_id');

        return view('admin.accreditors.show-accreditation', [
            'accreditation' => $accreditation,
            'levels' => $levels,
            'isAdmin' => $isAdmin
        ]);
    }

    public function edit($id)
    {
        $accreditation = AccreditationInfo::with('accreditationBody')->findOrFail($id);

        return response()->json([
            'id' => $accreditation->id,
            'title' => $accreditation->title,

            'date' => $accreditation->accreditation_date
                ? Carbon::parse($accreditation->accreditation_date)->format('Y-m-d')
                : null,

            'accreditation_body' => $accreditation->accreditationBody?->name,

            'visit_type' => strtolower($accreditation->visit_type),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'date' => 'required|date',
            'accreditation_body' => 'required|string',
            'visit_type' => ['required', Rule::enum(VisitType::class)],
        ]);

        $accreditation = null;
        $body = null;

        DB::transaction(function () use ($request, $id, &$accreditation, &$body) {

            // Accreditation Body
            $body = AccreditationBody::firstOrCreate([
                'name' => $request->accreditation_body
            ]);

            // Accreditation Info
            $accreditation = AccreditationInfo::findOrFail($id);

            $accreditation->update([
                'title' => $request->title,
                'year' => Carbon::parse($request->date)->year,
                'accreditation_body_id' => $body->id,
                'accreditation_date' => $request->date,
                'visit_type' => $request->visit_type,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Accreditation updated successfully.',
            'data' => [
                'title' => $accreditation->title,
                'visit_type' => $accreditation->visit_type,
                'accreditation_body' => $body->name,
                'accreditation_date' =>
                    optional($accreditation->accreditation_date)->format('F d, Y'),
            ]
        ]);
    }


    public function addLevelWithPrograms(Request $request)
    {
        $request->validate([
            'accreditation_info_id' => 'required|exists:accreditation_infos,id',
            'level' => 'required|string',
            'programs' => 'required|array|min:1',
            'programs.*' => 'required|string'
        ]);

        DB::transaction(function () use ($request) {


            $level = AccreditationLevel::firstOrCreate([
                'level_name' => $request->level
            ]);

            foreach ($request->programs as $programName) {

                // Ensure Program exists
                $program = Program::firstOrCreate([
                    'program_name' => $programName
                ]);

                // Prevent duplicate mapping
                InfoLevelProgramMapping::firstOrCreate([
                    'accreditation_info_id' => $request->accreditation_info_id,
                    'level_id' => $level->id,
                    'program_id' => $program->id,
                ]);
            }
        });

        return response()->json([
            'message' => 'Level and programs added successfully.'
        ], 200);
    }
    public function addProgramOnly(Request $request)
    {
        $request->validate([
            'accreditation_info_id' => 'required|exists:accreditation_infos,id',
            'level' => 'required|string',
            'programs' => 'required|array|min:1',
            'programs.*' => 'required|string'
        ]);

        DB::transaction(function () use ($request) {

            $level = AccreditationLevel::firstOrCreate([
                'level_name' => $request->level
            ]);

            foreach ($request->programs as $programName) {

                $program = Program::firstOrCreate([
                    'program_name' => $programName
                ]);

                InfoLevelProgramMapping::firstOrCreate([
                    'accreditation_info_id' => $request->accreditation_info_id,
                    'level_id' => $level->id,
                    'program_id' => $program->id,
                ]);
            }
        });

        return response()->json([
            'message' => 'Program(s) added successfully.'
        ], 200);
    }


    public function getAccreditations()
    {
        $user = auth()->user();
        $isAdmin = $user->user_type === UserType::ADMIN;
        $isDean = $user->user_type === UserType::DEAN;

        $levelOrder = [
            'PRELIMINARY' => 1,
            'LEVEL I' => 2,
            'LEVEL II' => 3,
            'LEVEL III' => 4,
            'LEVEL IV' => 5,
        ];

        if ($isAdmin || $isDean) {
            // Admin sees all mappings
            $mappings = InfoLevelProgramMapping::with([
                'accreditationInfo.accreditationBody',
                'level',
                'program'
            ])->get();
        } else {
            // Get the user’s assignments
            $assignments = AccreditationAssignment::where('user_id', $user->id)
                ->select('accred_info_id', 'program_id', 'level_id')
                ->distinct()
                ->get();

            if ($assignments->isEmpty()) {
                return response()->json([]); // no assignments
            }

            // Get the list of mapping IDs for filtering
            $mappings = InfoLevelProgramMapping::with([
                'accreditationInfo.accreditationBody',
                'level',
                'program'
            ])->where(function ($query) use ($assignments) {
                foreach ($assignments as $a) {
                    $query->orWhere(function ($q) use ($a) {
                        $q->where('accreditation_info_id', $a->accred_info_id)
                            ->where('program_id', $a->program_id)
                            ->where('level_id', $a->level_id);
                    });
                }
            })->get();
        }

        // Group by Accreditation Body
        $grouped = $mappings
            ->groupBy(fn($item) => $item->accreditationInfo->accreditation_body_id)
            ->map(function ($bodyItems) use ($levelOrder) {
                $body = $bodyItems->first()->accreditationInfo->accreditationBody;

                $bodyAccreditationInfos = $bodyItems
                    ->groupBy('accreditation_info_id')
                    ->map(function ($infoItems) use ($levelOrder) {
                        $accreditationInfo = $infoItems->first()->accreditationInfo;

                        $programs = $infoItems->map(function ($p) use ($levelOrder) {
                            return [
                                'name' => $p->program->program_name,
                                'level' => strtoupper(trim($p->level->level_name)),
                                'level_id' => $p->level->id,
                                'status' => $p->accreditationInfo->status
                            ];
                        })->sortBy(fn($p) => $levelOrder[$p['level']] ?? 999)
                            ->values();

                        return [
                            'id' => $accreditationInfo->id,
                            'title' => $accreditationInfo->title,
                            'year' => $accreditationInfo->year,
                            'status' => $accreditationInfo->status,
                            'programs' => $programs
                        ];
                    })->values();

                return [
                    'body_name' => $body->name,
                    'body_status' => 'Active',
                    'accreditation_infos' => $bodyAccreditationInfos
                ];
            })->values();

        return response()->json($grouped);
    }


    public function showProgram($infoId, $levelId, $programName)
    {
        $user = auth()->user();

        $isAdmin = $user->currentRole->name === UserType::ADMIN->value;
        $isDean  = $user->currentRole->name === UserType::DEAN->value;

        $levelName = AccreditationLevel::where('id', $levelId)->value('level_name');

        $program = InfoLevelProgramMapping::where([
            'accreditation_info_id' => $infoId,
            'level_id' => $levelId,
        ])->whereHas('program', function ($q) use ($programName) {
            $q->where('program_name', $programName);
        })->firstOrFail();

        // ================= ROLE IDs =================
        $roles = Role::whereIn('name', [
            UserType::INTERNAL_ASSESSOR->value,
            UserType::TASK_FORCE->value,
        ])->pluck('id', 'name');

        $iaRoleId = $roles[UserType::INTERNAL_ASSESSOR->value] ?? null;
        $tfRoleId = $roles[UserType::TASK_FORCE->value] ?? null;

        // ================= USERS TO SHOW =================
        if ($isAdmin) {
            $users = User::whereHas('roles', function ($q) {
                $q->where('name', UserType::INTERNAL_ASSESSOR);
            })->where('status', UserStatus::ACTIVE)->orderBy('name')->get();

        } elseif ($isDean) {
            $users = User::whereHas('roles', function ($q) {
                $q->where('name', UserType::TASK_FORCE);
            })->where('status', UserStatus::ACTIVE)->orderBy('name')->get();

        } else {
            $users = collect();
        }

        // ================= PROGRAM AREAS =================
        if ($isAdmin || $isDean) {

            $programAreas = ProgramAreaMapping::with([
                'users' => function ($q) use ($isAdmin, $isDean, $iaRoleId, $tfRoleId) {

                    if ($isAdmin) {
                        // Assigned by Admin: role_id = IA, role IS NULL
                        $q->wherePivot('role_id', $iaRoleId)
                        ->wherePivot('role', null);

                    } elseif ($isDean) {
                        // Assigned by Dean: role_id = TF, role IS NOT NULL (chair/member)
                        $q->wherePivot('role_id', $tfRoleId)
                        ->wherePivotNotNull('role');
                    }

                    $q->orderBy('name');
                }
            ])->where('info_level_program_mapping_id', $program->id)->get();

        } else {

            // ================= NON-ADMIN/DEAN =================
            $isInternalAssessor = $user->currentRole->name === UserType::INTERNAL_ASSESSOR->value;
            $isTaskForce        = $user->currentRole->name === UserType::TASK_FORCE->value;

            $assignedAreaIds = AccreditationAssignment::where([
                'user_id'        => $user->id,
                'accred_info_id' => $infoId,
                'level_id'       => $levelId,
                'program_id'     => $program->program_id,
            ])
            // Also filter by correct role_id + role null/not-null
            ->when($isInternalAssessor, fn($q) => $q->where('role_id', $iaRoleId)->whereNull('role'))
            ->when($isTaskForce,        fn($q) => $q->where('role_id', $tfRoleId)->whereNotNull('role'))
            ->pluck('area_id')
            ->unique()
            ->values();

            $programAreas = ProgramAreaMapping::with([
                'users' => function ($q) use ($isInternalAssessor, $isTaskForce, $iaRoleId, $tfRoleId) {

                    if ($isInternalAssessor) {
                        $q->wherePivot('role_id', $iaRoleId)
                        ->wherePivot('role', null);

                    } elseif ($isTaskForce) {
                        $q->wherePivot('role_id', $tfRoleId)
                        ->wherePivotNotNull('role');
                    }

                    $q->orderBy('name');
                }
            ])
            ->where('info_level_program_mapping_id', $program->id)
            ->whereIn('id', $assignedAreaIds)
            ->get();
        }

        return view('admin.accreditors.program', [
            'infoId'      => $infoId,
            'level'       => $levelName,
            'levelId'     => $levelId,
            'programName' => $programName,
            'programId'   => $program->program_id,
            'users'       => $users,
            'programAreas'=> $programAreas,
            'isAdmin'     => $isAdmin,
            'isDean'      => $isDean,
        ]);
    }

    public function getProgramAreas($programId)
    {
        $programAreas = ProgramAreaMapping::with('users', 'area')
            ->where('info_level_program_mapping_id', $programId)
            ->get()
            ->map(function ($pa) {
                return [
                    'id' => $pa->id,
                    'name' => $pa->area->area_name ?? 'N/A',
                    'users' => $pa->users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->toArray(),
                ];
            });

        return response()->json($programAreas);
    }


    public function saveAreas(Request $request, $programId)
    {
        \Log::info('saveAreas START', $request->all());

        DB::beginTransaction();

        try {

            /**
             * 1️⃣ GET EXISTING CONTEXT
             */
            $context = InfoLevelProgramMapping::where([
                'program_id' => $programId,
                'level_id' => $request->level_id,
                'accreditation_info_id' => $request->accreditation_info_id
            ])->first();

            if (!$context) {
                \Log::error('Context NOT FOUND', [
                    'program_id' => $programId,
                    'level_id' => $request->level_id,
                    'accreditation_info_id' => $request->accreditation_info_id
                ]);
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Program-Level-Accreditation mapping not found.'
                ], 404);
            }

            \Log::info('Context FOUND', [
                'context_id' => $context->id,
                'accreditation_info_id' => $context->accreditation_info_id,
                'level_id' => $context->level_id,
                'program_id' => $context->program_id
            ]);

            foreach ($request->areas as $areaData) {

                \Log::info('Processing Area', $areaData);

                /**
                 * 2️⃣ CREATE / GET AREA
                 */
                $area = Area::firstOrCreate([
                    'area_name' => $areaData['name']
                ]);

                \Log::info('Area OK', [
                    'area_id' => $area->id,
                    'area_name' => $area->area_name
                ]);

                /**
                 * 3️⃣ PROGRAM ↔ AREA MAPPING
                 */
                $programArea = ProgramAreaMapping::firstOrCreate([
                    'info_level_program_mapping_id' => $context->id,
                    'area_id' => $area->id
                ]);

                \Log::info('ProgramAreaMapping OK', [
                    'program_area_id' => $programArea->id,
                    'info_level_program_mapping_id' => $context->id,
                    'area_id' => $area->id
                ]);

                /**
                 * 4️⃣ CLEAR OLD ASSIGNMENTS
                 */
                AccreditationAssignment::where([
                    'accred_info_id' => $context->accreditation_info_id,
                    'level_id' => $context->level_id,
                    'program_id' => $context->program_id,
                    'area_id' => $programArea->id
                ])->delete();




                /**
                 * 5️⃣ ASSIGN USERS
                 */
                if (!empty($areaData['users'])) {
                    foreach ($areaData['users'] as $userId) {

                        $user = User::findOrFail($userId);
                        $currentRoleId = $user->currentRole->id;

                        // Prevent duplicate user in same area
                        $alreadyAssigned = AccreditationAssignment::where([
                            'user_id' => $userId,
                            'role_id' => $currentRoleId,
                            'accred_info_id' => $context->accreditation_info_id,
                            'level_id' => $context->level_id,
                            'program_id' => $context->program_id,
                            'area_id' => $programArea->id,
                        ])->exists();

                        if (!$alreadyAssigned) {
                            AccreditationAssignment::create([
                                'user_id' => $userId,
                                'role_id' => $currentRoleId,
                                'accred_info_id' => $context->accreditation_info_id,
                                'level_id' => $context->level_id,
                                'program_id' => $context->program_id,
                                'area_id' => $programArea->id,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            \Log::info('saveAreas SUCCESS');

            return response()->json([
                'success' => true,
                'message' => 'Areas & users saved successfully!'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('saveAreas FAILED', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed saving areas'
            ], 500);
        }
    }
    public function assignUsersToArea(Request $request)
    {
        \Log::info('assignUsersToArea START', $request->all());

        $request->validate([
            'area_id'                 => 'required|exists:program_area_mappings,id',
            'program_id'              => 'required|exists:programs,id',
            'level_id'                => 'required|exists:accreditation_levels,id',
            'accreditation_info_id'   => 'required|exists:accreditation_infos,id',
            'users'                   => 'sometimes|array',
        ]);

        DB::beginTransaction();

        try {
            $context = InfoLevelProgramMapping::where([
                'program_id'             => $request->program_id,
                'level_id'               => $request->level_id,
                'accreditation_info_id'  => $request->accreditation_info_id,
            ])->firstOrFail();

            $programArea = ProgramAreaMapping::where('id', $request->area_id)
                ->where('info_level_program_mapping_id', $context->id)
                ->firstOrFail();

            if (!empty($request->users)) {
                foreach ($request->users as $index => $userData) {
                    // Normalize input
                    if (is_numeric($userData)) {
                        $userId = $userData;
                        $providedRole = null;
                    } elseif (is_array($userData) && isset($userData['id'])) {
                        $userId = $userData['id'];
                        $providedRole = $userData['role'] ?? null;
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "Invalid user data at index {$index}. Must be numeric ID or object with 'id'."
                        ], 422);
                    }

                    $user = User::find($userId);
                    if (!$user) {
                        return response()->json([
                            'success' => false,
                            'message' => "User ID {$userId} not found."
                        ], 422);
                    }

                    // Ensure user has a current role
                    if (!$user->currentRole) {
                        return response()->json([
                            'success' => false,
                            'message' => "User {$user->name} does not have an active role."
                        ], 422);
                    }

                    $currentRoleId   = $user->currentRole->id;

                    $authUser = auth()->user();
                    $authRoleName = $authUser->currentRole?->name;

                    $taskForceRole = null;

                    // ================= DETERMINE role_id TO SAVE =================
                    // Use the role relevant to the assignment context,
                    // NOT the user's currentRole

                    if ($authRoleName === UserType::ADMIN->value) {
                        // Admin assigns Internal Assessors — find the IA role ID from the user's roles
                        $iaRole = Role::where('name', UserType::INTERNAL_ASSESSOR->value)->first();

                        // Verify this user actually HAS the internal assessor role
                        if (!$user->roles->contains('id', $iaRole->id)) {
                            return response()->json([
                                'success' => false,
                                'message' => "{$user->name} does not have the Internal Assessor role."
                            ], 422);
                        }

                        $currentRoleId = $iaRole->id; // ← always save IA role id
                        $taskForceRole = null;

                    } elseif ($authRoleName === UserType::DEAN->value) {
                        // Dean assigns Task Forces — find the TF role ID from the user's roles
                        $tfRole = Role::where('name', UserType::TASK_FORCE->value)->first();

                        // Verify this user actually HAS the task force role
                        if (!$user->roles->contains('id', $tfRole->id)) {
                            return response()->json([
                                'success' => false,
                                'message' => "{$user->name} does not have the Task Force role."
                            ], 422);
                        }

                        if (empty($providedRole)) {
                            return response()->json([
                                'success' => false,
                                'message' => "Dean must assign role as chair or member."
                            ], 422);
                        }

                        try {
                            $taskForceRole = TaskForceRole::from($providedRole);
                        } catch (\ValueError $e) {
                            return response()->json([
                                'success' => false,
                                'message' => "Invalid role '{$providedRole}'. Allowed: chair, member."
                            ], 422);
                        }

                        $currentRoleId = $tfRole->id; // ← always save TF role id

                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "You are not authorized to assign users."
                        ], 403);
                    }

                    // Chair uniqueness — use area_id correctly (it's program_area_mappings.id)
                    if ($taskForceRole === TaskForceRole::CHAIR) {
                        $existingChair = AccreditationAssignment::where([
                            'accred_info_id' => $context->accreditation_info_id,
                            'level_id'       => $context->level_id,
                            'program_id'     => $context->program_id,
                            'area_id'        => $programArea->id,
                            'role'           => TaskForceRole::CHAIR,
                            'role_id'        => $currentRoleId, // now correctly the TF role id
                        ])->exists();

                        if ($existingChair) {
                            return response()->json([
                                'success' => false,
                                'message' => 'This area already has a Chair for Task Force.'
                            ], 422);
                        }
                    }

                    // Prevent duplicate (same user, same role, same area)
                    $exists = AccreditationAssignment::where([
                        'user_id'          => $userId,
                        'role_id'          => $currentRoleId,
                        'accred_info_id'   => $context->accreditation_info_id,
                        'level_id'         => $context->level_id,
                        'program_id'       => $context->program_id,
                        'area_id'          => $programArea->id,
                    ])->exists();

                    if ($exists) {
                        return response()->json([
                            'success' => false,
                            'message' => "{$user->name} is already assigned to this area with the same role."
                        ], 422);
                    }

                    // Log the data before insert
                    \Log::info('Creating assignment:', [
                        'user_id'        => $userId,
                        'role_id'        => $currentRoleId,
                        'accred_info_id' => $context->accreditation_info_id,
                        'level_id'       => $context->level_id,
                        'program_id'     => $context->program_id,
                        'area_id'        => $programArea->id,
                        'role'           => $taskForceRole,
                    ]);

                    AccreditationAssignment::create([
                        'user_id'          => $userId,
                        'role_id'          => $currentRoleId,
                        'accred_info_id'   => $context->accreditation_info_id,
                        'level_id'         => $context->level_id,
                        'program_id'       => $context->program_id,
                        'area_id'          => $programArea->id,
                        'role'             => $taskForceRole,
                    ]);
                }
            }

            DB::commit();

            $assignedUsers = AccreditationAssignment::where([
                'accred_info_id' => $context->accreditation_info_id,
                'level_id'       => $context->level_id,
                'program_id'     => $context->program_id,
                'area_id'        => $programArea->id,
            ])->with('user')->get()->pluck('user');

            return response()->json([
                'success' => true,
                'message' => 'Users assigned successfully.',
                'area_id' => $programArea->id,
                'users'   => $assignedUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values(),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('assignUsersToArea FAILED', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed assigning users: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showParameters(
        int $infoId,
        int $levelId,
        int $programId,
        int $programAreaId
    ) {
        $user     = auth()->user();
        $roleName = $user->currentRole->name;

        $isAdmin            = $roleName === UserType::ADMIN->value;
        $isDean             = $roleName === UserType::DEAN->value;
        $isTaskForce        = $roleName === UserType::TASK_FORCE->value;
        $isInternalAssessor = $roleName === UserType::INTERNAL_ASSESSOR->value;
        $isAccreditor       = $roleName === UserType::ACCREDITOR->value;

        // ================= CONTEXT =================
        $context = InfoLevelProgramMapping::where([
            'accreditation_info_id' => $infoId,
            'level_id'              => $levelId,
            'program_id'            => $programId,
        ])->firstOrFail();

        // ================= PROGRAM AREA =================
        $programArea = ProgramAreaMapping::with([
            'area',
            'parameters.sub_parameters'
        ])
        ->where('id', $programAreaId)
        ->where('info_level_program_mapping_id', $context->id)
        ->firstOrFail();

        // ================= ROLE IDs =================
        $roles = Role::whereIn('name', [
            UserType::INTERNAL_ASSESSOR->value,
            UserType::TASK_FORCE->value,
        ])->pluck('id', 'name');

        $internalAssessorRoleId = $roles[UserType::INTERNAL_ASSESSOR->value] ?? null;
        $taskForceRoleId        = $roles[UserType::TASK_FORCE->value] ?? null;

        // ================= BASE QUERY =================
        $baseQuery = AccreditationAssignment::with(['user', 'role'])
            ->where([
                'accred_info_id' => $infoId,
                'level_id'       => $levelId,
                'program_id'     => $programId,
                'area_id'        => $programArea->area->id,
            ]);

        // ================= SPLIT BY ROLE (like showProgram) =================
        // Internal Assessors — shown to Admin
        $internalAssessors = (clone $baseQuery)
            ->where('role_id', $internalAssessorRoleId)
            ->get();

        // Task Forces — shown to Dean, sorted Chair first
        $taskForces = (clone $baseQuery)
            ->where('role_id', $taskForceRoleId)
            ->get()
            ->sortByDesc(fn ($a) => strtolower($a->role?->value ?? '') === 'chair');

        // ================= ROLE-BASED ASSIGNMENTS FOR VIEW =================
        if ($isAdmin) {
            $assignments = $internalAssessors;
        } elseif ($isDean || $isTaskForce) {
            $assignments = $taskForces;
        } else {
            $assignments = collect();
        }

        return view('admin.accreditors.parameter', [
            'infoId'             => $infoId,
            'levelId'            => $levelId,
            'programId'          => $programId,
            'programAreaId'      => $programAreaId,
            'context'            => $context,
            'programArea'        => $programArea,
            'assignments'        => $assignments,
            // Pass both sets separately so the blade can show
            // avatars for both groups regardless of viewer role
            'internalAssessors'  => $internalAssessors,
            'taskForces'         => $taskForces,
            'parameters'         => $programArea->parameters,
            'isAdmin'            => $isAdmin,
            'isDean'             => $isDean,
            'isTaskForce'        => $isTaskForce,
            'isIA'               => $isInternalAssessor,
            'isAccreditor'       => $isAccreditor,
            'loggedInUser'       => $user,
        ]);
    }

    public function storeParameters(Request $request, $programAreaMappingId)
    {
        // Validate the incoming request
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'parameters' => 'required|array|min:1',
            'parameters.*.name' => 'required|string|max:255',
            'parameters.*.sub_parameters.*' => 'nullable|string|max:255',
        ]);

        $parametersData = $request->input('parameters');

        DB::transaction(function () use ($parametersData, $programAreaMappingId, $request) {

            foreach ($parametersData as $paramData) {

                // Create the Parameter
                $parameter = Parameter::create([
                    'parameter_name' => $paramData['name'],
                    'area_id' => $request->input('area_id'),
                ]);

                // Map the Parameter to the Program Area
                $areaParamMapping = AreaParameterMapping::create([
                    'program_area_mapping_id' => $programAreaMappingId,
                    'parameter_id' => $parameter->id,
                ]);

                // If Sub-Parameters exist, create them and attach to mapping
                if (!empty($paramData['sub_parameters'])) {
                    foreach ($paramData['sub_parameters'] as $subName) {

                        // Skip empty sub-parameter names
                        if (trim($subName) === '')
                            continue;

                        $subParam = SubParameter::create([
                            'sub_parameter_name' => $subName,
                            'parameter_id' => $parameter->id,
                        ]);

                        // Attach sub-parameter to area mapping
                        $areaParamMapping->subParameters()->attach($subParam->id);
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Parameters & Sub-Parameters added successfully'
        ]);
    }

    public function subParameterUploads(
        SubParameter $subParameter,
        int $infoId,
        int $levelId,
        int $programId,
        int $programAreaId
    ) {
        $subParameter->load(['parameter', 'uploads.uploader']);

        return view('admin.accreditors.sub-param', [
            'subParameter' => $subParameter,
            'parameter' => $subParameter->parameter,
            'uploads' => $subParameter->uploads,

            // pass context forward
            'infoId' => $infoId,
            'levelId' => $levelId,
            'programId' => $programId,
            'programAreaId' => $programAreaId,
        ]);
    }


    public function storeSubParameterUploads(
        Request $request,
        SubParameter $subParameter,
        int $infoId,
        int $levelId,
        int $programId,
        int $programAreaId
    ) {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
        ]);

        $user = auth()->user();

        foreach ($request->file('files') as $file) {

            $path = $file->store(
                "accreditation_uploads/{$programAreaId}/{$subParameter->id}",
                'public'
            );

            AccreditationDocuments::create([
                'subparameter_id' => $subParameter->id,
                'parameter_id' => $subParameter->parameter_id,
                'area_id' => $programAreaId,
                'program_id' => $programId,
                'level_id' => $levelId,
                'accred_info_id' => $infoId,
                'upload_by' => Auth::id(),
                'role_id' => $user->currentRole->id,

                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Files uploaded successfully.');
    }

    public function destroySubParameterUpload(AccreditationDocuments $upload)
    {
        // Optional: authorization check
        // abort_if(Auth::id() !== $upload->upload_by && !Auth::user()->isAdmin(), 403);

        // Delete file from storage
        if (Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
        }
        
        // Delete database record
        $upload->delete();

        return back()->with('success', 'File deleted successfully.');
    }


    //INTERNAL ASSESSOR
    public function indexInternalAccessor()
    {
        $user = auth()->user();

        $isAdmin = $user->currentRole === UserType::ADMIN->value;
        $isInternalAssessor = $user->currentRole === UserType::INTERNAL_ASSESSOR->value;
        $isAccreditor = $user->currentRole === UserType::ACCREDITOR->value;

        $isAccreditationUI = true;
        $canEvaluate = $isAccreditor;

        $mappings = InfoLevelProgramMapping::with([
            'accreditationInfo',
            'level',
            'program',
            'programAreas'
        ])
        ->get();

        $data = [];

        foreach ($mappings as $mapping) {
            $levelName = $mapping->level->level_name;
            $program = $mapping->program;

            $totalAreas = $mapping->programAreas->count();

            // Count completed evaluations in accreditation_evaluations table
           $completedAreas = AccreditationEvaluation::where('accred_info_id', $mapping->accreditationInfo->id)
                ->where('level_id', $mapping->level->id)
                ->where('program_id', $program->id)
                ->where('status', 'finalized')
                ->pluck('area_id')
                ->unique()
                ->count();

            $progress = $totalAreas > 0
                ? round(($completedAreas / $totalAreas) * 100)
                : 0;

            // Determine status label
            $statusLabel = 'Pending';

            if ($progress >= 100) {
                $statusLabel = 'Completed';
            } elseif ($progress > 0) {
                $statusLabel = 'Ongoing';
            }

            if (!isset($data[$levelName])) {
                $data[$levelName] = [
                    'level_id' => $mapping->level->id,
                    'programs' => [],
                ];
            }

            $data[$levelName]['programs'][] = [
                'program_id' => $program->id,
                'program_name' => $program->program_name,
                'accreditation_id' => $mapping->accreditationInfo->id,
                'accreditation_title' => $mapping->accreditationInfo->title,
                'accreditation_status_label' => $statusLabel,
                'total_areas' => $totalAreas,
                'evaluated_areas' => $completedAreas,
                'progress' => $progress,
            ];
        }

        return view('admin.accreditors.internal-accessor', compact(
            'isAdmin',
            'isInternalAssessor',
            'isAccreditationUI',
            'canEvaluate',
            'data'
        ));
    }

    public function showProgramAreas(
        int $accreditationId,
        int $levelId,
        int $programId
    ) {
        $user = auth()->user();

        $isAdmin            = $user->currentRole->name === UserType::ADMIN->value;
        $isDean             = $user->currentRole->name === UserType::DEAN->value;
        $isInternalAssessor = $user->currentRole->name === UserType::INTERNAL_ASSESSOR->value;
        $isTaskForce        = $user->currentRole->name === UserType::TASK_FORCE->value;
        $isAccreditor       = $user->currentRole->name === UserType::ACCREDITOR->value;

        // ================= PROGRAM =================
        $program = Program::findOrFail($programId);

        // ================= CONTEXT =================
        $context = InfoLevelProgramMapping::where([
            'accreditation_info_id' => $accreditationId,
            'level_id'              => $levelId,
            'program_id'            => $programId,
        ])->firstOrFail();

        // ================= ROLE IDs =================
        $roles = Role::whereIn('name', [
            UserType::INTERNAL_ASSESSOR->value,
            UserType::TASK_FORCE->value,
        ])->pluck('id', 'name');

        $iaRoleId = $roles[UserType::INTERNAL_ASSESSOR->value] ?? null;
        $tfRoleId = $roles[UserType::TASK_FORCE->value] ?? null;

        // ================= BASE QUERY =================
        $programAreasQuery = ProgramAreaMapping::with([
            'area',

            'users' => function ($q) use ($isAdmin, $isDean, $isAccreditor, $isInternalAssessor, $isTaskForce, $iaRoleId, $tfRoleId) {

                if ($isAdmin || $isInternalAssessor || $isAccreditor) {
                    $q->wherePivot('role_id', $iaRoleId)
                    ->wherePivot('role', null);

                } elseif ($isDean || $isTaskForce || $isAccreditor) {
                    // Accreditor sees same view as Dean — Task Force assignments
                    $q->wherePivot('role_id', $tfRoleId)
                    ->wherePivotNotNull('role');
                }

                $q->orderBy('name');
            },

            'evaluations' => function ($q) {
                $q->latest()->limit(1);
            },

            'evaluations.files.uploader',
            'users.roles',
        ])
        ->where('info_level_program_mapping_id', $context->id);

        // ================= AREA VISIBILITY =================
        // Accreditor sees all areas like Dean — no filtering needed
        if (!$isAdmin && !$isDean && !$isAccreditor) {
            $programAreasQuery->whereHas('assignments', function ($q) use ($user, $isInternalAssessor, $isTaskForce, $iaRoleId, $tfRoleId) {

                $q->where('user_id', $user->id);

                if ($isInternalAssessor) {
                    $q->where('role_id', $iaRoleId)
                    ->whereNull('role');

                } elseif ($isTaskForce) {
                    $q->where('role_id', $tfRoleId)
                    ->whereNotNull('role');
                }
            });
        }

        $programAreas = $programAreasQuery->get();

        return view('admin.accreditors.internal-accessor-areas', [
            'programName'        => $program->program_name,
            'programAreas'       => $programAreas,
            'levelId'            => $levelId,
            'programId'          => $programId,
            'infoId'             => $accreditationId,
            'isInternalAssessor' => $isInternalAssessor,
            'isTaskForce'        => $isTaskForce,
            'isAdmin'            => $isAdmin,
            'isDean'             => $isDean,
            'isAccreditor'       => $isAccreditor,
        ]);
    }

    public function showAreaEvaluation(
        int $infoId,
        int $levelId,
        int $programId,
        int $programAreaId
    ) {
        // ================= AUTH USER =================
        $user = auth()->user();

        // ================= ROLE IDs =================
        $roles = Role::whereIn('name', [
            UserType::INTERNAL_ASSESSOR->value,
            UserType::TASK_FORCE->value,
        ])->pluck('id', 'name');

        $iaRoleId = $roles[UserType::INTERNAL_ASSESSOR->value] ?? null;
        $tfRoleId = $roles[UserType::TASK_FORCE->value] ?? null;

        // ================= CONTEXT VALIDATION =================
        $context = InfoLevelProgramMapping::where([
            'accreditation_info_id' => $infoId,
            'level_id'              => $levelId,
            'program_id'            => $programId,
        ])->firstOrFail();

        // ================= ACCESS CONTROL =================
        if ($user->currentRole->name === UserType::INTERNAL_ASSESSOR->value) {
            $isAssigned = ProgramAreaMapping::where('id', $programAreaId)
                ->where('info_level_program_mapping_id', $context->id)
                ->whereHas('assignments', function ($q) use ($user, $iaRoleId) {
                    $q->where('user_id', $user->id)
                    ->where('role_id', $iaRoleId)
                    ->whereNull('role');
                })
                ->exists();

            if (!$isAssigned) {
                abort(403, 'You are not assigned to this area.');
            }
        }

        // ================= PROGRAM AREA =================
        $programArea = ProgramAreaMapping::with([
            'area',

            'users' => function ($q) use ($user, $iaRoleId) {
                // Only Internal Assessors assigned to this area
                $q->wherePivot('role_id', $iaRoleId)
                ->wherePivot('role', null)
                ->orderByRaw('users.id = ? DESC', [$user->id])
                ->orderBy('name');
            },

            'parameters.sub_parameters',
        ])
        ->where('id', $programAreaId)
        ->where('info_level_program_mapping_id', $context->id)
        ->firstOrFail();

        // ================= PARAMETERS & SUBPARAMETERS =================
        $parameters = $programArea->parameters;

        $parametersArray = $parameters->map(function ($param) use ($infoId, $levelId, $programId, $programAreaId) {
            return [
                'id'   => $param->id,
                'name' => $param->parameter_name,
                'sub_parameters' => $param->sub_parameters->map(function ($sub) use ($infoId, $levelId, $programId, $programAreaId) {
                    return [
                        'id'           => $sub->id,
                        'name'         => $sub->sub_parameter_name,
                        'uploads_count' => $sub->uploads->count(),
                        'uploads_url'  => route('subparam.uploads.index', [
                            'subParameter'  => $sub->id,
                            'infoId'        => $infoId,
                            'levelId'       => $levelId,
                            'programId'     => $programId,
                            'programAreaId' => $programAreaId,
                        ]),
                    ];
                })->toArray(),
            ];
        })->toArray();

        $subparametersWithIds = $parameters->flatMap->sub_parameters->map(fn($sp) => [
            'id'   => $sp->id,
            'name' => $sp->sub_parameter_name,
        ]);

        // ================= EXISTING EVALUATION =================
        $currentUserEvaluation = AccreditationEvaluation::where([
            'accred_info_id' => $infoId,
            'level_id'       => $levelId,
            'program_id'     => $programId,
            'area_id'        => $programAreaId,
            'evaluated_by'   => $user->id,
        ])->with('subparameterRatings.ratingOption', 'areaRecommendations')->first();

        $isFinalized = $currentUserEvaluation && $currentUserEvaluation->status === EvaluationStatus::FINALIZED;
        $isSubmitted = $currentUserEvaluation && in_array($currentUserEvaluation->status, [
            EvaluationStatus::SUBMITTED,
            EvaluationStatus::UPDATED,
        ]);

        $readonly = $isFinalized || $isSubmitted;
        $locked   = $isFinalized;

        $initialEvaluations    = [];
        $initialRecommendation = '';

        if ($currentUserEvaluation) {
            foreach ($currentUserEvaluation->subparameterRatings as $rating) {
                $label  = $rating->ratingOption->label;
                $status = match ($label) {
                    'Available'                => 'available',
                    'Available but Inadequate' => 'inadequate',
                    'Not Available'            => 'not_available',
                    'Not Applicable'           => 'not_applicable',
                    default                    => null,
                };
                if ($status) {
                    $initialEvaluations[$rating->subparameter_id] = [
                        'status' => $status,
                        'score'  => $rating->score,
                    ];
                }
            }

            $rec = $currentUserEvaluation->areaRecommendations
                ->where('area_id', $programAreaId)
                ->first();
            $initialRecommendation = $rec->recommendation ?? '';
        }

        $isSubmittedOrUpdated = $currentUserEvaluation && in_array($currentUserEvaluation->status, [
            EvaluationStatus::SUBMITTED,
            EvaluationStatus::UPDATED,
        ]);

        $isAdmin            = $user->currentRole->name === UserType::ADMIN->value;
        $isInternalAssessor = $user->currentRole->name === UserType::INTERNAL_ASSESSOR->value;
        $isFinal            = $currentUserEvaluation?->status === EvaluationStatus::FINALIZED ?? false;

        return view('admin.accreditors.internal-accessor-parameter', compact(
            'infoId',
            'levelId',
            'programId',
            'programAreaId',
            'context',
            'programArea',
            'parameters',
            'parametersArray',
            'subparametersWithIds',
            'initialEvaluations',
            'initialRecommendation',
            'locked',
            'isSubmittedOrUpdated',
            'isFinal',
            'isAdmin',
            'isInternalAssessor',
            'readonly',
            'isSubmitted',
            'isFinalized'
        ));
    }
}
