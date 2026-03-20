<?php

namespace App\Http\Controllers;

use App\Enums\AccreditationStatus;
use App\Enums\UserType;
use App\Models\ADMIN\AccreditationInfo;
use App\Models\ADMIN\InfoLevelProgramMapping;
use App\Models\ADMIN\Parameter;
use App\Models\AccreditationEvaluation;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    /* =========================================================
     | GATE – Only Admin and Dean
     ========================================================= */
    private function authorizeArchiver(): void
    {
        $role = auth()->user()->currentRole->name;

        if (!in_array($role, [
            UserType::ADMIN->value,
            UserType::DEAN->value,
        ])) {
            abort(403, 'Only Admins and Deans can access the archive.');
        }
    }

    /* =========================================================
     | INDEX – Folder landing
     ========================================================= */
    public function index()
    {
        $this->authorizeArchiver();

        $completedCount = AccreditationInfo::where('status', AccreditationStatus::COMPLETED)->count();

        $deletedCount = InfoLevelProgramMapping::onlyTrashed()->count();

        return view('admin.accreditors.archive', compact(
            'completedCount',
            'deletedCount'
        ));
    }

    /* =========================================================
     | COMPLETED – List all completed accreditations
     ========================================================= */
    public function completed()
    {
        $this->authorizeArchiver();

        $accreditations = AccreditationInfo::where('status', AccreditationStatus::COMPLETED)
            ->with([
                'accreditationBody',
                'infoLevelProgramMappings.level',
                'infoLevelProgramMappings.program',
                'infoLevelProgramMappings.programAreas',
            ])
            ->orderByDesc('year')
            ->orderByDesc('accreditation_date')
            ->get();

        return view('admin.accreditors.archive-complete', compact('accreditations'));
    }

    /* =========================================================
     | DELETED – Soft-deleted programs (withdrawn)
     ========================================================= */
    public function deleted()
    {
        $this->authorizeArchiver();

        $deletedPrograms = InfoLevelProgramMapping::onlyTrashed()
            ->with([
                'accreditationInfo',
                'level',
                'program',
                'deletedBy',
            ])
            ->latest('deleted_at')
            ->get();

        return view('admin.accreditors.archive-deleted', compact('deletedPrograms'));
    }

    /* =========================================================
     | SHOW – Full detail of one completed accreditation
     ========================================================= */
    public function show(AccreditationInfo $accreditation)
    {
        $this->authorizeArchiver();

        // Must be completed to be viewable in archive
        abort_if(
            $accreditation->status !== AccreditationStatus::COMPLETED,
            404,
            'This accreditation is not in the archive.'
        );

        // Programs → areas
        $mappings = InfoLevelProgramMapping::where('accreditation_info_id', $accreditation->id)
            ->with([
                'level',
                'program',
                'programAreas.area',
            ])
            ->get();

        // Collect all area_ids under this accreditation
        $areaIds = $mappings
            ->flatMap(fn ($m) => $m->programAreas->pluck('area_id'))
            ->unique()
            ->values();

        // Parameters + sub-params + sub-sub-params scoped to those areas
        $parameters = Parameter::with([
            'sub_parameters.subSubParameters',
            'sub_parameters.uploads',
        ])
        ->whereIn('area_id', $areaIds)
        ->get();

        // Evaluations + ratings + recommendations
        $evaluations = AccreditationEvaluation::where('accred_info_id', $accreditation->id)
            ->with([
                'evaluator',
                'area',
                'subparameterRatings.ratingOption',
                'subparameterRatings.subparameter',
                'subSubParameterRatings.ratingOption',
                'areaRecommendations',
            ])
            ->get();

        return view('admin.accreditors.archive-show', compact(
            'accreditation',
            'mappings',
            'parameters',
            'evaluations',
        ));
    }

    /* =========================================================
     | MARK AS COMPLETED – POST from accreditation show page
     ========================================================= */
    public function markCompleted(AccreditationInfo $accreditation)
    {
        $this->authorizeArchiver();

        if ($accreditation->status === AccreditationStatus::COMPLETED) {
            return back()->with('error', 'This accreditation is already marked as completed.');
        }

        $accreditation->update(['status' => AccreditationStatus::COMPLETED]);

        return redirect()
            ->route('archive.completed')
            ->with('success', "{$accreditation->title} has been marked as completed and moved to the archive.");
    }
}