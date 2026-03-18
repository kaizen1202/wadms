<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('roles');

        // Roles user already has
        $ownedRoleIds = $user->roles->pluck('id');

        // Roles already requested & pending
        $pendingRoleIds = $user->roleRequests()
            ->where('status', 'pending')
            ->pluck('role_id');

        // Base allowed roles
        $allowedRoleSlugs = [];

        // Determine what roles they can request
        switch ($user->currentRole->slug ?? null) {
            case 'internal_assessor':
                $allowedRoleSlugs = ['task_force'];
                break;

            case 'task_force':
                $allowedRoleSlugs = ['internal_assessor'];
                break;

            case 'dean':
                $allowedRoleSlugs = ['internal_assessor', 'task_force'];
                break;

            default:
                $allowedRoleSlugs = [];
        }

        // Filter out roles the user already has or already requested
        $requestableRoles = Role::whereIn('slug', $allowedRoleSlugs)
            ->whereNotIn('id', $ownedRoleIds)
            ->whereNotIn('id', $pendingRoleIds)
            ->get();

        return view('admin.users.profile', compact(
            'user',
            'requestableRoles'
        ));
    }
        
        /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
