<?

namespace app\components\helper\types;

use app\components\Str;
use app\components\helper\Helper;
use yii\helpers\ArrayHelper;

/**
 * Helper working with strings
 */
class QueryList {

	/**
	 * Get youtube playlist
	 * @param string $url
	 * @return string
	 */
	public static function getData($template, $value, $allFields) {
		$html = \QL\QueryList::get($value->link, null, ['timeout' => 5])->getHtml();
		$query = \QL\QueryList::html($html);
		$check = false;
		$offset = $value->offset;
		if ($allFields) {
			$new = $query->rules([ 
								'title' => $template->title, 
								'link_img' => $template->link_img
							])
							->query()->getData()->all();			
		}
		do {
			$newTemplate = [
				'new' => $template->new->getValue(),
				'link_new' => $template->link_new->getValue(),
			];
			$newTemplate['new'][0] = str_replace('{offset}', $offset, $newTemplate['new'][0]);
			$newTemplate['link_new'][0] = str_replace('{offset}', $offset, $newTemplate['link_new'][0]);
			$item = $query->rules([
						'now' => $newTemplate['new'], 
						'link_new' => $newTemplate['link_new']
					])
					->query()->getData()->all();
			$new['now'] = $item['now'];
			$new['link_new'] = $item['link_new'];
			if ($new['now'] == $value->now && $offset != $value->offset) {
				$check = true;
				break;
			}
			$check = Helper::checkClude($new['now'], $value->exclude, $value->include);
			$offset++;
			if ($offset > $value->offset + 9) {
				$check = true;
				$new = [
					'now' => $value->now,
					'link_new' => $value->link_new
				];
			}
		} while (!$check);
		return $new;
	}
}