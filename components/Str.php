<?

namespace app\components;

/**
 * Helper working with strings
 */
class Str {

	/**
	 * Translate date months
	 * @param string $dt
	 * @return string
	 */
	public static function dateEngToRu($dt) {
		$monthEn = [
			'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December',
			'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
		];
		
		$monthRu = [
			'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
			'янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'
		];

		return str_replace($monthEn, $monthRu, $dt);
	}

	/**
	 * Get substring between 2 delimiters
	 * @param array $delimiter
	 * @param string $string
	 * @return string
	 */
	public static function explode($delimiter, $string, $offset = 0) {
		if ($delimiter[1] && $delimiter[0]) {
			return explode($delimiter[1], explode($delimiter[0], $string)[$offset + 1])[0];
		} else if (!$delimiter[1] && $delimiter[0]) {
			return explode($delimiter[0], $string)[$offset + 1];
		}
	}
}