<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContractSampleCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContractSampleCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_contract_sample_category');
    }

    public function view(AuthUser $authUser, ContractSampleCategory $contract): bool
    {
        return $authUser->can('view_contract_sample_category');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_contract_sample_category');
    }

    public function update(AuthUser $authUser, ContractSampleCategory $contract): bool
    {
        return $authUser->can('update_contract_sample_category');
    }

    public function delete(AuthUser $authUser, ContractSampleCategory $contract): bool
    {
        return $authUser->can('delete_contract_sample_category');
    }

    public function restore(AuthUser $authUser, ContractSampleCategory $contract): bool
    {
        return $authUser->can('restore_contract_sample_category');
    }

    public function forceDelete(AuthUser $authUser, ContractSampleCategory $contract): bool
    {
        return $authUser->can('force_delete_contract_sample_category');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_contract_sample_category');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_contract_sample_category');
    }

    public function replicate(AuthUser $authUser, ContractSampleCategory $contract): bool
    {
        return $authUser->can('replicate_contract_sample_category');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_contract_sample_category');
    }

}
