<?php

namespace app\components\jobs;

use Yii;
use yii\base\BaseObject;
use yii\helpers\Html;
use app\modules\template\models\Template;
use app\components\helper\Helper;
use app\modules\helper\models\Items;

class Helping extends BaseObject implements \yii\queue\JobInterface {

	public $value;
	public $only_new;
	public $telegram = false;
	public $idTelegram;

	public function execute($queue) {
		$value = $this->value;
		$only_new = $this->only_new;
		$telegram = $this->telegram;
		$idTelegram = $this->idTelegram;

		// Get item info
		var_dump($value->title . ' - ' . $value->link);
		$media = [];
		$template = Template::findOne($value->id_template);
		$new = Helper::getData($template, $value);
		if (isset($new) && $new['link_new'] == '' && $new['now'] == '') {
			if (($model = Items::findOne($value->id)) !== null) {
				$model->error = '1';
				//$model->dt_update = time();
				$model->save(FALSE, ['error', 'dt_update']);
				return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $value->now, 'link_new' => $value->link_new, 'error' => '1'];
			}
		}
		$new['now'] = ($new['now'] != '') ? $new['now'] : $new['link_new'];
		$media = isset($new['media']) ? $new['media'] : '';
		$item = false;
		if (($template->update_type == '0' && (($new['now'] != $value->now && $new['now'] != $value->new) || ($new['now'] == $value->now && $new['now'] != $value->new))) || 
			($template->update_type == '1' && ($new['link_new'] != $value->link_new)) || 
			($value->error == '1')) {
			if (($model = Items::findOne($value->id)) !== null) {
				$error = '-1';
				if ($new['now'] != $value->new) {
					$model->new = $new['now'];
					$model->link_new = $new['link_new'];
					$model->dt_update = time();
					$error = '0';
				}
				$model->error = '0';
				$model->save();						
				$item = ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $new['now'], 'link_new' => $new['link_new'], 'media' => $media, 'error' => $error];
			}
		} else if (!$only_new && ($new['now'] != $value->now && $new['now'] == $value->new)) {
			$item = ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $value->new, 'link_new' => $value->link_new, 'media' => $media, 'error' => $value->error];
		}

		//send info in the telegram message
		if ($telegram && ($item && $item['error'] == '0')) {
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
			Yii::$app->controllerNamespace = 'app\modules\telegram\controllers';
			$result = Yii::$app->runAction('default/get-torrent', ['url' => $fullLink, 'idTelegram' => $idTelegram]);
			return Yii::debug($result);
		}
	}
}