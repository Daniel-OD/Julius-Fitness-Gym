<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GymClass;
use Illuminate\Auth\Access\HandlesAuthorization;

class GymClassPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GymClass');
    }

    public function view(AuthUser $authUser, GymClass $gymClass): bool
    {
        return $authUser->can('View:GymClass');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GymClass');
    }

    public function update(AuthUser $authUser, GymClass $gymClass): bool
    {
        return $authUser->can('Update:GymClass');
    }

    public function delete(AuthUser $authUser, GymClass $gymClass): bool
    {
        return $authUser->can('Delete:GymClass');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GymClass');
    }

    public function restore(AuthUser $authUser, GymClass $gymClass): bool
    {
        return $authUser->can('Restore:GymClass');
    }

    public function forceDelete(AuthUser $authUser, GymClass $gymClass): bool
    {
        return $authUser->can('ForceDelete:GymClass');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GymClass');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GymClass');
    }

    public function replicate(AuthUser $authUser, GymClass $gymClass): bool
    {
        return $authUser->can('Replicate:GymClass');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GymClass');
    }

}