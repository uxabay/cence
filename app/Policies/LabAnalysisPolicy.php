<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LabAnalysis;
use Illuminate\Auth\Access\HandlesAuthorization;

class LabAnalysisPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_lab_analysis');
    }

    public function view(AuthUser $authUser, LabAnalysis $labAnalysis): bool
    {
        return $authUser->can('view_lab_analysis');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_lab_analysis');
    }

    public function update(AuthUser $authUser, LabAnalysis $labAnalysis): bool
    {
        return $authUser->can('update_lab_analysis');
    }

    public function delete(AuthUser $authUser, LabAnalysis $labAnalysis): bool
    {
        return $authUser->can('delete_lab_analysis');
    }

    public function restore(AuthUser $authUser, LabAnalysis $labAnalysis): bool
    {
        return $authUser->can('restore_lab_analysis');
    }

    public function forceDelete(AuthUser $authUser, LabAnalysis $labAnalysis): bool
    {
        return $authUser->can('force_delete_lab_analysis');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_lab_analysis');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_lab_analysis');
    }

    public function replicate(AuthUser $authUser, LabAnalysis $labAnalysis): bool
    {
        return $authUser->can('replicate_lab_analysis');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_lab_analysis');
    }

}