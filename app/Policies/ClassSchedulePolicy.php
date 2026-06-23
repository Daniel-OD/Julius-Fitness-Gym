<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClassSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ClassSchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ClassSchedule');
    }

    public function view(AuthUser $authUser, ClassSchedule $classSchedule): bool
    {
        return $authUser->can('View:ClassSchedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ClassSchedule');
    }

    public function update(AuthUser $authUser, ClassSchedule $classSchedule): bool
    {
        return $authUser->can('Update:ClassSchedule');
    }

    public function delete(AuthUser $authUser, ClassSchedule $classSchedule): bool
    {
        return $authUser->can('Delete:ClassSchedule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ClassSchedule');
    }

    public function restore(AuthUser $authUser, ClassSchedule $classSchedule): bool
    {
        return $authUser->can('Restore:ClassSchedule');
    }

    public function forceDelete(AuthUser $authUser, ClassSchedule $classSchedule): bool
    {
        return $authUser->can('ForceDelete:ClassSchedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ClassSchedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ClassSchedule');
    }

    public function replicate(AuthUser $authUser, ClassSchedule $classSchedule): bool
    {
        return $authUser->can('Replicate:ClassSchedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ClassSchedule');
    }
}
