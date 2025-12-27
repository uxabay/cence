<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContractNotificationSettings extends Settings
{
    /**
     * --------------------------------------
     *  GENERAL FLAGS
     * --------------------------------------
     */

    // Κεντρικό flag για όλες τις ειδοποιήσεις
    public bool $enable_notifications;

    /**
     * --------------------------------------
     *  THRESHOLD SETTINGS
     * --------------------------------------
     */

    public int $warning_threshold_50;
    public int $warning_threshold_75;
    public int $warning_threshold_90;
    public int $warning_threshold_100;

    /**
     * --------------------------------------
     *  GLOBAL RECIPIENTS
     * --------------------------------------
     */

    // Λίστα ρόλων που θα λαμβάνουν όλες τις ειδοποιήσεις
    public array $notify_roles;

    /**
     * --------------------------------------
     *  EVENT TYPES (simple version)
     * --------------------------------------
     */

    // Ενεργοποίηση ειδοποιήσεων για τα warning levels (50/75/90/100)
    public bool $notify_on_warning_levels;

    // Ειδοποίηση όταν μια σύμβαση έχει ολοκληρώσει πλήρως το budget/δείγματα
    public bool $notify_on_contract_completion;

    /**
     * --------------------------------------
     *  SETTINGS GROUP
     * --------------------------------------
     */

    public static function group(): string
    {
        return 'contract_notifications';
    }
}

