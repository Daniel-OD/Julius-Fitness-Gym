<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WorkoutTemplate;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkoutTemplatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WorkoutTemplate');
    }

    public function view(AuthUser $authUser, WorkoutTemplate $workoutTemplate): bool
    {
        return $authUser->can('View:WorkoutTemplate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WorkoutTemplate');
    }

    public function update(AuthUser $authUser, WorkoutTemplate $workoutTemplate): bool
    {
        return $authUser->can('Update:WorkoutTemplate');
    }

    public function delete(AuthUser $authUser, WorkoutTemplate $workoutTemplate): bool
    {
        return $authUser->can('Delete:WorkoutTemplate');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WorkoutTemplate');
    }

    public function restore(AuthUser $authUser, WorkoutTemplate $workoutTemplate): bool
    {
        return $authUser->can('Restore:WorkoutTemplate');
    }

    public function forceDelete(AuthUser $authUser, WorkoutTemplate $workoutTemplate): bool
    {
        return $authUser->can('ForceDelete:WorkoutTemplate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WorkoutTemplate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WorkoutTemplate');
    }

    public function replicate(AuthUser $authUser, WorkoutTemplate $workoutTemplate): bool
    {
        return $authUser->can('Replicate:WorkoutTemplate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WorkoutTemplate');
    }

}