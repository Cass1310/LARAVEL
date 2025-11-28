<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Backup de la base de datos diariamente a las 2:00 AM
        $schedule->command('backup:run --only-db')->dailyAt('02:00');
        
        // Backup completo los domingos a las 3:00 AM
        $schedule->command('backup:run')->weeklyOn(0, '03:00');
        
        // Limpiar backups antiguos los lunes a las 4:00 AM
        $schedule->command('backup:clean')->weeklyOn(1, '04:00');
        
        // Monitoreo de backups los viernes a las 5:00 AM
        $schedule->command('backup:monitor')->weeklyOn(5, '05:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}