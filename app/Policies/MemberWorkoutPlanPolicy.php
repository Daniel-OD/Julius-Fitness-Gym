<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MemberWorkoutPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberWorkoutPlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MemberWorkoutPlan');
    }

    public function view(AuthUser $authUser, MemberWorkoutPlan $memberWorkoutPlan): bool
    {
        return $authUser->can('View:MemberWorkoutPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MemberWorkoutPlan');
    }

    public function update(AuthUser $authUser, MemberWorkoutPlan $memberWorkoutPlan): bool
    {
        return $authUser->can('Update:MemberWorkoutPlan');
    }

    public function delete(AuthUser $authUser, MemberWorkoutPlan $memberWorkoutPlan): bool
    {
        return $authUser->can('Delete:MemberWorkoutPlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MemberWorkoutPlan');
    }

    public function restore(AuthUser $authUser, MemberWorkoutPlan $memberWorkoutPlan): bool
    {
        return $authUser->can('Restore:MemberWorkoutPlan');
    }

    public function forceDelete(AuthUser $authUser, MemberWorkoutPlan $memberWorkoutPlan): bool
    {
        return $authUser->can('ForceDelete:MemberWorkoutPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MemberWorkoutPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MemberWorkoutPlan');
    }

    public function replicate(AuthUser $authUser, MemberWorkoutPlan $memberWorkoutPlan): bool
    {
        return $authUser->can('Replicate:MemberWorkoutPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MemberWorkoutPlan');
    }

}