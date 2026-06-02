<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CheckIn;
use Illuminate\Auth\Access\HandlesAuthorization;

class CheckInPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CheckIn');
    }

    public function view(AuthUser $authUser, CheckIn $checkIn): bool
    {
        return $authUser->can('View:CheckIn');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CheckIn');
    }

    public function update(AuthUser $authUser, CheckIn $checkIn): bool
    {
        return $authUser->can('Update:CheckIn');
    }

    public function delete(AuthUser $authUser, CheckIn $checkIn): bool
    {
        return $authUser->can('Delete:CheckIn');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CheckIn');
    }

    public function restore(AuthUser $authUser, CheckIn $checkIn): bool
    {
        return $authUser->can('Restore:CheckIn');
    }

    public function forceDelete(AuthUser $authUser, CheckIn $checkIn): bool
    {
        return $authUser->can('ForceDelete:CheckIn');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CheckIn');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CheckIn');
    }

    public function replicate(AuthUser $authUser, CheckIn $checkIn): bool
    {
        return $authUser->can('Replicate:CheckIn');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CheckIn');
    }

}