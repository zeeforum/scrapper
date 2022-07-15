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
        $schedule->command('scrapper:indeed --query=php --fromage=7')->daily();
        $schedule->command('scrapper:indeed --query=laravel --fromage=7')->dailyAt('00:10');
        $schedule->command('scrapper:indeed --query=react --fromage=7')->dailyAt('00:15');
        $schedule->command('scrapper:indeed --query=vue --fromage=7')->dailyAt('00:20');
        $schedule->command('scrapper:indeed --query=vuejs --fromage=7')->dailyAt('00:25');
        $schedule->command('scrapper:indeed --query=reactnative --fromage=7')->dailyAt('00:30');
        $schedule->command('scrapper:indeed --query=flutter --fromage=7')->dailyAt('00:35');

        $countries_arr = [
            '01' => 'pk', // Pakistan
            '02' => 'se', // Sweden
            '03' => 'au', // Austrailia
            '04' => 'gb', // United Kingdom
            '05' => 'ca', // Canada
        ];
        
        if ($countries_arr) {
            foreach ($countries_arr as $time => $country_code) {
                $schedule->command('scrapper:indeed --query=php --fromage=7 --country=' . $country_code)->dailyAt($time . ':05');
                $schedule->command('scrapper:indeed --query=laravel --fromage=7 --country=' . $country_code)->dailyAt($time . ':10');
                $schedule->command('scrapper:indeed --query=react --fromage=7 --country=' . $country_code)->dailyAt($time . ':15');
                $schedule->command('scrapper:indeed --query=vue --fromage=7 --country=' . $country_code)->dailyAt($time . ':20');
                $schedule->command('scrapper:indeed --query=vuejs --fromage=7 --country=' . $country_code)->dailyAt($time . ':25');
                $schedule->command('scrapper:indeed --query=reactnative --fromage=7 --country=' . $country_code)->dailyAt($time . ':30');
                $schedule->command('scrapper:indeed --query=flutter --fromage=7 --country=' . $country_code)->dailyAt($time . ':35');
            }
        }


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
