<?

use app\components\Str;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use app\modules\user\models\User;
use app\modules\template\models\Template;

/* @var $this \yii\web\View */
/* @var $model \app\modules\helper\models\Items */

$this->params['breadcrumbs'][] = $this->context->title;
$this->params['menuSide']['create'] = true;

$editUrl = $model->attributes;
array_unshift($editUrl, 'mass-update');

echo GridView::widget([
	'dataProvider' => $model->search(),
	'filterModel' => $model,
	'pjax' => true,
	'hover' => true,
	'striped' => false,
	'toolbar' => [
		[
			'content' => Html::a('Редактировать', $editUrl, [
								'class' => 'btn btn-default',
								'style' => 'margin-right: 10px;',
								'data-pjax' => '0',
								'target' => '_blank'
							]) . ' ' .
						Html::a('Ожидания', ['/helper/default/calendar'], [
								'class' => 'btn btn-default',
								'data-pjax' => '0',
								'target' => '_blank'
							]) .
						Html::a('История', ['/helper/default/history'], [
								'class' => 'btn btn-default',
								'data-pjax' => '0',
								'target' => '_blank'
							]) .
						Html::a('<i class="glyphicon glyphicon-repeat"></i>', '#', [
								'class' => 'btn btn-default helping',
								'title' => 'Обновить'
							]) . 
						Html::a('<i class="glyphicon glyphicon-remove"></i>', [''], [
								'class' => 'btn btn-default',
								'title'=> 'Сбросить фильтр'								
							]),
		],
	],
	'panel' => [
		'type' => GridView::TYPE_DEFAULT,
	],
	'rowOptions' => function ($data) {
		if ($data->uncheckedCount > 0) {
			return ['class' => 'info'];
		} else if ($data->error) {
			return ['class' => 'danger'];
		} 
	},
	'columns' => [
		[
			'attribute' => 'id',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->id;
			},
			'width' => '36px',
			'filter' => false,
		],
		[
			'attribute' => 'user_id',
			'label' => 'Пользователь',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->user->login;
			},
			'width' => '150px',
			'filter' => false,
			/*'filterType' => GridView::FILTER_SELECT2,
			'filter' => User::all(), 
			'filterWidgetOptions' => [
				'pluginOptions' => ['placeholder' => '',  'allowClear' => true],
			],
			'filterInputOptions' => ['multiple' => true, 'disabled' => !\Yii::$app->user->identity?->admin],*/
		],
		[
			'attribute' => 'title',
			'format' => 'raw',
			'value' => function ($data) {
				$link = $data->link;
				if ($data->template->name == 'vk.com') {
					$link = 'https://www.vk.com/club' . mb_substr($link, 1);
				} else if ($data->template->name == 'mangadex.org') {
					$link = 'https://mangadex.org/title/' . explode(',', $link)[0];
				} else if ($data->template->name == 'proxyrarbg.org') {
					$link = 'https://proxyrarbg.org/torrents.php?search=' . $link;
				} else if ($data->template->name == 'rss') {
					$link = explode(',', $link);
					$link = end($link);
				} else if ($data->template->id == 8) {
					$link = $link . '&fresh_load_' . time();
					//$link = str_replace('nyaa.si', 'nyaa.digital', $link);
					$link = 'https://freeproxy.io/o.php?b=4&u=' . urlencode($link);
				} else if ($data->template->id == 49) {
					$link = 'https://www.reddit.com/r/manga/search/?q=' . urlencode('"[DISC] ' . $link . ' -"') . '&type=posts&sort=new';
				} 
				return Html::a($data->title, $link, ['target' => '_blank']) . ' ' . Html::a('<span class="glyphicon glyphicon-time"></span>', ['/helper/default/history', 'ItemsHistory[item_id][]' => $data->id, 'ItemsHistory[checked]' => ($data->firstUnchecked ? '0' : '')], ['style' => 'color: #6c757d!important;', 'target' => '_blank', 'data-pjax' => '0']);
			},
			'filterInputOptions' => [
				'autocomplete' => 'off',
				'class' => 'form-control'
			],
		],
		[
			'attribute' => 'id_template',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->template->name;
			},
			'width' => '200px',
			'filterType' => GridView::FILTER_SELECT2,
			'filter' => Template::sortedAll(), 
			'filterWidgetOptions' => [
				'pluginOptions' => ['placeholder' => '',  'allowClear' => true],
			],
			'filterInputOptions' => ['multiple' => true],
		],
		/*[
			'attribute' => 'now',
			'format' => 'raw',
			'value' => function ($data) {
				if ($lastChecked = $data->lastChecked) {
					$lastChecked = $data->lastChecked;
					$now = $lastChecked->now;
					$link = $lastChecked->link;
				} else if ($data->now) {
					$now = $data->now;
					$link = '';
				}
				if ($now) {
					$text = nl2br(StringHelper::truncate($now, 100, '...', null, true));
					$tooltip = Html::tag('span', $text, [
						'title' => $now,
						'data-toggle' => 'tooltip',
					]);
					if ($link) {
						$out = Html::a($tooltip, $link, ['target' => '_blank', 'data-pjax' => '0']);
					} else {
						$out = $tooltip;
					}
				}
				return $out;
			},
			'filter' => false,
		],*/
		[
			'attribute' => 'now',
			'format' => 'raw',
			'label' => 'Первый непросмотренный',
			'value' => function ($data) {
				$out = '';
				$checkbox = '';
				if ($data->uncheckedCount > 1 && $firstUnchecked = $data->firstUnchecked) {
					$now = $firstUnchecked->now;
					$link = $data->link_alter ? : $firstUnchecked->link;
					$checkbox = Html::checkbox('checked[]', $firstUnchecked->checked, ['data-id' => $firstUnchecked->id, 'data-type' => 'first', 'class' => 'checkHistory']) . ' ';
				} else if ($data->now) {
					$now = $data->now;
					$link = $data->link_alter ? : Template::getFullLink($data->link_new, $data->id_template);
				}
				if ($now) {
					$text = nl2br(StringHelper::truncate($now, 100, '...', null, true));
					$tooltip = Html::tag('span', $text, [
						'title' => $now,
						'data-toggle' => 'tooltip',
					]);
					if ($link) {
						$out = Html::a($tooltip, $link, ['target' => '_blank', 'data-pjax' => '0']);
					} else {
						$out = $tooltip;
					}
				}
				return $checkbox . $out;
			},
			'filter' => false,
		],
		[
			'attribute' => 'new',
			'format' => 'raw',
			'label' => 'Последний непросмотренный',
			'value' => function ($data) {
				$out = '';
				$checkbox = '';
				if ($lastUnchecked = $data->lastUnchecked) {
					$new = $lastUnchecked->now;
					$link = $data->link_alter ? : $lastUnchecked->link;
					$checkbox = Html::checkbox('checked[]', $lastUnchecked->checked, ['data-id' => $lastUnchecked->id, 'data-type' => 'last', 'class' => 'checkHistory']) . ' ';
				} else if ($data->new) {
					$new = $data->new;
					$link = $data->link_alter ? : Template::getFullLink($data->link_new, $data->id_template);
				}
				if ($new) {
					$text = nl2br(StringHelper::truncate($new, 100, '...', null, true));
					$tooltip = Html::tag('span', $text, [
						'title' => $new,
						'data-toggle' => 'tooltip',
					]);
					if ($link) {
						$out = Html::a($tooltip, $link, ['target' => '_blank', 'data-pjax' => '0']);
					} else {
						$out = $tooltip;
					}
				}
				$additional = $data->link_additional ? ' ' . Html::a('<span class="glyphicon glyphicon-arrow-right"></span>', $data->link_additional, ['style' => 'color: #6c757d!important;', 'target' => '_blank', 'data-pjax' => '0']) : '';
				return $checkbox . $out . $additional;
			},
			'filter' => false,
		],
		[
			'attribute' => 'dt_update',
			'format' => 'raw',
			'value' => function ($data) {
				$dt = $data->lastValue->dt;
				$dt_update = $dt ? Str::dateEngToRu(date('d F H:i', $dt)) : '';
				$estimate = $data->estimate;
				$title = 'Ожидайте';
				if ($estimate) {
					$estimate_start = Str::dateEngToRu(date('d F H:i', $dt + $estimate[0]));
					$estimate_end = Str::dateEngToRu(date('d F H:i', $dt + $estimate[1]));
					$title = 'Ожидается: ' . $estimate_start . ' - ' . $estimate_end;
				}
				$tooltip = Html::tag('span', $dt_update, [
						'title' => $title,
						'data-toggle' => 'tooltip',
					]);
				return $tooltip;
			},
			'filter' => false,
			'width' => '135px',
		],
		[
			'class' => yii\grid\ActionColumn::className(),
			'buttons' => [
				'check' => function ($url, $model) {
					$button = '';
					if ($model->uncheckedCount > 0) {
						$button = Html::a('<span class="glyphicon glyphicon-film text-success"></span> <span class="glyphicon glyphicon-ok text-success"></span>', '#', [
							'class' => 'check',
							'title' => 'Check',
							'data-id' => $model->id,
						]) . '<br>';
					}
					return $button;
				},
				'copy' => function ($url, $model) {
					$button = '';
					if (Yii::$app->user->identity?->copying == '1') {
						$button = Html::a('<span class="glyphicon glyphicon-film"></span> <span class="glyphicon glyphicon-plus"></span>', $url, [
							'class' => 'copy',
							'title' => 'Copy',
							'data-id' => $model->id,
							'data-module' => 'helper',
						]) . '<br>';
					}
					return $button;
				},
				'view' => function ($url, $model) {
					return Html::a('<span class="glyphicon glyphicon-sunglasses"></span>', $url, [
						'title' => Yii::t('yii', 'View'),
						'data-pjax' => '0',
					]);
				},
			],
			'template' => '{check} {copy} {view} {update} {delete}',
			'visibleButtons' => [
				'check' => function ($model) {
					$user = Yii::$app->user;
					return ($user->identity?->admin || $user->id == $model->user_id);
				},
				'update' => function ($model) {
					$user = Yii::$app->user;
					return ($user->identity?->admin || $user->id == $model->user_id);
				},
				'delete' => function ($model) {
					$user = Yii::$app->user;
					return ($user->identity?->admin || $user->id == $model->user_id);
				},
				'view' => function ($model) {
					$user = Yii::$app->user;
					return ($user->id != $model->user_id);
				},

			],
			'contentOptions' => ['style' => 'width: 65px'],
			'options' => [

			],
			
		]
	]
]);