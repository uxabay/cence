<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Οι artisan εντολές που είναι διαθέσιμες στην εφαρμογή.
     *
     * Εδώ δηλώνεις custom commands που έχεις δημιουργήσει
     * π.χ. PermissionsRefreshCommand, ImportContractsCommand, κλπ.
     */
    protected $commands = [
        \App\Console\Commands\PermissionsRefreshCommand::class,
        // πρόσθεσε εδώ και άλλες custom commands όταν δημιουργηθούν
    ];

    /**
     * Εγγραφή custom commands και closure commands.
     *
     * @return void
     */
    protected function commands(): void
    {
        // Αυτό διαβάζει όλα τα commands από τον φάκελο app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        // Εναλλακτικά, μπορείς να καταχωρίσεις route-based console commands
        require base_path('routes/console.php');
    }

    /**
     * Ορισμός προγραμματισμένων (scheduled) tasks.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Παράδειγμα: εκτέλεση του PermissionsRefresh κάθε Κυριακή στις 2 π.μ.
        // $schedule->command('permissions:refresh --seed')->weeklyOn(0, '2:00');

        // Παράδειγμα: καθαρισμός παλιών logs ή queue jobs
        // $schedule->command('queue:prune-batches --hours=48')->daily();
    }
}
