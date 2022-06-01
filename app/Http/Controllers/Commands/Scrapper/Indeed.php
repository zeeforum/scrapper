<?php
namespace App\Http\Controllers\Commands\Scrapper;

use App\Http\Controllers\Controller;
use App\Models\PostedJob;
use App\Services\IndeedScrapper;

class Indeed extends Controller {

	public static function fetchJobDetail() {
		$jobs = PostedJob::where('is_scrapped', 0)->orderBy('id', 'asc')->limit(10)->get();

		if ($jobs->count() > 0) {
			foreach ($jobs as $job) {
				try {
					$scrapper = new IndeedScrapper();
					$scrapper->getJobDetail($job->job_id);
					sleep(3);
				} catch (\Exception $e) {
					echo $e->getMessage();
					exit();
				}
			}
		}

		echo 'Done!' . PHP_EOL;
	}

}