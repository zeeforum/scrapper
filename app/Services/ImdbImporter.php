<?php
namespace App\Services;

use App\Models\ImdbTitle;

class ImdbImporter {

	private static $savedRecords = 0;

	public static function readFile($fileName, $path = '') {
		$isHeader = true;

		if ($path === '')
			$path = storage_path('app/public/import');

		$fileName = $path . DIRECTORY_SEPARATOR . $fileName;

		$file = fopen($fileName,'r');

		while(!feof($file)) {
			// Ignore first line because it's header
			$line = fgets($file);
			if ($isHeader) {
				$isHeader = false;
				continue;
			}

			$line = rtrim($line, "\r\n");
			$values = preg_split("/\t+/", $line);
			
			self::save($values);
		}

		fclose($file);

		return self::$savedRecords . ' Records Inserted Successfully!';
	}

	private static function save($arr) {
		if ($arr) {
			$start_year = self::removeCharacters($arr[5]);
			$end_year = self::removeCharacters($arr[6]);
			$runtime_minutes = self::removeCharacters($arr[7]);

			$dbArr = [
				'tconst' => $arr[0],
				'title_type' => self::removeCharacters($arr[1]),
				'primary_title' => self::removeCharacters($arr[2]),
				'original_title' => $arr[3],
				'is_adult' => $arr[4],
				'start_year' => $start_year > 0 ? $start_year : NULL,
				'end_year' => $end_year > 0 ? $end_year : NULL,
				'runtime_minutes' => $runtime_minutes > 0 ? $runtime_minutes : NULL,
				'genres' => self::removeCharacters($arr[8]),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			];

			if (ImdbTitle::insert($dbArr)) {
				self::$savedRecords++;
			}
		}
	}

	private static function removeCharacters($string) {
		return str_replace("\N", '', $string);
	}

}