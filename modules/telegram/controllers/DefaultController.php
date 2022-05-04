<?
namespace app\modules\telegram\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use app\modules\helper\models\Items;
use app\modules\user\models\User;
use app\modules\template\models\Template;
use app\models\TelegramForm;
use QL\QueryList;
use GuzzleHttp\Exception\ClientException;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Exception;
use app\components\youtube\Youtube;

/**
 * Controller for telegram module
 */
class DefaultController extends Controller {

	public $title = 'Telegram helper';

	public $sites = [
		'nyaasi' => 'https://nyaa',
		'anilibria' => 'https://www.anilibria.tv',
		'googleDrive' => 'https://drive.google.com',
		'romantica' => 'https://sovetromantica.com',
		'erairaws' => 'https://www.erai-raws.info',
	];

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action) {		
		$user = Yii::$app->user;
		if (($action->id != 'update' && $action->id != 'webhook-page') && !$user->identity->admin) {
			if ($user->isGuest) {
				$this->redirect($user->loginUrl);
			} else {
				$this->goHome();
			}
		}
		if ($action->id == 'update' || $action->id == 'webhook-page') {
			$this->enableCsrfValidation = FALSE;
			$this->layout = FALSE;
		}

		return parent::beforeAction($action);
	}

	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * Index
	 * @return json
	 */
	public function actionIndex() {
		$model = new TelegramForm;
		return $this->render('index', ['model' => $model]);
	}

	/**
	 * Send message
	 * @return json
	 */
	public function actionSendMessage($chat_id = '197239226', $text = 'test') {
		$result = Yii::$app->telegram->sendMessage([
					'chat_id' => $chat_id,
					'text' => $text,
				]); 
		return $result;
	}

	/**
	 * Get updates in selected chat
	 * @return json
	 */
	public function actionGetUpdates($chat_id = '197239226') {
		Yii::$app->telegram->deleteWebhook(); 
		$updates = Yii::$app->telegram->getUpdates([
			'chat_id' => $chat_id,
		]); 

		$result = Yii::$app->telegram->sendMessage([
			'chat_id' => $chat_id,
			'text' => json_encode($updates),
		]); 
		Yii::$app->telegram->setWebhook(['url' => \Yii::$app->params['webhookPage']]); 
		return $result;
	}


	/**
	 * Activate webhook on url
	 * @return json
	 */
	public function actionSetWebhook($url = '') {
		$url = $url != '' ? $url : \Yii::$app->params['webhookPage'];
		$result = Yii::$app->telegram->setWebhook(['url' => $url]); 
		return json_encode($result);
	}

	/**
	 * Webhook page
	 * @return json
	 */
	public function actionWebhookPage() {
		$response = Yii::$app->telegram->input;
		Yii::debug(json_encode($response));
		if (isset($response->message)) {
			$message = $response->message;
			$idTelegram = $message['from']['id'];
			$type = 'message';
		} else if (isset($response->channel_post)) {
			$message = $response->channel_post;
			$idTelegram = $message['sender_chat']['id'];
			$type = 'channel_post';
		} else if (isset($response->callback_query)) {
			$message = $response->callback_query['message'];
			$idTelegram = $response->callback_query['from']['id'];
			$type = 'callback_query';
		}
		if (isset($response->callback_query['data'])) {
			$callback_data = json_decode($response->callback_query['data']);
			$idMessage = $message['message_id'];
			if ($callback_data->type == 'check') {
				$model = Items::findOne($callback_data->item_id);
				$model->now = $model->new;
				if ($model->save()) {
					$result = Yii::$app->telegram->editMessageReplyMarkup([
						'chat_id' => $idTelegram,
						'message_id' => $idMessage,
					]);	
					return Yii::debug($result);
				}
			}
		} else if (!isset($message)) {
			return false;
		}

		if (isset($message['text'])) {
			if ($message['text'] == '/get_id') {
				$this->getId($idTelegram);
			} else if ($message['text'] == '/change_mode') {
				$this->changeMode($idTelegram);
			} else if ($message['text'] == '/show_keyboard') {
				$this->showKeyboard($idTelegram);
			} else if ($message['text'] == '/update') {
				return true;
				$this->update($idTelegram);
			} 			
		}

		$urls = [];
		if (isset($message['entities'])) {
			foreach ($message['entities'] as $entity) {
				if ($entity['type'] == 'url') {
					$urls[] = mb_substr($message['text'], $entity['offset'], $entity['length']);
				}
				if ($entity['type'] == 'text_link') {
					$urls[] = $entity['url'];
				}
			}
		}
		Yii::debug(json_encode($urls));
		foreach ($urls as $url) {
			if (mb_stripos($url, 'youtube.com/playlist?list=') !== false || (mb_stripos($url, 'youtube.com') !== false && mb_stripos($url, '/videos') !== false)) {
				$file = Youtube::getPlaylist($url);
				$result = Yii::$app->telegram->sendDocument([
					'chat_id' => $idTelegram,
					'document' => $file
				]);
			} else {
				$this->getTorrent($url, $idTelegram);
			}
		}
	}

	public function actionUpdate($id_telegram = null, $user_id = null, $only_new = false) {
		if ($user_id == 1) {
			return $this->update($id_telegram, $user_id, $only_new);
		}
		return false;
	}

	public function actionTest() {	
	}


	public function actionTestpage() {
		$this->layout = false;
		return $this->render('testpage');
	}

	public function actionGetPlaylist($url, $reverse = false) {
		if (mb_stripos($url, 'youtube.com/playlist?list=') !== false || (mb_stripos($url, 'youtube.com') !== false && mb_stripos($url, '/videos') !== false)) {
			$file = Youtube::getPlaylist($url, $reverse);
			Yii::$app->response->sendFile($file);
		}
	}


	private function getId($idTelegram) {
		$result = Yii::$app->telegram->sendMessage([
				'chat_id' => $idTelegram,
				'text' => $idTelegram,
		]);
		return $result;
	}

	private function changeMode($idTelegram) {
		$result = false;
		if ($user = User::find()->where(['id_telegram' => $idTelegram, 'del' => '0'])->one()) {
			$user->show_all = $user->show_all ? '0' : '1';
			if ($user->save(FALSE, ['show_all'])) {
				$result = Yii::$app->telegram->sendMessage([
					'chat_id' => $idTelegram,
					'text' => 'Режим изменен на ' . ($user->show_all == '1' ? '"Показывать все"' : '"Показывать один"'),
				]);
			}
		} else {
			$result = Yii::$app->telegram->sendMessage([
				'chat_id' => $idTelegram,
				'text' => 'Вначале зарегистрируйтесь или добавьте "ID в телеграме"',
			]);
		}
		Yii::debug($result);
		return $result;
	}

	private function showKeyboard($idTelegram) {
		$reply_markup = [
			'keyboard' => [[
				['text' => '/update']
			]],
			'resize_keyboard' => true,
		];
		$result = Yii::$app->telegram->sendMessage([
			'chat_id' => $idTelegram,
			'text' => 'Готово',
			'reply_markup' => json_encode($reply_markup),
		]);
		Yii::debug($result);
		return json_encode($result);
	}

	private function update($idTelegram = null, $userId = null, $only_new = false) {
		if ($idTelegram) {
			$user = User::find()->where(['id_telegram' => $idTelegram, 'del' => '0'])->one();
			$userId = $user->id;
		} else if ($userId) {
			$user = User::find()->where(['id' => $userId, 'del' => '0'])->one();
			$idTelegram = $user->id_telegram;
		} else {
			return false;
		}
		$items = json_decode(Yii::$app->runAction('helper/default/helping', ['user_id' => $userId, 'only_new' => $only_new]), true);
		Yii::debug($items);
		$out = false;
		if ($items) {
			foreach ($items as $item) {
				if ($item && $item['error'] == '0') {
					$fullLink = Template::getFullLink($item['link_new'], $item['id_template']);
					$linkText = Html::a($item['new'], $fullLink);
					$reply_markup = [
						'inline_keyboard' => [[
							[
								'text' => 'Check',
								'callback_data' => json_encode(['type' => 'check', 'item_id' => $item['id']])
							]
						]],
						'resize_keyboard' => true,
					];
					if ($item['media']) {
						$item['media'][0]['caption'] = "<b>$item[title]</b> \n$linkText";
						$item['media'][0]['parse_mode'] = 'HTML';
						try {
							$result = Yii::$app->telegram->sendMediaGroup([
									'chat_id' => $idTelegram,
									'media' => json_encode($item['media']),
								]); 
							Yii::debug(json_encode($result));							
						} catch (ClientException $e) {
							Yii::debug($e);
						}						
					} else {
						$result = Yii::$app->telegram->sendMessage([
							'chat_id' => $idTelegram,
							'text' => "<b>$item[title]</b> \n$linkText",
							'parse_mode' => 'HTML',
							'disable_web_page_preview' => true,
							'reply_markup' => json_encode($reply_markup),
						]);
						Yii::debug($result);
					}
					$result = $this->getTorrent($fullLink, $idTelegram);
					Yii::debug($result);
					$out = true;
				}
			}
			Yii::debug($out);
		}
		if (!$out && !$only_new) {
			$result = Yii::$app->telegram->sendMessage([
				'chat_id' => $idTelegram,
				'text' => 'Ничего нового',
			]);
		}
		return $out;
	}

	private function getTorrent($url, $idTelegram) {
		//nyaasi or .torrent
		if (mb_stripos($url, '.torrent') !== false || (mb_stripos($url, $this->sites['nyaasi']) !== false && basename($url) == 'torrent')) {
			$result = Yii::$app->telegram->sendDocument([
				'chat_id' => $idTelegram,
				'document' => $this->loadFile($url)
			]);
			Yii::debug(json_encode($result));
		//soviet romantika or google drive
		} else if (mb_stripos($url, $this->sites['googleDrive']) !== false || mb_stripos($url, $this->sites['romantica']) !== false) {
			if (mb_stripos($url, $this->sites['romantica']) !== false) {
				try {
					$url = QueryList::get($url)->find('.animeTorrentDownload')->attrs('href')->all()[0];
				}
				catch (ClientException $e) {
					Yii::error($e->getMessage());
				}
			}
			$idPos = strrpos($url, '?id=');
			if ($idPos !== false) {
				$folderId = mb_substr($url, $idPos + 4, strlen($url));
			} else {
				$folderId = basename(parse_url($url, PHP_URL_PATH));
			}
			$client = new Google_Client(['developer_key' => Yii::$app->params['googleApiKey']]);
			$service = new Google_Service_Drive($client);
			try {
				$folders = $service->files->listFiles([
					'fields' => 'files(id, name, mimeType)',
					'q' => "mimeType = 'application/vnd.google-apps.folder' and '$folderId' in parents and (name contains 'СУБТИТРЫ' or name contains 'sub' or name contains 'ТОРРЕНТ' or name contains 'torrent')",
				]);
			}
			catch (Google_Service_Exception $e) {
				Yii::error($e->getMessage());
			}
			$show_all = 0;
			if ($user = User::find()->where(['id_telegram' => $idTelegram, 'del' => '0'])->one()) {
				$show_all = $user->show_all;
			}
			foreach ($folders->getFiles() as $folder) {
				$subFolderId = $folder->getId();
				try {
					$listFiles = $service->files->listFiles([
						'fields' => 'files(id, name, modifiedTime, webContentLink)',
						'q' => "'$subFolderId' in parents",
						'orderBy' => 'modifiedTime desc',
						'pageSize' => $show_all ? null : 1
					]);
				}
				catch (Google_Service_Exception $e) {
					Yii::error($e->getMessage());
					continue;
				}
				if ($files = $listFiles->getFiles()) {
					foreach ($files as $file) {
						$newName = $file->getName();
						$document = $this->loadFile($file->getWebContentLink(), $newName);
						Yii::debug('Попытка отправить файл: ' . $newName);
						$result = Yii::$app->telegram->sendDocument([
							'chat_id' => $idTelegram,
							'document' => $document
						]);
						Yii::debug(json_encode($result));
						if (!$result) {
							Yii::debug('Что-то пошло не так и отправляю ссылку');
							$result = Yii::$app->telegram->sendMessage([
								'chat_id' => $idTelegram,
								'text' => $file->getWebContentLink(),
							]); 
						}
					}
				}
			}
		}
	}

	private function loadFile($url, $filename = null) {
		if (!$filename) {
			//$filename = urldecode(basename($url));
			$explode = explode('/', urldecode($url));
			$filename = end($explode);
			$filename = ($filename == 'torrent') ? array_slice($explode, -2, 1)[0] : $filename;
		}
		if (mb_stripos($filename, '.torrent') === false && mb_stripos($filename, '.ass') === false) {
			$filename .= '.torrent';
		}
		$file = Yii::$app->basePath . '/uploads/' . $filename;
		$fp = fopen($file, 'w+');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		if (file_exists($file)) {
			return $file;
		}
		return false;
	}
}
