<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $loggedInUser = auth()->user();

        $isAdmin = $loggedInUser->currentRole->name === UserType::ADMIN->value;
        $isDean  = $loggedInUser->currentRole->name === UserType::DEAN->value;

        $users = User::where('status', UserStatus::PENDING)->get();

        return view('admin.users.index', compact(
            'isAdmin',
            'isDean',
            'users'
        ));
    }


    public function data()
    {
        $viewer = auth()->user();

        $query = User::where('status', UserStatus::PENDING);

        match ($viewer->currentRole->name) {

            UserType::ADMIN->value => $query->whereIn('user_type', [
                UserType::INTERNAL_ASSESSOR,
                UserType::ACCREDITOR,
            ]),

            UserType::DEAN->value => $query->where('user_type', UserType::TASK_FORCE),

            default => $query->whereRaw('1 = 0'),
        };

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function suspend($id)
    {
        $user = User::findOrFail($id);
        $user->status = UserStatus::SUSPENDED;
        $user->save();

        return response()->json([
            'message' => 'User suspended successfully'
        ]);
    }

    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Activate user
        $user->status = UserStatus::ACTIVE;
        $user->save();

        // Get role based on user_type enum
        $roleSlug = str($user->user_type->value)->slug('_');

        $role = Role::where('slug', $roleSlug)->firstOrFail();
        
        // Attach role safely
        $user->roles()->syncWithoutDetaching([$role->id]);

        // Insert in current_role_id
        $user->current_role_id = $role->id;
        $user->save();

        return response()->json([
            'message' => 'User verified and role assigned successfully'
        ]);
    }
}
