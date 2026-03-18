<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SwitchRoleController extends Controller
{
   public function switch(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = auth()->user();

        // Check if user actually has this role in pivot
        $hasRole = $user->roles()
            ->where('roles.id', $request->role_id)
            ->exists();

        if (!$hasRole) {
            abort(403, 'You are not allowed to switch to this role.');
        }

        // Update current role
        $user->update([
            'current_role_id' => $request->role_id,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Role switched successfully.');
    }
}
