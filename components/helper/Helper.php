<?

namespace app\components\helper;

use app\components\Str;
use app\components\helper\types\QueryList;
use app\components\helper\types\Explode;
use app\components\helper\types\Api;
use yii\helpers\ArrayHelper;

/**
 * Helper working with strings
 */
class Helper {

	/**
	 * Get youtube playlist
	 * @param string $url
	 * @return string
	 */
	public static function getData($template, $value, $allFields = false) {
		if (is_array($value)) {
			$value = (object) $value;
		}
		switch ($template->type) {
			case 0:
				return QueryList::getData($template, $value, $allFields);
			case 1:
				return Explode::getData($template, $value, $allFields);
			case 2:
				return Api::getData($template, $value, $allFields);
		}		
	}

	public static function checkClude($value, $exclude, $include) {
		if ((count($include) == 0 && count($exclude) == 0)) {
			$check = true;
		} else {
			if (count($exclude) > 0) {
				$excludeCheck = true;
				foreach ($exclude as $word) {
					if (mb_strpos(mb_strtolower($value), mb_strtolower($word)) !== false) {
						$excludeCheck = false;
						break;
					}
				}
				if ($excludeCheck) {
					$check = true;
				} else {
					$check = false;
				}
			}
			if (count($include) > 0) {
				foreach ($include as $word) {
					$includeCheck = false;
					if (mb_strpos(mb_strtolower($value), mb_strtolower($word)) !== false) {
						$includeCheck = true;
						break;
					}
				}
				if ($includeCheck) {
					$check = true;
				} else {
					$check = false;
				}
			}
		}
		return $check;
	}
}