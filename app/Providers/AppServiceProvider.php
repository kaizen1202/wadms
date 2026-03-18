<?php

namespace App\Providers;

use App\Models\RoleRequest;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Enums\UserType;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('admin.layouts.sidebar', function ($view) {
            $user = auth()->user();
            $unverifiedCount = 0;
            $pendingRoleRequestCount = 0;

            if ($user?->user_type === UserType::ADMIN) {
                // Count of pending users (internal assessors & accreditors)
                $unverifiedCount = User::whereIn('user_type', [
                        UserType::INTERNAL_ASSESSOR,
                        UserType::ACCREDITOR,
                    ])
                    ->where('status', 'Pending')
                    ->count();

                // Count of pending role requests for Internal Assessor
                $pendingRoleRequestCount = RoleRequest::where('status', 'pending')
                    ->whereHas('role', function($q) {
                        $q->where('slug', 'internal_assessor');
                    })
                    ->count();
            }

            if ($user?->user_type === UserType::DEAN) {
                // Pending Task Force users
                $unverifiedCount = User::where('user_type', UserType::TASK_FORCE)
                    ->where('status', 'Pending')
                    ->count();

                // Pending role requests for Task Force
                $pendingRoleRequestCount = RoleRequest::where('status', 'pending')
                    ->whereHas('role', function($q) {
                        $q->where('slug', 'task_force');
                    })
                    ->count();
            }

            $view->with([
                'unverifiedCount' => $unverifiedCount,
                'pendingRoleRequestCount' => $pendingRoleRequestCount
            ]);
        });

        View::composer('admin.layouts.master', function ($view) {
            $user = auth()->user();

            if ($user) {
                // Get all roles the user has from pivot
                $roles = $user->roles; // Eager loaded via relationship

                // Current role
                $currentRole = $user->currentRole;

                // Roles available to switch to (from pivot) excluding current role
                $switchableRoles = $roles->filter(function ($role) use ($currentRole) {
                    return $role->id !== $currentRole?->id;
                });

                $view->with([
                    'switchableRoles' => $switchableRoles,
                    'currentRole' => $currentRole,
                    'user' => $user
                ]);
            }
        });
    }
}
