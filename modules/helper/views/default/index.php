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
		if ($data->now != $data->new) {
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
			'filterType' => GridView::FILTER_SELECT2,
			'filter' => User::all(), 
			'filterWidgetOptions' => [
				'pluginOptions' => ['placeholder' => '',  'allowClear' => true],
			],
			'filterInputOptions' => ['multiple' => true, /*'disabled' => !\Yii::$app->user->identity?->admin*/],
		],
		[
			'attribute' => 'title',
			'format' => 'raw',
			'value' => function ($data) {
				$link = $data->link;
				if ($data->template->name == 'vk.com') {
					$link = 'https://www.vk.com/club' . mb_substr($data->link, 1);
				} else if ($data->template->name == 'mangadex.org') {
					$link = 'https://mangadex.org/title/' . explode(',', $data->link)[0];
				} else if ($data->template->name == 'proxyrarbg.org') {
					$link = 'https://proxyrarbg.org/torrents.php?search=' . $data->link;
				} else if ($data->template->name == 'rss') {
					$link = explode(',', $data->link);
					$link = end($link);
				} else if ($data->template->id == 8) {
					$link = str_replace('.si', '.ink', $data->link);
				} else if ($data->template->name == 'SubsPlease') {
					$link = 'https://nyaa.ink/user/subsplease?f=0&c=0_0&q=' . urlencode($data->link);
				}
				return Html::a($data->title, $link, ['target' => '_blank']) . ' ' . Html::a('<span class="glyphicon glyphicon-time"></span>', ['/helper/default/history', 'ItemsHistory[item_id][]' => $data->id], ['style' => 'color: #6c757d!important;', 'target' => '_blank', 'data-pjax' => '0']);
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
		[
			'attribute' => 'now',
			'format' => 'raw',
			'value' => function ($data) {
				$text = nl2br(StringHelper::truncate($data->now, 100, '...', null, true));
				$tooltip = Html::tag('span', $text, [
					'title' => $data->now,
					'data-toggle' => 'tooltip',
				]);
				return $tooltip;
			},
			'filter' => false,
		],
		[
			'attribute' => 'new',
			'format' => 'raw',
			'value' => function ($data) {
				$out = '';
				if ($data->new) {
					$text = nl2br(StringHelper::truncate($data->new, 100, '...', null, true));
					$tooltip = Html::tag('span', $text, [
						'title' => $data->new,
						'data-toggle' => 'tooltip',
					]);
					if ($data->link_new) {
						$out = Html::a($tooltip, Template::getFullLink($data->link_new, $data->id_template), ['target' => '_blank', 'data-pjax' => '0']);
					} else {
						$out = $tooltip;
					}
				}
				return $out;
			},
			'filter' => false,
		],
		[
			'attribute' => 'dt_update',
			'format' => 'raw',
			'value' => function ($data) {
				$dt_update = $data->dt_update ? Str::dateEngToRu(date('d F H:i', $data->dt_update)) : '';
				$estimate = $data->estimate;
				$title = 'Ожидайте';
				if ($estimate) {
					$estimate_start = Str::dateEngToRu(date('d F H:i', $data->dt_update + $estimate[0]));
					$estimate_end = Str::dateEngToRu(date('d F H:i', $data->dt_update + $estimate[1]));
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
					if ($model->now != $model->new) {
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
			'options' => [
				'width' => '55px',
			],
			
		]
	]
]);