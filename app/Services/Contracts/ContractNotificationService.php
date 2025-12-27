<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\Registration;
use App\Models\User;
use App\Settings\ContractNotificationSettings;
use App\Notifications\ContractThresholdWarningNotification;
use Illuminate\Support\Facades\DB;

class ContractNotificationService
{
    /**
     * Entry point – καλείται από το Registration model (booted or observer).
     */
    public function evaluateRegistration(Registration $registration): void
    {
        $contract = $registration->contract;

        if (!$contract) {
            return;
        }

        $settings = app(ContractNotificationSettings::class);

        if (!$settings->enable_notifications) {
            return;
        }

        // Υπολογισμός ποσοστού υλοποίησης
        $percentage = $contract->getProgressPercentage();

        // Threshold list
        $thresholds = [
            $settings->warning_threshold_50,
            $settings->warning_threshold_75,
            $settings->warning_threshold_90,
            $settings->warning_threshold_100,
        ];

        foreach ($thresholds as $threshold) {
            if ($percentage >= $threshold) {
                $this->triggerThresholdNotification($contract, $registration, $threshold, $percentage);
            }
        }
    }

    /**
     * Ελέγχει αν πρέπει να σταλεί ειδοποίηση για συγκεκριμένο threshold.
     */
    protected function triggerThresholdNotification(
        Contract $contract,
        Registration $registration,
        int $threshold,
        float $percentage
    ): void {

        $settings = app(ContractNotificationSettings::class);

        if (!$settings->notify_on_warning_levels) {
            return;
        }

        if ($this->notificationAlreadySent($contract->id, $threshold)) {
            return;
        }

        $this->sendThresholdNotification($contract, $threshold, $percentage, $registration);
    }

    /**
     * Έλεγχος εάν έχει σταλεί ήδη ειδοποίηση γι’ αυτό το threshold.
     */
    protected function notificationAlreadySent(int $contractId, int $threshold): bool
    {
        return DB::table('notifications')
            ->where('type', ContractThresholdWarningNotification::class)
            ->where('data->contract_id', $contractId)
            ->where('data->threshold', $threshold)
            ->exists();
    }

    /**
     * Αποστολή ειδοποίησης σε όλους όσους έχουν τα αντίστοιχα roles.
     */
    protected function sendThresholdNotification(
        Contract $contract,
        int $threshold,
        float $percentage,
        ?Registration $registration
    ): void {

        $settings = app(ContractNotificationSettings::class);

        $users = User::role($settings->notify_roles)->get();

        if ($users->isEmpty()) {
            return;
        }

        $payload = [
            'contract_id'     => $contract->id,
            'contract_title'  => $contract->title,
            'threshold'       => $threshold,
            'percentage'      => $percentage,
            'registration_id' => $registration?->id,
        ];

        foreach ($users as $user) {
            $user->notify(new ContractThresholdWarningNotification($payload));
        }
    }
}
