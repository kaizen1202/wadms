<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleRequest;
use App\Enums\UserType;
use App\Enums\RoleRequestStatus;

class RoleRequestController extends Controller
{
    /**
     * Show pending role requests for the logged-in approver
     */
    public function index()
    {
        $user = auth()->user();

        $requests = RoleRequest::with(['user', 'role', 'approver'])
            ->where('status', RoleRequestStatus::PENDING)
            ->when($user->user_type === UserType::DEAN, function ($q) {
                // Dean approves only Task Force requests
                $q->whereHas('role', fn($r) => $r->where('slug', 'task_force'));
            })
            ->when($user->user_type === UserType::ADMIN, function ($q) {
                // Admin approves only Internal Assessor requests
                $q->whereHas('role', fn($r) => $r->where('slug', 'internal_assessor'));
            })
            ->get();

        return view('role-requests.index', compact('requests'));
    }

    /**
     * Store a new role request
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        // Already has role
        if ($user->roles()->where('roles.id', $data['role_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'You already have this role assigned.'], 422);
        }

        // Pending request exists
        if (RoleRequest::where('user_id', $user->id)
            ->where('role_id', $data['role_id'])
            ->where('status', RoleRequestStatus::PENDING)
            ->exists()) {
            return response()->json(['success' => false, 'message' => 'You already have a pending request for this role.'], 422);
        }

        // Create request
        RoleRequest::create([
            'user_id' => $user->id,
            'role_id' => $data['role_id'],
            'reason' => $data['reason'] ?? null,
            'status' => RoleRequestStatus::PENDING,
        ]);

        return response()->json(['success' => true, 'message' => 'Role request submitted successfully.']);
    }

    /**
     * DataTables JSON endpoint
     */
    public function data()
    {
        $user = auth()->user();

        $requests = RoleRequest::with([
                'user.roles', 
                'role'
            ])
            ->where('status', RoleRequestStatus::PENDING->value)
            ->when($user->user_type === UserType::DEAN, function ($q) {
                $q->whereHas('role', fn($r) => $r->where('slug', 'task_force'));
            })
            ->when($user->user_type === UserType::ADMIN, function ($q) {
                $q->whereHas('role', fn($r) => $r->where('slug', 'internal_assessor'));
            })
            ->get();

        return response()->json([
            'data' => $requests
        ]);
    }

    /**
     * Approve a role request
     */
    public function approve(RoleRequest $roleRequest)
    {
        $user = auth()->user();
        $isAdmin = $user->currentRole->name === UserType::ADMIN->value;
        $isDean = $user->currentRole->name === UserType::DEAN->value;

        if (
            ($isAdmin && $roleRequest->role->slug !== 'internal_assessor') ||
            ($isDean && $roleRequest->role->slug !== 'task_force')
        ) {
            abort(403, 'You are not authorized to approve this request.');
        }

        $roleRequest->status = RoleRequestStatus::APPROVED;
        $roleRequest->approved_by = $user->id;
        $roleRequest->approved_at = now();
        $roleRequest->save();

        $roleRequest->user->roles()->syncWithoutDetaching([$roleRequest->role_id]);

        // Return JSON for AJAX
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Role request approved successfully.'
            ]);
        }

        return back()->with('success', 'Role request approved.');
    }


    /**
     * Reject a role request
     */
    public function reject(RoleRequest $roleRequest)
    {
        $user = auth()->user();
        $isAdmin = $user->currentRole->name === UserType::ADMIN->value;
        $isDean = $user->currentRole->name === UserType::DEAN->value;

        if (
            ($isAdmin && $roleRequest->role->slug !== 'internal_assessor') ||
            ($isDean && $roleRequest->role->slug !== 'task_force')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reject this request.'
            ], 403);
        }

        $roleRequest->status = RoleRequestStatus::REJECTED;
        $roleRequest->approved_by = $user->id;
        $roleRequest->approved_at = now();
        $roleRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Role request rejected.'
        ]);
    }
}
