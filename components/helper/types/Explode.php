<?

namespace app\components\helper\types;

use app\components\Str;
use app\components\helper\Helper;
use yii\httpclient\Client;
use yii\helpers\ArrayHelper;

/**
 * Helper working with strings
 */
class Explode {

	/**
	 * Get youtube playlist
	 * @param string $url
	 * @return string
	 */
	public static function getData($template, $value, $allFields) {
		$client = new Client();
		$response = $client->get($value->link, [], ['timeout' => 5])->send();
		$content = $response->content;
		$check = false;
		$offset = $value->offset;
		if ($allFields) {
			$new = [
				'title' => Str::explode($template->title, $content),
				'link_img' => Str::explode($template->link_img, $content),
			];
		}
		do {
			$new['now'] = Str::explode($template->new, $content, $offset);
			$new['link_new'] = Str::explode($template->link_new, $content, $offset);
			if ($new['now'] == $value->now && $offset != $value->offset) {
				$check = true;
				break;
			}
			$check = Helper::checkClude($new['now'], $value->exclude, $value->include);
			$offset++;
			if ($offset > $value->offset + 9) {
				$check = true;
				$new['now'] = $value->new;
				$new['link_new'] = $value->link_new;
			}
		} while (!$check);
		return $new;
	}
}