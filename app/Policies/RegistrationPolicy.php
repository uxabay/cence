<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Registration;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_registration');
    }

    public function view(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->can('view_registration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_registration');
    }

    public function update(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->can('update_registration');
    }

    public function delete(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->can('delete_registration');
    }

    public function restore(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->can('restore_registration');
    }

    public function forceDelete(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->can('force_delete_registration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_registration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_registration');
    }

    public function replicate(AuthUser $authUser, Registration $registration): bool
    {
        return $authUser->can('replicate_registration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_registration');
    }

}