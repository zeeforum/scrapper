<?php
namespace App\Services;

use App\Models\ImdbTitle;
use App\Models\ImdbName;

class ImdbImporter {

	private static $savedRecords = 0;

	public static function readFile($fileName, $imdb_data = 'title.basics', $path = '') {
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
			
			switch ($imdb_data) {
				case 'title.basics':
					self::saveImdbTitles($values);
					break;
				case 'name.basics':
					self::saveImdbNames($values);
					break;
				default:
					# code...
					break;
			}
		}

		fclose($file);

		return self::$savedRecords . ' Records Inserted Successfully!';
	}

	private static function saveImdbTitles($arr) {
		if ($arr && isset($arr[0]) && $arr[0] !== '') {
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

	private static function saveImdbNames($arr) {
		if ($arr && isset($arr[0]) $arr[0] !== '') {
			$birth_year = isset($arr[2]) ? self::removeCharacters($arr[2]) : 0;
			$death_year = isset($arr[3]) ? self::removeCharacters($arr[3]) : 0;
			$primary_profession = isset($arr[4]) ? self::removeCharacters($arr[4]) : '';
			$known_for_titles = isset($arr[5]) ? self::removeCharacters($arr[5]) : '';

			$dbArr = [
				'nconst' => $arr[0],
				'primary_name' => self::removeCharacters($arr[1]),
				'birth_year' => $birth_year > 0 ? $birth_year : NULL,
				'death_year' => $death_year > 0 ? $death_year : NULL,
				'primary_profession' => $primary_profession != '' ? $primary_profession : NULL,
				'known_for_titles' => $known_for_titles != '' ? $known_for_titles : NULL,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
			];

			if (ImdbName::insert($dbArr)) {
				self::$savedRecords++;
			}
		}
	}

	private static function removeCharacters($string) {
		return str_replace("\N", '', $string);
	}

}