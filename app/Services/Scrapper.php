<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Scrapper {

	protected $headers = [];
	protected $url = '';

	/**
	 * Send HTTP Request & Get Response
	 *
	 * @param string $startUrl
	 * @return void
	 */
	public function scrap($startUrl = '')
	{
		if ($startUrl != '') {
			$url = $startUrl;
		} else if ($this->url != '') {
			$url = $this->url;
		} else {
			if (config('settings.logs.indeedLogs'))
				Log::info('No URL provided.');

			return 'No URL provided.';
		}

		if (count($this->headers) > 0) {
			$response = Http::retry(2, 5)->timeout(0)->withHeaders($this->headers)->get($url);
		} else {
			$response = Http::retry(2, 5)->timeout(0)->get($url);
		}

		if (config('settings.logs.indeedLogs'))
			Log::info('Status Code (' . $url . '): ' . $response->status());

		return $response->body();
	}

	/**
	 * check if current node empty?
	 *
	 * @param Crawler $node
	 * @return boolean
	 */
	protected function isNodeEmpty($node) {
		return $node->count() <= 0;
	}

}