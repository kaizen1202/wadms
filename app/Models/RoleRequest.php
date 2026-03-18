<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\RoleRequestStatus;

class RoleRequest extends Model
{
    use HasFactory;

    protected $table = 'role_requests';

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'role_id',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    // Cast the status to enum
    protected $casts = [
        'status' => RoleRequestStatus::class,
        'approved_at' => 'datetime',
    ];

    // -------------------------
    // Relationships
    // -------------------------

    // User who requested
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Requested role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Admin who approved/rejected
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // -------------------------
    // Helper methods
    // -------------------------

    public function isPending(): bool
    {
        return $this->status === RoleRequestStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === RoleRequestStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === RoleRequestStatus::REJECTED;
    }
}
