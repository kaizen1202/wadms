<?php

namespace App\Http\Controllers\ADMIN;

use App\Http\Controllers\Controller;
use App\Models\ADMIN\AccreditationAssignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    // Delete Assignment (Improve soon as soft delete)
    public function destroy(AccreditationAssignment $assignment)
    {
        $userName = $assignment->user->name;
        $assignment->delete();

        return redirect()->back()
            ->with('success', "{$userName} has been unassigned successfully.");
    }
}
