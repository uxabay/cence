<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Δίνουμε πάντα full access στους Administrators.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole('Administrator') ? true : null;
    }

    /**
     * Εμφάνιση λίστας χρηστών.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_user') || $user->can('manage_users');
    }

    /**
     * Προβολή συγκεκριμένου χρήστη.
     */
    public function view(User $user, User $target): bool
    {
        // Δεν επιτρέπεται να βλέπει άλλον Admin αν δεν είναι Admin
        if ($target->hasRole('Administrator') && !$user->hasRole('Administrator')) {
            return false;
        }

        return $user->can('view_user') || $user->can('manage_users');
    }

    /**
     * Δημιουργία νέου χρήστη.
     */
    public function create(User $user): bool
    {
        return $user->can('create_user') || $user->can('manage_users');
    }

    /**
     * Επεξεργασία υπάρχοντος χρήστη.
     */
    public function update(User $user, User $target): bool
    {
        // Κανόνας ασφαλείας: non-admin δεν επεξεργάζεται Admin
        if ($target->hasRole('Administrator') && !$user->hasRole('Administrator')) {
            return false;
        }

        return $user->can('update_user') || $user->can('manage_users');
    }

    /**
     * Διαγραφή (soft delete) χρήστη.
     */
    public function delete(User $user, User $target): bool
    {
        // Δεν επιτρέπεται διαγραφή Admin
        if ($target->hasRole('Administrator')) {
            return false;
        }

        return $user->can('delete_user') || $user->can('manage_users');
    }

    /**
     * Επαναφορά (restore) χρήστη.
     */
    public function restore(User $user, User $target): bool
    {
        return $user->can('restore_user') || $user->can('manage_users');
    }

    /**
     * Οριστική διαγραφή (force delete).
     */
    public function forceDelete(User $user, User $target): bool
    {
        // Κανένας, εκτός Admin
        if (!$user->hasRole('Administrator')) {
            return false;
        }

        return $user->can('force_delete_user') || $user->can('manage_users');
    }

    /**
     * Εισαγωγή (import) χρηστών.
     */
    public function import(User $user): bool
    {
        return $user->can('import_user') || $user->can('manage_users');
    }

    /**
     * Εξαγωγή (export) χρηστών.
     */
    public function export(User $user): bool
    {
        return $user->can('export_user') || $user->can('manage_users');
    }
}
