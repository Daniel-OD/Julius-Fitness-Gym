<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ClassBooking;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClassBookingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ClassBooking');
    }

    public function view(AuthUser $authUser, ClassBooking $classBooking): bool
    {
        return $authUser->can('View:ClassBooking');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ClassBooking');
    }

    public function update(AuthUser $authUser, ClassBooking $classBooking): bool
    {
        return $authUser->can('Update:ClassBooking');
    }

    public function delete(AuthUser $authUser, ClassBooking $classBooking): bool
    {
        return $authUser->can('Delete:ClassBooking');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ClassBooking');
    }

    public function restore(AuthUser $authUser, ClassBooking $classBooking): bool
    {
        return $authUser->can('Restore:ClassBooking');
    }

    public function forceDelete(AuthUser $authUser, ClassBooking $classBooking): bool
    {
        return $authUser->can('ForceDelete:ClassBooking');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ClassBooking');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ClassBooking');
    }

    public function replicate(AuthUser $authUser, ClassBooking $classBooking): bool
    {
        return $authUser->can('Replicate:ClassBooking');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ClassBooking');
    }

}