<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LabAnalysisPackage;
use Illuminate\Auth\Access\HandlesAuthorization;

class LabAnalysisPackagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_lab_analysis_package');
    }

    public function view(AuthUser $authUser, LabAnalysisPackage $labAnalysisPackage): bool
    {
        return $authUser->can('view_lab_analysis_package');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_lab_analysis_package');
    }

    public function update(AuthUser $authUser, LabAnalysisPackage $labAnalysisPackage): bool
    {
        return $authUser->can('update_lab_analysis_package');
    }

    public function delete(AuthUser $authUser, LabAnalysisPackage $labAnalysisPackage): bool
    {
        return $authUser->can('delete_lab_analysis_package');
    }

    public function restore(AuthUser $authUser, LabAnalysisPackage $labAnalysisPackage): bool
    {
        return $authUser->can('restore_lab_analysis_package');
    }

    public function forceDelete(AuthUser $authUser, LabAnalysisPackage $labAnalysisPackage): bool
    {
        return $authUser->can('force_delete_lab_analysis_package');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_lab_analysis_package');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_lab_analysis_package');
    }

    public function replicate(AuthUser $authUser, LabAnalysisPackage $labAnalysisPackage): bool
    {
        return $authUser->can('replicate_lab_analysis_package');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_lab_analysis_package');
    }

}