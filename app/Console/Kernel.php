<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Fetch Jobs From Indeed
        $schedule->command('scrapper:indeed --query=php --fromage=7')->hourly();
        $schedule->command('scrapper:indeed --query=php --fromage=7 --country=pk')->hourlyAt(5);
        $schedule->command('scrapper:indeed --query=laravel --fromage=7')->hourlyAt(20);
        $schedule->command('scrapper:indeed --query=laravel --fromage=7 --country=pk')->hourlyAt(40);

        // Fetch Job Details from Indeed
        $schedule->command('indeed:jobdetail')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
