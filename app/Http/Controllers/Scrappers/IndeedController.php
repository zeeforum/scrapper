<?php 
namespace App\Http\Controllers\Scrappers;

use App\Http\Controllers\ScrapperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class IndeedController extends ScrapperController {

	// Headers to be send
	protected $headers = [];

	// Base URL
	protected $url = 'https://www.indeed.com';

	public function index() {

	}

	/**
	 * Search for jobs against specific query
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function search(Request $request, $url = '') {
		if ($url == '') {
			if ($request->get('query') && $request->get('query') != '') {
				$searchUrl = $this->url . '/jobs?q=' . $request->get('query');
			} else {
				throw new Exception('Invalid Query!');
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

		if ($request->get('fromage') && $request->get('fromage') != '' && in_array($request->get('fromage'), $fromAge)) {
			if (!str_contains($searchUrl, '?')) {
				$searchUrl .= '?';
			}

			$searchUrl .= '&fromage=' . $request->get('fromage');
		}

		Log::info('SEARCH URL: ' . $searchUrl);

		try {
			$response = $this->scrap($searchUrl);
		} catch (\Exception $e) {
			Log::info($e->getMessage());
			throw new Exception($e->getMessage());
		}

		Log::info('START PARSING');
		$jobs = $this->parseJobs($response);
		Log::info('END PARSING');

		return 'Success';
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
			Log::warn($e->getMessage());
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
			});

			Log::info($jobs);
		} else {
			Log::info($crawler->html());
		}

		$this->nextPage($crawler);

		return $jobs;
	}

	private function nextPage($crawler) {
		$nextPageNode = $crawler->filter('div.pagination ul.pagination-list li [aria-label="Next"]');

		if (!$this->isNodeEmpty($nextPageNode)) {
			$nextPageUrl = $nextPageNode->attr('href');
			Log::info('NEXT PAGE: ' . $this->url . $nextPageUrl);

			return $this->search(new Request(), $this->url . $nextPageUrl);
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

		return $jobDetailArr;
	}

}