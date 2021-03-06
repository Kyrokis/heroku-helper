<?
namespace app\modules\helper\controllers;

use Yii;
use yii\web\Controller;
use yii\httpclient\Client;
use app\modules\helper\models\Items;
use app\modules\helper\models\ItemsHistory;
use app\modules\user\models\User;
use app\modules\template\models\Template;
use app\components\thread\Thread;
use QL\QueryList;
use VK\Client\VKApiClient;
use app\components\Str;
use app\components\youtube\Youtube;


/**
 * Controller for Helper module
 */
class DefaultController extends Controller {

	public $title = 'Helper';

	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action) {
		$user = Yii::$app->user;
		if ($action->id == 'helping') {
			$this->enableCsrfValidation = FALSE;
			$this->layout = FALSE;
		} else if ($user->isGuest) {
			$this->redirect($user->loginUrl);
		}

		return parent::beforeAction($action);
	}

	/**
	 * Index
	 * @return json
	 */
	public function actionIndex() {
		$model = new Items();
		$model->setScenario(Items::SCENARIO_SEARCH);
		$model->user_id = Yii::$app->user->id;
		$model->load(\Yii::$app->request->get());
		return $this->render('index', ['model' => $model]);
	}

	/**
	 * History page
	 * @return json
	 */
	public function actionHistory() {
		$model = new ItemsHistory();
		$model->load(\Yii::$app->request->get());
		if (!$model->dt_start) {
			$model->dt_start = date('d.m.Y', strtotime('-1 month'));
		}
		if (!$model->dt_end) {
			$model->dt_end = date('d.m.Y', time());
		}
		$model->dt = $model->dt_start . ' - ' . $model->dt_end;
		return $this->render('history', ['model' => $model]);
	}

	public function actionHistoryCalendar() {
		$model = new ItemsHistory();
		$model->load(\Yii::$app->request->get());
		if (!$model->dt_start) {
			$model->dt_start = date('d.m.Y', strtotime('-1 month'));
		}
		if (!$model->dt_end) {
			$model->dt_end = date('d.m.Y', time());
		}
		$model->dt = $model->dt_start . ' - ' . $model->dt_end;
		return $this->render('calendar', ['model' => $model]);
	}

	public function actionCalendar($history = 0) {
		$model = $history ? new ItemsHistory() : new Items();
		$model->load(\Yii::$app->request->get());
		if ($history) {
			if (!$model->dt_start) {
				$model->dt_start = date('d.m.Y', strtotime('-1 month'));
			}
			if (!$model->dt_end) {
				$model->dt_end = date('d.m.Y', time());
			}
			$model->dt = $model->dt_start . ' - ' . $model->dt_end;
			$fields = [
				'type' => 'history',
				'title' => 'History calendar',
				'dateField' => 'dt',
				'valueField' => 'item_id'
			];
		} else {
			$fields = [
				'type' => 'estimated',
				'title' => 'Estimated calendar',
				'dateField' => 'dt_estimated',
				'valueField' => 'id'
			];
		}
		return $this->render('calendar', ['model' => $model, 'fields' => $fields]);
	}

	/**
	 * Get Data
	 * @return json
	 */
	public function actionGetData($link, $id_template, $offset = 0) {
		$template = Template::findOne($id_template);
		if ($template->type == 1) {
			$client = new Client();
			$response = $client->get($link)->send();
			$content = $response->content;
			$items = [
				'title' => Str::explode($template->title, $content),
				'now' => Str::explode($template->new, $content, $offset),
				'link_new' => Str::explode($template->link_new, $content, $offset),
				'link_img' => Str::explode($template->link_img, $content),
			];	
		} else if ($template->type == 2) {
			if ($template->name == 'vk.com') {
				$vk = new VKApiClient();
				$post = $vk->wall()->get(\Yii::$app->params['vkApiKey'], [
								'owner_id' => $link,
								'offset' => $offset,
								'count' => 1,
								'filter' => 'owner',
								'extended' => 1
							]);
				$items = [
					'title' => $post['groups'][0]['name'],
					'link_img' => $post['groups'][0]['photo_200'],
					'link_new' => '/wall' . $link . '_' . $post['items'][0]['id'],
				];	
				if (isset($post['items'][0]['copy_history'])) {
					$items['now'] = $post['items'][0]['copy_history'][0]['text'];
				} else {
					$items['now'] = $post['items'][0]['text'];
				}				
			} else if ($template->name == 'mangadex.org') {
				$link = explode(',', $link);
				$data = [
					'manga' => $link[0],
					'limit' => 1,
					'offset' => $offset,
					'order' => ['chapter' => 'desc'],
					'includes' => ['manga']
				];
				if ($link[1]) {
					$data['translatedLanguage'] = [$link[1]];
				}
				if ($link[2]) {
					$data['groups'] = [$link[2]];
				}
				$client = new Client();
				$response = $client->get('https://api.mangadex.org/chapter', $data)->send();
				$content = $response->data['results'][0];
				//var_dump($content); die;
				$items = [
					'now' => 'Chapter ' . $content['data']['attributes']['chapter'] . ($content['data']['attributes']['title'] ? ': ' . $content['data']['attributes']['title'] : ''),
					'link_new' => $content['data']['id'],
					'link_img' => '',
				];
				foreach ($content['relationships'] as $relationship) {
					if ($relationship['type'] == 'manga') {
						$items['title'] = $relationship['attributes']['title']['en'];
						break;
					}
				}
			} else if ($template->name == 'rss') {
				$client = new Client();
				$response = $client->get($link)->send();
				$content = $response->data['channel'];
				$items = [
					'title' => $content['title'],
					'now' => $content['item'][$offset]['title'],
					'link_new' => $content['item'][$offset]['link'],
					'link_img' => $content['image']['url'],
				];	
			} else if ($template->name == 'proxyrarbg.org') {
				$command = 'python ' . \Yii::$app->basePath  . '/web/get_rarbg.py -query="' . $link . '"';
				exec($command, $result);
				$torrents = json_decode($result[0]);
				$items = [
					'title' => $link,
					'now' => $torrents[$offset][0],
					'link_new' => $torrents[$offset][1],
					'link_img' => '',
				];	
			}
		} else {
			foreach ($template->attributes as $key => $attribute) {
				if (is_object($attribute)) {
					$template->$key[0] = str_replace('{offset}', $offset, $template->$key[0]);
				}
			}
			$items = QueryList::get($link)->rules([ 
							'title' => $template->title,
							'now' => $template->new,
							'link_new' => $template->link_new,
							'link_img' => $template->link_img,
						])
						->query()->getData()->all();
		}
		$items['now'] = ($items['now'] != '') ? $items['now'] : $items['link_new'];
		return json_encode($items);
	}


	/**
	 * Check selected item
	 * @return bool
	 */
	public function actionCheck($id) {
		$model = $this->findModel($id);
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('?? ?????? ?????? ???????? ???? ?????? ????????????????');
		}
		$model->now = $model->new;
		return $model->save();
	}

	/**
	 * Helping items
	 * @return json
	 */	
	public function actionHelping($id = null, $user_id = null, $only_new = false) {
		$items = Items::find()->andFilterWhere(['id' => $id, 'user_id' => $user_id, 'del' => '0'])->all();
		$only_new = ($only_new == '0' || $only_new == 'false' || !$only_new) ? false : true;
		if ($user_id) {
			$user = User::findOne($user_id);
			$user->dt_helping = time();
			$user->save(FALSE, ['dt_helping']);	
		}
		$Thread = new Thread();
		foreach ($items as $value) {
			$Thread->Create(function() use($value, $only_new) {
				$template = app\modules\template\models\Template::findOne($value->id_template);				
				if ($template->type == 1) {
					$client = new \yii\httpclient\Client();
					$response = $client->get($value->link, [], ['timeout' => 10])->send();
					$content = $response->content;
					$check = false;
					$offset = $value->offset;
					do {
						$new = [
							'now' => \app\components\Str::explode($template->new, $content, $offset),
							'link_now' => \app\components\Str::explode($template->link_new, $content, $offset)
						];
						if ($new['now'] == $value->now && $offset != $value->offset) {
							$check = true;
						}
						if ((count($value->include) == 0 && count($value->exclude) == 0)) {
							$check = true;
						} else {
							if (count($value->exclude) > 0) {
								$excludeCheck = true;
								foreach ($value->exclude as $word) {
									if (mb_strpos(mb_strtolower($new['now']), mb_strtolower($word)) !== false) {
										$excludeCheck = false;
									}
								}
								if ($excludeCheck) {
									$check = true;
								} else {
									$check = false;
								}
							}
							if (count($value->include) > 0) {
								foreach ($value->include as $word) {
									$includeCheck = false;
									if (mb_strpos(mb_strtolower($new['now']), mb_strtolower($word)) !== false) {
										$includeCheck = true;
									}
								}
								if ($includeCheck) {
									$check = true;
								} else {
									$check = false;
								}
							}
						}
						$offset++;
						if ($offset > 10) {
							$check = true;
							$new = [
								'now' => $value->now,
								'link_now' => $value->link_new
							];
						}
					} while (!$check);
				} else if ($template->type == 2) {
					if ($template->name == 'vk.com') {
						$vk = new \VK\Client\VKApiClient();
						$post = $vk->wall()->get(\Yii::$app->params['vkApiKey'], [
										'owner_id' => $value->link,
										'offset' => $value->offset,
										'count' => 1,
										'filter' => 'owner',
										'extended' => 1
									]);
						$new['link_now'] = '/wall' . $value->link . '_' . $post['items'][0]['id'];
						if (isset($post['items'][0]['copy_history'])) {
							$new['now'] = $post['items'][0]['copy_history'][0]['text'];
						} else {
							$new['now'] = $post['items'][0]['text'];
						}						
					} else if ($template->name == 'mangadex.org') {
						$link = explode(',', $value->link);
						$data = [
							'manga' => $link[0],
							'limit' => 1,
							'offset' => $value->offset,
							'order' => ['chapter' => 'desc'],
						];
						if (isset($link[1])) {
							$data['translatedLanguage'] = [$link[1]];
						}
						if (isset($link[2])) {
							$data['groups'] = [$link[2]];
						}
						$client = new \yii\httpclient\Client();
						$response = $client->get('https://api.mangadex.org/chapter', $data)->send();
						$content = $response->data['results'][0];
						$new = [
							'now' => 'Chapter ' . $content['data']['attributes']['chapter'] . ($content['data']['attributes']['title'] ? ': ' . $content['data']['attributes']['title'] : ''),
							'link_now' => $content['data']['id']
						];
					} else if ($template->name == 'rss') {
						$client = new \yii\httpclient\Client();
						$response = $client->get($value->link)->send();
						$content = $response->data['channel'];
						$new = [
							'now' => $content['item'][$value->offset]['title'],
							'link_now' => $content['item'][$value->offset]['link'],
						];	
					} else if ($template->name == 'proxyrarbg.org') {
						$command = 'python ' . \Yii::$app->basePath  . '/web/get_rarbg.py -query="' . $value->link . '"';
						exec($command, $result);
						$torrents = json_decode($result[0]);
						$new = [
							'now' => $torrents[$value->offset][0],
							'link_now' => $torrents[$value->offset][1],
						];	
					}
				} else {
					$query = \QL\QueryList::get($value->link, null, ['timeout' => 10]);
					$check = false;
					$offset = $value->offset;
					do {
						$newTemplate = [
							'new' => $template->new->getValue(),
							'link_now' => $template->link_new->getValue(),
						];
						$newTemplate['new'][0] = str_replace('{offset}', $offset, $newTemplate['new'][0]);
						$newTemplate['link_now'][0] = str_replace('{offset}', $offset, $newTemplate['link_now'][0]);
						$new = $query->rules([
									'now' => $newTemplate['new'], 
									'link_now' => $newTemplate['link_now']
								])
								->query()->getData()->all();

						if ($new['now'] == $value->now && $offset != $value->offset) {
							$check = true;
						}
						if ((count($value->include) == 0 && count($value->exclude) == 0)) {
							$check = true;
						} else {
							if (count($value->exclude) > 0) {
								$excludeCheck = true;
								foreach ($value->exclude as $word) {
									if (mb_strpos(mb_strtolower($new['now']), mb_strtolower($word)) !== false) {
										$excludeCheck = false;
									}
								}
								if ($excludeCheck) {
									$check = true;
								} else {
									$check = false;
								}
							}
							if (count($value->include) > 0) {
								foreach ($value->include as $word) {
									$includeCheck = false;
									if (mb_strpos(mb_strtolower($new['now']), mb_strtolower($word)) !== false) {
										$includeCheck = true;
									}
								}
								if ($includeCheck) {
									$check = true;
								} else {
									$check = false;
								}
							}
						}
						$offset++;
						if ($offset > 10) {
							$check = true;
							$new = [
								'now' => $value->now,
								'link_now' => $value->link_new
							];
						}
					} while (!$check);
				}
				if (isset($new) && $new['link_now'] == '' && $new['now'] == '') {
					if (($model = \app\modules\helper\models\Items::findOne($value->id)) !== null) {
						$model->error = '1';
						//$model->dt_update = time();
						$model->save(FALSE, ['error', 'dt_update']);
						return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $value->now, 'link_new' => $value->link_new, 'error' => '1'];
					}
				}
				$new['now'] = ($new['now'] != '') ? $new['now'] : $new['link_now'];
				if (($template->update_type == '0' && (($new['now'] != $value->now && $new['now'] != $value->new) || ($new['now'] == $value->now && $new['now'] != $value->new))) || 
					($template->update_type == '1' && ($new['link_now'] != $value->link_new)) || 
					($value->error == '1')) {
					if (($model = \app\modules\helper\models\Items::findOne($value->id)) !== null) {
						if ($new['now'] != $value->new) {
							$model->new = $new['now'];
							$model->link_new = $new['link_now'];
							$model->dt_update = time();
						}
						$model->error = '0';
						$model->save();
						return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $new['now'], 'link_new' => $new['link_now'], 'error' => $model->error];
					}
				} else if (!$only_new && ($new['now'] != $value->now && $new['now'] == $value->new)) {
					return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $value->new, 'link_new' => $value->link_new, 'error' => $value->error];
				}
			});
		}
		return json_encode($Thread->Run());
	}	

	/**
	 * Creates a new Items model.
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new Items();
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else if (!$model->offset) {
			$model->offset = 0;
		}
		return $this->render('create', ['model' => $model]);
	}

	/**
	 * Updates an existing Items model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate($id) {
		$model = $this->findModel($id);
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('?? ?????? ?????? ???????? ???? ?????? ????????????????');
		}
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else if (!$model->offset) {
			$model->offset = 0;
		}
		return $this->render('update', ['model' => $model]);
	}

	/**
	 * View an existing Items model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionView($id) {
		$model = $this->findModel($id);
		if (\Yii::$app->request->post()) {
			$newModel = $this->copyModel($model);
			return $this->redirect(['index']);
			//return $this->redirect(['update', 'id' => $newModel->id]);
		}
		return $this->render('view', ['model' => $model]);
	}

	/**
	 * Copies an existing Items model to user.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionCopy($id) {
		$model = $this->findModel($id);
		if ($this->copyModel($model)) {
			return true;
		}
		return false;
	}

	/**
	 * Deletes an existing Items model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id) {
		$model = $this->findModel($id);
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('?? ?????? ?????? ???????? ???? ?????? ????????????????');
		}
		$model->delete();

		return $this->redirect(Yii::$app->request->referrer ? : ['index']);
	}

	/**
	 * Deletes an existing ItemsHistory model.
	 * If deletion is successful, the browser will be redirected to the 'history' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionHistoryDelete($id = null, $item_id = null) {
		if ($id) {
			$model = ItemsHistory::findOne($id);
			if (!$model) {
				throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
			}
			$user = Yii::$app->user;
			if (!$user->identity->admin && $user->id != $model->item->user_id) {
				throw new \yii\web\ForbiddenHttpException('?? ?????? ?????? ???????? ???? ?????? ????????????????');
			}
			$model->delete();
		} else if ($item_id) {
			$model = $this->findModel($item_id);
			$user = Yii::$app->user;
			if (!$user->identity->admin && $user->id != $model->user_id) {
				throw new \yii\web\ForbiddenHttpException('?? ?????? ?????? ???????? ???? ?????? ????????????????');
			}
			ItemsHistory::deleteAll(['item_id' => $item_id]);
		}

		return $this->redirect(Yii::$app->request->referrer ? : ['history']);
	}

	public function actionDeleteAllDeleted() {
		$user = Yii::$app->user;
		if (!$user->identity->admin) {
			throw new \yii\web\ForbiddenHttpException('?? ?????? ?????? ???????? ???? ?????? ????????????????');
		}
		Items::deleteAllDeleted();

		return $this->redirect(Yii::$app->request->referrer ? : ['index']);
	}

	/**
	 * Finds the Items model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Items the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = Items::findOne($id)) !== null) {
			return $model;
		}
		
		throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
	}


	private function copyModel($model) {
		$newModel = new Items();
		$newModel->load($model->attributes, '');
		unset($newModel->id);
		$newModel->user_id = Yii::$app->user->id;
		if ($newModel->save()) {
			return $newModel;
		}
		return false;
	}

	public function actionDownload($url) {
		$explode = explode('/', urldecode($url));
		$filename = end($explode);
		$filename = ($filename == 'torrent') ? array_slice($explode, -2, 1)[0] : $filename;
		if (mb_stripos($filename, '.torrent') === false) {
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
			return Yii::$app->response->sendFile($file);
		}
	}

	public function actionTest() {
	}

	public function actionYtPlaylist($url) {
		$file = Youtube::getPlaylist($url);
		return \Yii::$app->response->sendFile($file);
	}
}