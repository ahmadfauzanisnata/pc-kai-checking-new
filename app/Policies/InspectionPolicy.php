<?php

namespace App\Policies;

use App\Enums\InspectionStatus;
use App\Models\Inspection;
use App\Models\User;

class InspectionPolicy
{
    /**
     * Determine whether the user can view any inspections.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the inspection.
     */
    public function view(User $user, Inspection $inspection): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $inspection->inspectionPeriod?->business_area_id === $user->business_area_id;
        }

        if ($user->isInspector()) {
            return $inspection->inspector_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can fill/edit the checklist.
     */
    public function fillChecklist(User $user, Inspection $inspection): bool
    {
        if (!$user->isInspector() || $inspection->inspector_id !== $user->id) {
            return false;
        }

        return in_array($inspection->status, [
            InspectionStatus::ASSIGNED,
            InspectionStatus::IN_PROGRESS,
            InspectionStatus::REVISION_REQUIRED,
        ]);
    }

    /**
     * Determine whether the user can verify the inspection.
     */
    public function verify(User $user, Inspection $inspection): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        if ($inspection->inspectionPeriod?->business_area_id !== $user->business_area_id) {
            return false;
        }

        return in_array($inspection->status, [
            InspectionStatus::PENDING_ADMIN_VERIFICATION,
            InspectionStatus::RESUBMITTED,
        ]);
    }

    /**
     * Determine whether the user can request revision.
     */
    public function requestRevision(User $user, Inspection $inspection): bool
    {
        return $this->verify($user, $inspection);
    }

    /**
     * Determine whether the user can submit verification to Super Admin.
     */
    public function submitApproval(User $user, Inspection $inspection): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        if ($inspection->inspectionPeriod?->business_area_id !== $user->business_area_id) {
            return false;
        }

        return in_array($inspection->status, [
            InspectionStatus::ADMIN_VERIFIED,
            InspectionStatus::ADMIN_REVISION_REQUIRED,
        ]);
    }

    /**
     * Determine whether the user can approve the inspection.
     */
    public function approve(User $user, Inspection $inspection): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return $inspection->status === InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL;
    }

    /**
     * Determine whether the user can return inspection to Admin.
     */
    public function returnToAdmin(User $user, Inspection $inspection): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return $inspection->status === InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL;
    }

    /**
     * Determine whether the user can lock the inspection.
     */
    public function lock(User $user, Inspection $inspection): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return $inspection->status === InspectionStatus::APPROVED;
    }

    /**
     * Determine whether the user can unlock the inspection.
     */
    public function unlock(User $user, Inspection $inspection): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return $inspection->status === InspectionStatus::LOCKED;
    }

    /**
     * Determine whether the user can archive the inspection.
     */
    public function archive(User $user, Inspection $inspection): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return $inspection->status === InspectionStatus::LOCKED;
    }
}
