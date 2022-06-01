<?php
namespace App\Services;

use App\Models\Pos
use App\Models\PostedJob;
PostedJobuse Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class IndeedScrapper extends Scrapper {

	// Headers to be send
	protected $headers = [];

	// Base URL
	protected $url = 'https://www.indeed.com';

	// Total Jobs Saved
	private $totalJobsSaved = 0;

	/**
	 * Search for jobs against specific query
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function search($url = '', $query = 'php', $fromage = '', $country = '') {
		if ($country != '')
			$this->url = str_replace('www.', $country . '.', $this->url);

		if ($url == '') {
			if ($query) {
				$searchUrl = $this->url . '/jobs?q=' . $query;
			} else {
				return 'Invalid Query';
			}
		} else {
			$searchUrl = $url;
		}

		$fromAge = [
			1,
			3,
			7,
			14,
			'last'
		];

		if ($fromage != ''&& in_array($fromage, $fromAge)) {
			if (!str_contains($searchUrl, '?')) {
				$searchUrl .= '?';
			}

			$searchUrl .= '&fromage=' . $fromage;
		}

		if (config('settings.logs.indeedLogs'))
			Log::info('SEARCH URL: ' . $searchUrl);

		try {
			$response = $this->scrap($searchUrl);
		} catch (\Exception $e) {
			throw new Exception($e->getMessage());
		}

		if (config('settings.logs.indeedLogs'))
			Log::info('START PARSING');

		$jobs = $this->parseJobs($response);
		
		if (config('settings.logs.indeedLogs'))
			Log::info('END PARSING');

		return $this->totalJobsSaved . ' Jobs Saved.';
	}

	public function getJobDetail($jobId = '') {
		if ($jobId != '') {
			$this->url .= '/viewjob?jk=' . $jobId;
		} else {
			throw new Exception('Invalid Job ID!');
		}

		try {
			$response = $this->scrap();
		} catch (\Exception $e) {
			if (config('settings.logs.indeedLogs'))
				Log::info($e->getMessage());
			throw new Exception($e->getMessage());
		}

		return $this->parseJobDetail($jobId, $response);
	}

	/**
	 * Fetch Jobs from Indeed
	 *
	 * @param string $response
	 * @return \Illuminate\Http\JsonResponse
	 */
	private function parseJobs($response) {
		$jobs = [];

		$crawler = new Crawler($response);

		$jobNodes = $crawler->filter('ul.jobsearch-ResultsList li > div.cardOutline');
		// return $jobNodes;
		if (config('settings.logs.indeedLogs'))
			Log::info('Count: ' . $jobNodes->count());


		if ($jobNodes->count() > 0) {
			$jobNodes->each(function(Crawler $jobNode) use (&$jobs) {
				$job = [];

				// Get Job Title
				$jobTitleNode = $jobNode->filter('a.jcs-JobTitle');
				$isTitleNodeEmpty = $this->isNodeEmpty($jobTitleNode);
				$job['title'] = !$isTitleNodeEmpty ? $jobTitleNode->text() : '';

				// Get Job ID
				if (!$isTitleNodeEmpty) {
					$jobId = $jobTitleNode->attr('data-jk');
					$job['id'] = $jobId ?? '';
				}

				$companyNameNode = $jobNode->filter('.companyInfo .companyName');
				$job['company'] = !$this->isNodeEmpty($companyNameNode) ? $companyNameNode->text() : '';

				$companyLocationNode = $jobNode->filter('.companyInfo .companyLocation');
				$job['location'] = !$this->isNodeEmpty($companyLocationNode) ? $companyLocationNode->text() : '';

				$jobDateNode = $jobNode->filter('.jobCardShelfContainer span.date');
				$job['time_posted'] = !$this->isNodeEmpty($jobDateNode) ? str(strtolower($jobDateNode->text()))->replace('posted', '')->toString() : '';

				$jobs[] = $job;

				$bool = PostedJob::updateOrCreate([
					'job_id' => $job['id'],
				], [
					'job_web' => 'indeed',
					'title' => $job['title'],
					'company_name' => $job['company'],
					'location' => $job['location'],
					'time_posted' => $job['time_posted'],
				]);

				if ($bool) {
					$this->totalJobsSaved++;
				}
			});

			if (config('settings.logs.indeedDetailLogs'))
				Log::info($jobs);
		} else {
			if (config('settings.logs.indeedDetailLogs'))
				Log::info($crawler->html());
		}

		$this->nextPage($crawler);

		return $jobs;
	}

	private function nextPage($crawler) {
		$nextPageNode = $crawler->filter('div.pagination ul.pagination-list li [aria-label="Next"]');

		if (!$this->isNodeEmpty($nextPageNode)) {
			$nextPageUrl = $nextPageNode->attr('href');

			if (config('settings.logs.indeedLogs'))
				Log::info('NEXT PAGE: ' . $this->url . $nextPageUrl);

			return $this->search($this->url . $nextPageUrl);
		}
		
		return false;
	}

	private function parseJobDetail($jobId, $response) {
		$jobDetailArr = [];
		$crawler = new Crawler($response);
		$titleNode = $crawler->filter('h1.jobsearch-JobInfoHeader-title');

		$jobDetailArr['id'] = $jobId;
		$jobDetailArr['title'] = !$this->isNodeEmpty($titleNode) ? $titleNode->text() : '';

		$companyNameNode = $crawler->filter('.jobsearch-CompanyAvatar-companyLink');
		$jobDetailArr['company_name'] = !$this->isNodeEmpty($companyNameNode) ? $companyNameNode->text() : '';

		$companyRatingCountNode = $crawler->filter('[itemprop="ratingCount"]');
		$jobDetailArr['total_reviews'] = !$this->isNodeEmpty($companyRatingCountNode) ? $companyRatingCountNode->text() : '';

		$companyRatingNode = $crawler->filter('[itemprop="ratingValue"]');
		$jobDetailArr['rating'] = !$this->isNodeEmpty($companyRatingNode) ? $companyRatingNode->text() : '';

		$jobDescriptionNode = $crawler->filter('#jobDescriptionText');
		$jobDetailArr['job_description'] = !$this->isNodeEmpty($jobDescriptionNode) ? $jobDescriptionNode->text() : '';

		$salaryNode = $crawler->filter('#salaryInfoAndJobType > span');
		$jobDetailArr['salary_or_time'] = !$this->isNodeEmpty($salaryNode) ? $salaryNode->html() : '';

		$hiringNode = $crawler->filter('.jobsearch-HiringInsights-entry');
		$jobDetailArr['hiring'] = !$this->isNodeEmpty($hiringNode) ? $hiringNode->text() : '';

		$hiringInsightNode = $crawler->filter('#hiringInsightsSectionRoot');
		$jobDetailArr['hiring_insight'] = !$this->isNodeEmpty($hiringInsightNode) ? $hiringInsightNode->text() : '';

		$postedNode = $crawler->filter('.jobsearch-JobMetadataFooter div')?->first();
		$jobDetailArr['time_posted'] = !$this->isNodeEmpty($postedNode) ? $postedNode->text() : '';


		$job = PostedJob::where('job_id', $jobId)->first();

		if (!$job) {
			$job = new PostedJob();
		}

		$job->job_id = $jobId;
		$job->job_web = 'indeed';
		$job->title = $jobDetailArr['title'];
		$job->company_name = $jobDetailArr['company_name'];
		$job->total_reviews = $jobDetailArr['total_reviews'];
		$job->rating = $jobDetailArr['rating'];
		$job->description = $jobDetailArr['job_description'];

		if (preg_match('/[0-9]+/', $job->salary_or_time))
			$job->salary = $jobDetailArr['salary_or_time'];
		else
			$job->job_timing = $jobDetailArr['salary_or_time'];

		$job->hiring = $jobDetailArr['hiring'];
		$job->hiring_insight = $jobDetailArr['hiring_insight'];

		if (preg_match('/today|((days|day|month|months|year|years) ago)/i', $jobDetailArr['time_posted']))
			$job->time_posted = $jobDetailArr['time_posted'];
		else if ($job->company_name == '' || $job->company_name == null)
			$job->company_name = $jobDetailArr['time_posted'];
		
		$job->is_scrapped = 1;

		$job->save();

		return $jobDetailArr;
	}

}