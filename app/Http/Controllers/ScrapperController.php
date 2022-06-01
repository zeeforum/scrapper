<?php
namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapperController extends Controller
{

	protected $headers = [];
	protected $url = '';

	public function scrap($startUrl = '')
	{
		if ($startUrl != '') {
			$url = $startUrl;
		} else if ($this->url != '') {
			$url = $this->url;
		} else {
			throw new Exception('No url provided.');
		}

		if (count($this->headers) > 0) {
			$response = Http::withHeaders($this->headers)->get($url);
		} else {
			$response = Http::get($url);
		}

		Log::info('Status Code (' . $url . '): ' . $response->status());

		return $response->body();
	}

	protected function isNodeEmpty($node) {
		return $node->count() <= 0;
	}

}