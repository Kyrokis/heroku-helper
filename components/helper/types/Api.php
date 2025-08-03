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
			case 'mangalib.me':
				return self::mangalib($template, $value, $allFields);
			case 'rss':
				return self::rss($template, $value, $allFields);
			case 'proxyrarbg.org':
				return self::proxyrarbg($template, $value, $allFields);
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
			$groupIndex = array_key_last($post['groups']);
			$new = [
				'title' => $post['groups'][$groupIndex]['name'],
				'link_img' => $post['groups'][$groupIndex]['photo_200'],
			];
		}
		$offset = 0;
		$items = $post['items'];
		ArrayHelper::multisort($items, 'date', SORT_DESC);
		do {
			$item = $items[$offset];
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
			$new['dt'] = $item['date'];
			if ($new['now'] == $value->now && $offset != $value->offset) {
				$check = true;
				break;
			}
			
			$check = Helper::checkClude($new['now'], $value->exclude, $value->include);
			$offset++;
			if ($offset > 9) {
				$check = true;
				$new['now'] = $value->now;
				$new['link_new'] = $value->link_new;
				$new['dt'] = $value->dt_update;
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
			'link_new' => $content['id'],
			'dt' => strtotime($content['attributes']['createdAt'])
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

	public static function mangalib($template, $value, $allFields) {
		$client = new Client();
		$title = end(explode('/', $value->link));
		$response = $client->get("https://api.cdnlibs.org/api/manga/$title/chapters")->send();
		$chapters = array_reverse($response->data['data']);
		$chapter = $chapters[$value->offset];
		$new = [
			'now' => "Том $chapter[volume] Глава $chapter[number]" . ($chapter['name'] ? ' - ' . $chapter['name'] : ''), 
			'link_new' => "https://mangalib.me/ru/$title/read/v$chapter[volume]/c$chapter[number]",
			'dt' => strtotime($chapter['branches'][0]['created_at']),
		];
		if ($allFields) {
			$clientTitle = new Client();
			$responseTitle = $client->get("https://api.cdnlibs.org/api/manga/$title")->send();
			$new['title'] = $responseTitle->data['data']['name'];
			$new['link_img'] = $responseTitle->data['data']['cover']['default'];
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
}