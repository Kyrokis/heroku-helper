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
use yii\helpers\ArrayHelper;


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
		if ($action->id == 'helping' || $action->id == 'download') {
			$this->enableCsrfValidation = FALSE;
			$this->layout = FALSE;
		} else if ($user->isGuest) {
			return $this->redirect($user->loginUrl)->send();
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
	public function actionGetData($link, $id_template, $offset = 0, $include = null, $exclude = null) {
		$include = json_decode($include);
		$exclude = json_decode($exclude);
		$template = Template::findOne($id_template);
		if ($template->type == 1) {
			$client = new Client();
			$response = $client->get($link)->send();
			$content = $response->content;
			$items = [
				'title' => Str::explode($template->title, $content),
				'link_img' => Str::explode($template->link_img, $content),
			];
			$offsetCycle = $offset;
			do {
				$items['now'] = Str::explode($template->new, $content, $offsetCycle);
				$items['link_new'] = Str::explode($template->link_new, $content, $offsetCycle);
				$check = self::checkClude($items['now'], $exclude, $include);
				$offsetCycle++;
				if ($offsetCycle > $offset + 9) {
					$check = true;
					$items['now'] = Str::explode($template->new, $content, $offset);
					$items['link_new'] = Str::explode($template->link_new, $content, $offset);
				}
			} while (!$check);
		} else if ($template->type == 2) {
			if ($template->name == 'vk.com') {
				$vk = new VKApiClient();
				$post = $vk->wall()->get(\Yii::$app->params['vkApiKey'], [
								'owner_id' => $link,
								'offset' => $offset,
								'count' => 10,
								'filter' => 'owner',
								'extended' => 1
							]);
				$items = [
					'title' => $post['groups'][0]['name'],
					'link_img' => $post['groups'][0]['photo_200'],
					'link_new' => '/wall' . $link . '_' . $post['items'][0]['id'],
				];
				$offsetCycle = 0;
				do {
					$item = isset($post['items'][$offsetCycle]['copy_history']) ? $post['items'][$offsetCycle]['copy_history'][0] : $post['items'][$offsetCycle];
					$items['now'] = $item['text'];
					$items['link_new'] = '/wall' . $link . '_' . $item['id'];
					$check = self::checkClude($items['now'], $exclude, $include);
					$offsetCycle++;
					if ($offsetCycle > $offset + 9) {
						$check = true;
						$item = isset($post['items'][0]['copy_history']) ? $post['items'][0]['copy_history'][0] : $post['items'][0];
						$items['now'] = $item['text'];
						$items['link_new'] = '/wall' . $link . '_' . $item['id'];
					}
				} while (!$check);
				//$sizes = ['w' => 0, 'z' => 1, 'y' => 2, 'r' => 3, 'q' => 4, 'p' => 5, 'o' => 6, 'x' => 7, 'm' => 8, 's' => 9];
				$sizes = ['w', 'z', 'y', 'r', 'q', 'p', 'o', 'x', 'm', 's'];
				foreach ($item['attachments'] as $attachment) {
					if ($attachment['type'] == 'photo') {
						foreach ($attachment['photo']['sizes'] as $size) {
							$url[array_search($size['type'], $sizes)] = $size['url'];
						}
						//$sizes = array_filter($attachment['photo']['sizes'], fn($key) => ($key['type'] == 'w' || $key['type'] == 'z'));
						\Yii::debug($sizes);
						if ($url) {
							ksort($url);
							$media[] = [
								'type' => 'photo',
								'media' => array_values($url)[0],
							];									
						}
					}
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
				if (isset($link[1]) && $link[1]) {
					$data['translatedLanguage'] = [$link[1]];
				}
				if (isset($link[2]) && $link[2]) {
					$data['groups'] = [$link[2]];
				}
				$client = new Client();
				$response = $client->get('https://api.mangadex.org/chapter', $data)->send();
				$content = $response->data['data'][0];
				$items = [
					'now' => 'Chapter ' . $content['attributes']['chapter'] . ($content['attributes']['title'] ? ': ' . $content['attributes']['title'] : ''),
					'link_new' => $content['id'],
					'link_img' => '',
				];
				foreach ($content['relationships'] as $relationship) {
					if ($relationship['type'] == 'manga') {
						$items['title'] = isset($relationship['attributes']['title']['ja']) ? $relationship['attributes']['title']['ja'] : $relationship['attributes']['title']['en'];
						break;
					}
				}
			} else if ($template->name == 'rss') {
				$link = explode(',', $link);
				$client = new Client();
				$response = $client->get($link[0])->send();
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
			//$query = QueryList::get($link);
			$html = QueryList::get($link, null, ['timeout' => 5])->getHtml();
			$query = QueryList::html($html);
			$items = $query->rules([ 
							'title' => $template->title, 
							'link_img' => $template->link_img
						])
						->query()->getData()->all();
			$offsetCycle = $offset;
			do {
				//
				$newTemplate = [
					'new' => $template->new->getValue(),
					'link_new' => $template->link_new->getValue(),
				];
				$newTemplate['new'][0] = str_replace('{offset}', $offsetCycle, $newTemplate['new'][0]);
				$newTemplate['link_new'][0] = str_replace('{offset}', $offsetCycle, $newTemplate['link_new'][0]);
				$item = $query->rules([
							'now' => $newTemplate['new'], 
							'link_now' => $newTemplate['link_new']
						])
						->query()->getData()->all();
				$items['now'] = $item['now'];
				$items['link_new'] = $item['link_now'];
				//
				$check = self::checkClude($items['now'], $exclude, $include);
				$offsetCycle++;
				if ($offsetCycle > $offset + 9) {
					$check = true;
					//
					foreach ($template->attributes as $key => $attribute) {
						if (is_object($attribute)) {
							$template->$key[0] = str_replace('{offset}', $offset, $template->$key[0]);
						}
					}
					$item = $query->rules([
							'now' => $template->new, 
							'link_now' => $template->link_new
						])
						->query()->getData()->all();
					$items['now'] = $item['now'];
					$items['link_new'] = $item['link_now'];
					//
				}
			} while (!$check);
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
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
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
				$media = [];
				$template = app\modules\template\models\Template::findOne($value->id_template);
				if ($template->type == 1) {
					$client = new \yii\httpclient\Client();
					$response = $client->get($value->link, [], ['timeout' => 5])->send();
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
							break;
						}
						$check = \app\modules\helper\controllers\DefaultController::checkClude($new['now'], $value->exclude, $value->include);
						$offset++;
						if ($offset > $value->offset + 9) {
							$check = true;
							$new = [
								'now' => $value->new,
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
										'count' => 10,
										'filter' => 'owner',
										'extended' => 1
									]);
						$offset = 0;
						do {
							$item = isset($post['items'][$offset]['copy_history']) ? $post['items'][$offset]['copy_history'][0] : $post['items'][$offset];
							$new = [
								'now' => $item['text'],
								'link_now' => '/wall' . $value->link . '_' . $item['id']
							];
							if ($new['now'] == $value->now && $offset != $value->offset) {
								$check = true;
								break;
							}
							$check = \app\modules\helper\controllers\DefaultController::checkClude($new['now'], $value->exclude, $value->include);
							$offset++;
							if ($offset > $value->offset + 9) {
								$check = true;
								$new = [
									'now' => $value->new,
									'link_now' => $value->link_new
								];
							}
						} while (!$check);
						\Yii::debug($offset);
						foreach ($item['attachments'] as $attachment) {
							if ($attachment['type'] == 'photo') {
								$sizes = array_filter($attachment['photo']['sizes'], fn($key) => ($key['type'] == 'w' || $key['type'] == 'z'));
								\Yii::debug($sizes);
								if ($sizes) {
									$media[] = [
										'type' => 'photo',
										'media' => array_values($sizes)[0]['url'],
									];									
								}
							}
						}
					} else if ($template->name == 'mangadex.org') {
						$link = explode(',', $value->link);
						$data = [
							'manga' => $link[0],
							'limit' => 1,
							'offset' => $value->offset,
							'order' => ['chapter' => 'desc'],
						];
						if (isset($link[1]) && $link[1]) {
							$data['translatedLanguage'] = [$link[1]];
						}
						if (isset($link[2]) && $link[2]) {
							$data['groups'] = [$link[2]];
						}
						$client = new \yii\httpclient\Client();
						$response = $client->get('https://api.mangadex.org/chapter', $data)->send();
						\Yii::debug($response->data);
						$content = $response->data['data'][0];
						$new = [
							'now' => 'Chapter ' . $content['attributes']['chapter'] . ($content['attributes']['title'] ? ': ' . $content['attributes']['title'] : ''),
							'link_now' => $content['id']
						];
					} else if ($template->name == 'rss') {
						$link = explode(',', $value->link);
						$client = new \yii\httpclient\Client();
						$response = $client->get($link[0])->send();
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
					$html = \QL\QueryList::get($value->link, null, ['timeout' => 5])->getHtml();
					$query = \QL\QueryList::html($html);
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
							break;
						}
						$check = \app\modules\helper\controllers\DefaultController::checkClude($new['now'], $value->exclude, $value->include);
						$offset++;
						if ($offset > $value->offset + 9) {
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
						return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $new['now'], 'link_new' => $new['link_now'], 'media' => $media, 'error' => $model->error];
					}
				} else if (!$only_new && ($new['now'] != $value->now && $new['now'] == $value->new)) {
					return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $value->new, 'link_new' => $value->link_new, 'media' => $media, 'error' => $value->error];
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
		$model->setScenario(Items::SCENARIO_CREATE);
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
		$model->setScenario(Items::SCENARIO_CREATE);
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
		}
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else if (!$model->offset) {
			$model->offset = 0;
		}
		return $this->render('update', ['model' => $model]);
	}	

	/**
	 * Updates an existing Items model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionMassUpdate() {
		$ids = null;
		if (\Yii::$app->request->get() && count(\Yii::$app->request->get()) > 1) {
			$modelIds = new Items();
			$modelIds->setScenario(Items::SCENARIO_SEARCH);
			$modelIds->load(\Yii::$app->request->get(), '');
			$modelIds->user_id = Yii::$app->user->id;
			$result = $modelIds->searchQuery()->all();
			$ids = ArrayHelper::getColumn($result, 'id');
		}
		$model = new Items();
		$model->setScenario(Items::SCENARIO_MASSUPDATE);
		$model->id = $ids;
		$attrs = ['id_template', 'offset', 'include', 'exclude', 'user_id'];
		if ($model->load(\Yii::$app->request->post())) {
			foreach ($model->id as $id) {
				$item = $this->findModel($id);
				foreach ($attrs as $attr) {
					if ($model->{$attr}) {
						if ($attr == 'include' || $attr == 'exclude') {
							$value = $item->{$attr}->getValue();
							$item->{$attr} = array_merge($value, $model->{$attr});
						} else {
							$item->{$attr} = $model->{$attr};
						}
					}
					if ($model->linkSearch && $model->linkReplace) {
						$item->link = str_replace($model->linkSearch, $model->linkReplace, $item->link);
					}
					if ($model->titleSearch && $model->titleReplace) {
						$item->title = str_replace($model->titleSearch, $model->titleReplace, $item->title);
					}
					$item->save();
				}
			}
			return $this->redirect(['index']);
		}
		return $this->render('mass-update', ['model' => $model]);
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
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
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
				throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
			}
			$model->delete();
		} else if ($item_id) {
			$model = $this->findModel($item_id);
			$user = Yii::$app->user;
			if (!$user->identity->admin && $user->id != $model->user_id) {
				throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
			}
			ItemsHistory::deleteAll(['item_id' => $item_id]);
		}

		return $this->redirect(Yii::$app->request->referrer ? : ['history']);
	}

	public function actionDeleteAllDeleted() {
		$user = Yii::$app->user;
		if (!$user->identity->admin) {
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
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

	public function actionDownload($url, $text = true) {
		$headers;
		$tempName = time();
		$file = Yii::$app->basePath . '/uploads/' . $tempName;
		$fp = fopen($file, 'w+');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
				$len = strlen($header);
				$header = explode(':', $header, 2);
				if (count($header) < 2) // ignore invalid headers
				  return $len;		
				$headers[strtolower(trim($header[0]))][] = trim($header[1]);			
				return $len;
			});
		curl_setopt($ch, CURLOPT_FILE, $fp);
		$responce = curl_exec($ch);
		curl_close($ch);
		\Yii::debug($headers);
		if (file_exists($file) && filesize($file) !== 0) {
			if (isset($headers['content-disposition'][0])) {
				$filename = Str::explode(['filename="', '"'], $headers['content-disposition'][0]);
			} else if (isset($headers['content-type'][0])) {
				$contentType = $headers['content-type'][0];
				if ((mb_stripos($contentType, 'text/html') !== false) || (mb_stripos($contentType, 'application/json') !== false) || (mb_stripos($contentType, 'application/xml') !== false)) {
					unlink($file);
					return false;
				}
				if (mb_stripos($contentType, 'name="') !== false) {
					$filename = Str::explode(['name="', '"'], $contentType);
				} else {
					$filename = $tempName . '.' . explode('/', $contentType)[1];
				}
			}
			$filename = urldecode($filename);
			rename($file, Yii::$app->basePath . '/uploads/' . $filename);
			return $text ? Yii::$app->basePath . '/uploads/' . $filename : Yii::$app->response->sendFile(Yii::$app->basePath . '/uploads/' . $filename);
		} else {
			unlink($file);
		}
		return false;
	}

	public function actionTest() {
	}

	public function actionYtPlaylist($url) {
		$file = Youtube::getPlaylist($url);
		return \Yii::$app->response->sendFile($file);
	}
}