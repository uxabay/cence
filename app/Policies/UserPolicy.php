<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Προτεραιότητα: αν είναι Super Admin, έχει όλα τα δικαιώματα.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }
    }

    /**
     * Εμφάνιση όλων των χρηστών.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.viewAny');
    }

    /**
     * Προβολή συγκεκριμένου χρήστη.
     */
    public function view(User $user, User $model): bool
    {
        // Επιτρέπει να βλέπει τον εαυτό του ή αν έχει δικαίωμα.
        return $user->id === $model->id || $user->can('users.view');
    }

    /**
     * Δημιουργία νέου χρήστη.
     */
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    /**
     * Ενημέρωση στοιχείων χρήστη.
     */
    public function update(User $user, User $model): bool
    {
        // Επιτρέπει ενημέρωση του εαυτού του ή αν έχει δικαίωμα.
        return $user->id === $model->id || $user->can('users.update');
    }

    /**
     * Διαγραφή χρήστη.
     */
    public function delete(User $user, User $model): bool
    {
        // Δεν μπορεί να διαγράψει τον εαυτό του.
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('users.delete');
    }

    /**
     * Επαναφορά (soft deleted).
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('users.restore');
    }

    /**
     * Οριστική διαγραφή (force delete).
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('users.forceDelete');
    }
}
