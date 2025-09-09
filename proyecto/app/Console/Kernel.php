<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define los comandos programados de la aplicación.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Aquí registras tu comando personalizado
        $schedule->command('consumo:analizar')->hourly();
    }

    /**
     * Registra los comandos Artisan de la aplicación.
     */
    protected function commands(): void
    {
        // Carga automáticamente los comandos en app/Console/Commands
        $this->load(__DIR__.'/Commands');

        // Opcional: puedes definir comandos adicionales en este archivo
        require base_path('routes/console.php');
    }
}
