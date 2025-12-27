<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LabCustomer;
use Illuminate\Auth\Access\HandlesAuthorization;

class LabCustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_lab_customer');
    }

    public function view(AuthUser $authUser, LabCustomer $labCustomer): bool
    {
        return $authUser->can('view_lab_customer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_lab_customer');
    }

    public function update(AuthUser $authUser, LabCustomer $labCustomer): bool
    {
        return $authUser->can('update_lab_customer');
    }

    public function delete(AuthUser $authUser, LabCustomer $labCustomer): bool
    {
        return $authUser->can('delete_lab_customer')
            && $labCustomer->canBeDeleted();
    }

    public function restore(AuthUser $authUser, LabCustomer $labCustomer): bool
    {
        return $authUser->can('restore_lab_customer');
    }

    public function forceDelete(AuthUser $authUser, LabCustomer $labCustomer): bool
    {
        return $authUser->can('force_delete_lab_customer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_lab_customer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_lab_customer');
    }

    public function replicate(AuthUser $authUser, LabCustomer $labCustomer): bool
    {
        return $authUser->can('replicate_lab_customer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_lab_customer');
    }

}
