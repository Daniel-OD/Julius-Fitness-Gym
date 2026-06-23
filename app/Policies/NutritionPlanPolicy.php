<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NutritionPlan;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class NutritionPlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NutritionPlan');
    }

    public function view(AuthUser $authUser, NutritionPlan $nutritionPlan): bool
    {
        return $authUser->can('View:NutritionPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NutritionPlan');
    }

    public function update(AuthUser $authUser, NutritionPlan $nutritionPlan): bool
    {
        return $authUser->can('Update:NutritionPlan');
    }

    public function delete(AuthUser $authUser, NutritionPlan $nutritionPlan): bool
    {
        return $authUser->can('Delete:NutritionPlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:NutritionPlan');
    }

    public function restore(AuthUser $authUser, NutritionPlan $nutritionPlan): bool
    {
        return $authUser->can('Restore:NutritionPlan');
    }

    public function forceDelete(AuthUser $authUser, NutritionPlan $nutritionPlan): bool
    {
        return $authUser->can('ForceDelete:NutritionPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NutritionPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NutritionPlan');
    }

    public function replicate(AuthUser $authUser, NutritionPlan $nutritionPlan): bool
    {
        return $authUser->can('Replicate:NutritionPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NutritionPlan');
    }
}
