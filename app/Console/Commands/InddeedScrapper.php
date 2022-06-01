<?php

namespace App\Console\Commands;

use App\Services\IndeedScrapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InddeedScrapper extends Command
{
    /**
     * The name and signature of the console command.
     * --query: The country to search
     * --fromage: The job search from last (1, 3, 7, 14 and last)
     * --country: The country to search
     *
     * @var string
     */
    protected $signature = 'scrapper:indeed {--query=} {--fromage=} {--country=} {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrap jobs from indeed.com';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('max_execution_time', 0);

        $objScrapper = new IndeedScrapper();

        $jobId = $this->option('id') !== '' ? $this->option('id') : '';

        if ($jobId != '') {
            $response = $objScrapper->getJobDetail($jobId);

            dd($response);
        }

        $response = $objScrapper->search('', $this->option('query'), $this->option('fromage'), $this->option('country'));

        echo "The url is scrapped." . PHP_EOL;
        echo $response;
        exit();
    }
}
