<?

namespace app\components\helper\types;

use app\components\Str;
use app\components\helper\Helper;
use yii\httpclient\Client;
use yii\helpers\ArrayHelper;

/**
 * Helper working with strings
 */
class Api {

	/**
	 * Get youtube playlist
	 * @param string $url
	 * @return string
	 */
	public static function getData($template, $value, $allFields) {
		switch ($template->name) {
			case 'vk.com':
				return self::vk($template, $value, $allFields);
			case 'mangadex.org':
				return self::mangadex($template, $value, $allFields);
			case 'rss':
				return self::rss($template, $value, $allFields);
			case 'proxyrarbg.org':
				return self::proxyrarbg($template, $value, $allFields);
			case 'mangalib.me':
				return self::mangalib($template, $value, $allFields);
			case 'SubsPlease':
				return self::subsplease($template, $value, $allFields);
			case 'NanDesuKa':
				return self::NanDesuKa($template, $value, $allFields);
		}
	}

	public static function vk($template, $value, $allFields) {
		$vk = new \VK\Client\VKApiClient();
		$post = $vk->wall()->get(\Yii::$app->params['vkApiKey'], [
						'owner_id' => $value->link,
						'offset' => $value->offset,
						'count' => 10,
						'filter' => 'owner',
						'extended' => 1
					]);
		if ($allFields) {
			$new = [
				'title' => $post['groups'][0]['name'],
				'link_img' => $post['groups'][0]['photo_200'],
			];
		}
		$offset = 0;
		do {
			$item = $post['items'][$offset];
			$new['now'] = $item['text'];
			if (isset($post['items'][$offset]['copy_history'])) {
				$repost = $post['items'][$offset]['copy_history'][0];
				if ($new['now'] == '') {
					$new['now'] .= $repost['text'];
				} else {
					$new['now'] .= "\n" . $repost['text'];
				}
			}
			$new['link_new'] = '/wall' . $value->link . '_' . $item['id'];
			if ($new['now'] == $value->now && $offset != $value->offset) {
				$check = true;
				break;
			}
			$check = Helper::checkClude($new['now'], $value->exclude, $value->include);
			$offset++;
			if ($offset > $value->offset + 8) {
				$check = true;
				$new['now'] = $value->new;
				$new['link_new'] = $value->link_new;
			}
		} while (!$check);
		\Yii::debug($offset);
		//geting media
		if (isset($item['attachments'])) {
			foreach ($item['attachments'] as $attachment) {
				if ($attachment['type'] == 'photo') {
					$sizes = array_filter($attachment['photo']['sizes'], fn($key) => ($key['type'] == 'w' || $key['type'] == 'z'));
					\Yii::debug($sizes);
					if ($sizes) {
						$new['media'][] = [
							'type' => 'photo',
							'media' => array_values($sizes)[0]['url'],
						];									
					}
				}
			}			
		}
		return $new;
	}

	public static function mangadex($template, $value, $allFields) {
		$link = explode(',', $value->link);
		$data = [
			'manga' => $link[0],
			'limit' => 1,
			'offset' => $value->offset,
			'includes' => ['manga'],
			'order' => ['chapter' => 'desc'],
		];
		if (isset($link[1]) && $link[1]) {
			$data['translatedLanguage'] = [$link[1]];
		}
		if (isset($link[2]) && $link[2]) {
			$data['groups'] = [$link[2]];
		}
		$client = new Client();
		$response = $client->get('https://api.mangadex.org/chapter', $data, ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36'])->send();
		\Yii::debug($response->data);
		$content = $response->data['data'][0];
		$new = [
			'now' => 'Chapter ' . $content['attributes']['chapter'] . ($content['attributes']['title'] ? ': ' . $content['attributes']['title'] : ''),
			'link_new' => $content['id']
		];
		if ($allFields) {
			foreach ($content['relationships'] as $relationship) {
				if ($relationship['type'] == 'manga') {
					$new['title'] = isset($relationship['attributes']['title']['ja-ro']) ? $relationship['attributes']['title']['ja-ro'] : $relationship['attributes']['title']['en'];
					$new['link_img'] = '';
					break;
				}
			}
		}
		return $new;
	}

	public static function proxyrarbg($template, $value, $allFields) {
		$command = 'python ' . \Yii::$app->basePath  . '/web/get_rarbg.py -query="' . $value->link . '"';
		exec($command, $result);
		$torrents = json_decode($result[0]);
		$new = [
			'now' => $torrents[$value->offset][0],
			'link_new' => $torrents[$value->offset][1],
		];	
		if ($allFields) {
			$new['title'] = $value->link;
			$new['link_img'] = '';
		}
		return $new;
	}

	public static function rss($template, $value, $allFields) {
		$link = explode(',', $value->link);
		$client = new Client();
		$response = $client->get($link[0])->send();
		$content = $response->data['channel'];
		$new = [
			'now' => $content['item'][$value->offset]['title'],
			'link_new' => $content['item'][$value->offset]['link'],
		];
		if ($allFields) {
			$new['title'] = $content['title'];
			$new['link_img'] = '';
		}
		return $new;
	}

	public static function mangalib($template, $value, $allFields) {
		$new = Explode::getData($template, $value, $allFields);
		$new['now'] = json_decode($new['now']);
		$link_new = explode('","chapter_volume":', $new['link_new']);
		$new['link_new'] = $value->link . "/v$link_new[1]/c$link_new[0]";
		return $new;
	}

	public static function subsplease($template, $value, $allFields) {
		$value->link = 'https://nyaa.si/user/subsplease?f=0&c=0_0&q=' . $value->link . '&fresh_load_' . time();
		//$value->link = 'https://freeproxy.io/o.php?b=4&u=' . urlencode('https://nyaa.si/user/subsplease?f=0&c=0_0&q=' . $value->link . '&fresh_load_' . time());
		//$value->link = 'https://nyaa.land/user/subsplease?f=0&c=0_0&q=' . urlencode($value->link);
		//var_dump($value); die;
		$new = QueryList::getData($template, $value, $allFields);
		return $new;
	}

	public static function nandesuka($template, $value, $allFields) {
		$value->link = 'https://nyaa.si/user/NanDesuKa?f=0&c=0_0&q=' . $value->link . '&fresh_load_' . time();
		$new = QueryList::getData($template, $value, $allFields);
		return $new;
	}
}