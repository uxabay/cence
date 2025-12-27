<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CustomerCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_customer_category');
    }

    public function view(AuthUser $authUser, CustomerCategory $customerCategory): bool
    {
        return $authUser->can('view_customer_category');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_customer_category');
    }

    public function update(AuthUser $authUser, CustomerCategory $customerCategory): bool
    {
        return $authUser->can('update_customer_category');
    }

    public function delete(AuthUser $authUser, CustomerCategory $customerCategory): bool
    {
        return $authUser->can('delete_customer_category')
            && $customerCategory->canBeDeleted();
    }

    public function restore(AuthUser $authUser, CustomerCategory $customerCategory): bool
    {
        return $authUser->can('restore_customer_category');
    }

    public function forceDelete(AuthUser $authUser, CustomerCategory $customerCategory): bool
    {
        return $authUser->can('force_delete_customer_category')
            && $customerCategory->canBeDeleted();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_customer_category');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_customer_category');
    }

    public function replicate(AuthUser $authUser, CustomerCategory $customerCategory): bool
    {
        return $authUser->can('replicate_customer_category');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_customer_category');
    }

}
