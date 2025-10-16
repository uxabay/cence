<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LabSampleCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class LabSampleCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_lab_sample_category');
    }

    public function view(AuthUser $authUser, LabSampleCategory $labSampleCategory): bool
    {
        return $authUser->can('view_lab_sample_category');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_lab_sample_category');
    }

    public function update(AuthUser $authUser, LabSampleCategory $labSampleCategory): bool
    {
        return $authUser->can('update_lab_sample_category');
    }

    public function delete(AuthUser $authUser, LabSampleCategory $labSampleCategory): bool
    {
        return $authUser->can('delete_lab_sample_category');
    }

    public function restore(AuthUser $authUser, LabSampleCategory $labSampleCategory): bool
    {
        return $authUser->can('restore_lab_sample_category');
    }

    public function forceDelete(AuthUser $authUser, LabSampleCategory $labSampleCategory): bool
    {
        return $authUser->can('force_delete_lab_sample_category');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_lab_sample_category');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_lab_sample_category');
    }

    public function replicate(AuthUser $authUser, LabSampleCategory $labSampleCategory): bool
    {
        return $authUser->can('replicate_lab_sample_category');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_lab_sample_category');
    }

}