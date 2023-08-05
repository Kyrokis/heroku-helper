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
		$link = $value->link;
		if ($template->id == 8) {
			//$link = str_replace('.si', '.ink', $value->link);
			//$link = $value->link . '&fresh_load';
			$link = 'https://freeproxy.io/o.php?b=4&u=' . urlencode($value->link. '&fresh_load_' . time());
			//return false;
		}
		$html = \QL\QueryList::get($link, null, ['timeout' => 10])->getHtml();
		$query = \QL\QueryList::html($html);
		$check = false;
		$offset = $value->offset;
		if ($allFields) {
			$new = $query->rules([ 
								'title' => $template->title, 
								//'link_img' => $template->link_img
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