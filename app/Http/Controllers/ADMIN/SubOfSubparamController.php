<?php

namespace App\Http\Controllers\ADMIN;

use App\Http\Controllers\Controller;
use App\Models\ADMIN\AccreditationDocuments;
use App\Models\ADMIN\AccreditationInfo;
use App\Models\ADMIN\SubparamSubSubparamMapping;
use App\Models\ADMIN\SubSubparameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubOfSubparamController extends Controller
{
    public function subSubParameterUploads(
        int $infoId,
        int $levelId,
        int $programId,
        int $programAreaId,
        int $subSubParameterId
    ) {
        $accreditationStatus = AccreditationInfo::where('id', $infoId)->value('status');
        $subSubParameter = SubSubparameter::findOrFail($subSubParameterId);

        $uploads = AccreditationDocuments::where([
            'sub_sub_parameter_id' => $subSubParameterId,
            'accred_info_id'       => $infoId,
            'level_id'             => $levelId,
            'program_id'           => $programId,
            'area_id'              => $programAreaId,
        ])->with(['uploader', 'uploaderRole'])->get();

        return view('admin.accreditors.sub-subparam', [
            'subSubParameter' => $subSubParameter,
            'uploads'         => $uploads,
            'accreditationStatus' => $accreditationStatus->value,
            'infoId'          => $infoId,
            'levelId'         => $levelId,
            'programId'       => $programId,
            'programAreaId'   => $programAreaId,
        ]);
    }

    public function storeSubSubParameterUploads(
        Request $request,
        int $infoId,
        int $levelId,
        int $programId,
        int $programAreaId,
        int $subSubParameterId
    ) {
        $request->validate([
            'files'   => 'required|array',
            'files.*' => 'file|mimes:pdf|max:10240',
        ]);

        $subSubParameter = SubSubparameter::findOrFail($subSubParameterId);
        $user            = auth()->user();

        foreach ($request->file('files') as $file) {

            $path = $file->store(
                "accreditation_uploads/{$programAreaId}/{$subSubParameterId}",
                'public'
            );

            AccreditationDocuments::create([
                'sub_sub_parameter_id' => $subSubParameterId,
                'subparameter_id'      => $subSubParameter->sub_parameter_id,
                'parameter_id'         => $subSubParameter->subParameter->parameter_id,
                'area_id'              => $programAreaId,
                'program_id'           => $programId,
                'level_id'             => $levelId,
                'accred_info_id'       => $infoId,
                'upload_by'            => Auth::id(),
                'role_id'              => $user->currentRole->id,
                'file_name'            => $file->getClientOriginalName(),
                'file_path'            => $path,
                'file_type'            => $file->getClientOriginalExtension(),
            ]);
        }

        return back()->with('success', 'Files uploaded successfully.');
    }

    public function destroySubSubParameterUpload(int $uploadId)
    {
        $upload = AccreditationDocuments::findOrFail($uploadId);

        // Only the uploader can delete
        if ($upload->upload_by !== Auth::id() || $upload->role_id !== auth()->user()->current_role_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
        }

        $upload->delete();

        return back()->with('success', 'File deleted successfully.');
    }
}
