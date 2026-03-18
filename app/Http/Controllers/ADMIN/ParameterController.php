<?php

namespace App\Http\Controllers\ADMIN;

use App\Http\Controllers\Controller;
use App\Models\ADMIN\AreaParameterMapping;
use App\Models\ADMIN\Parameter;
use App\Models\ADMIN\ParameterSubparameterMapping;
use App\Models\ADMIN\SubParameter;
use Illuminate\Http\Request;

class ParameterController extends Controller
{
    // ===================== BULK UPDATE PARAMATER =====================
    public function bulkUpdate(Request $request)
    {
        foreach ($request->parameters as $id => $name) {
            Parameter::where('id', $id)
                ->update(['parameter_name' => $name]);
        }

        return response()->json([
            'message' => 'Parameters updated successfully.'
        ]);
    }

    // ===================== BULK DELETE PARAMETER =====================
    public function bulkDelete(Request $request)
    {
        $programAreaId = $request->area_id;

        if ($request->parameters && $programAreaId) {
            AreaParameterMapping::where('program_area_mapping_id', $programAreaId)
                ->whereIn('parameter_id', $request->parameters)
                ->delete();
        }

        return response()->json([
            'message' => 'Selected parameters unassigned from this area.'
        ]);
    }

    // ===================== UPDATE SUB-PARAMETER =====================
    public function updateSubParameter(Request $request, $subParameterId)
    {
        $request->validate([
            'sub_parameter_name' => 'required|string|max:255',
        ]);

        $subParam = SubParameter::findOrFail($subParameterId);
        $subParam->sub_parameter_name = $request->sub_parameter_name;
        $subParam->save();

        return response()->json([
            'message' => 'Sub-parameter updated successfully.'
        ]);
    }

    // ===================== DELETE SUB-PARAMETER =====================
    public function deleteSubParameter($subParameterId)
    {
        $subParam = SubParameter::findOrFail($subParameterId);

        // Delete related mappings first
        ParameterSubparameterMapping::where('subparameter_id', $subParameterId)->delete();

        // Then delete the sub-parameter itself
        $subParam->delete();

        return response()->json([
            'message' => 'Sub-parameter and its mappings deleted successfully.'
        ]);
    }
}
