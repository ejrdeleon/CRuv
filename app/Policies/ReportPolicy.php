<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     * Administrators can view all reports. Residents can only view their own reports.
     */
    public function view(User $user, Report $report): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $report->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Resident can edit only while still in submitted state
        return $report->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can change report status.
     */
    public function changeStatus(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can assign report.
     */
    public function assign(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can add internal admin comments.
     */
    public function addInternalNote(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }
}
