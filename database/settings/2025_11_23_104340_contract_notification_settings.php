<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contract_notifications.enable_notifications', true);

        $this->migrator->add('contract_notifications.warning_threshold_50', 50);
        $this->migrator->add('contract_notifications.warning_threshold_75', 75);
        $this->migrator->add('contract_notifications.warning_threshold_90', 90);
        $this->migrator->add('contract_notifications.warning_threshold_100', 100);

        $this->migrator->add('contract_notifications.notify_roles', ['Administrator']);

        $this->migrator->add('contract_notifications.notify_on_warning_levels', true);
        $this->migrator->add('contract_notifications.notify_on_contract_completion', true);
    }

    public function down(): void
    {
        $this->migrator->delete('contract_notifications.enable_notifications');

        $this->migrator->delete('contract_notifications.warning_threshold_50');
        $this->migrator->delete('contract_notifications.warning_threshold_75');
        $this->migrator->delete('contract_notifications.warning_threshold_90');
        $this->migrator->delete('contract_notifications.warning_threshold_100');

        $this->migrator->delete('contract_notifications.notify_roles');

        $this->migrator->delete('contract_notifications.notify_on_warning_levels');
        $this->migrator->delete('contract_notifications.notify_on_contract_completion');
    }
};
