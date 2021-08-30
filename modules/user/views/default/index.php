<?

use app\components\Str;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/* @var $this \yii\web\View */
/* @var $model \app\modules\user\models\User */

$this->params['breadcrumbs'][] = $this->context->title;
$this->params['menuSide']['create'] = true;

echo GridView::widget([
	'dataProvider' => $model->search(),
	'filterModel' => $model,
	'pjax' => true,
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
			'attribute' => 'login',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->login;
			},
			'filterInputOptions' => [
				'autocomplete' => 'off',
				'class' => 'form-control'
			],
		],
		[
			'attribute' => 'id_telegram',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->id_telegram;
			},
			'filterInputOptions' => [
				'autocomplete' => 'off',
				'class' => 'form-control'
			],
		],
		[
			'attribute' => 'show_all',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->show_all ? 'Да' : 'Нет';
			},
			'width' => '36px',
			'filter' => false,
		],
		[
			'attribute' => 'dt_helping',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->dt_helping ? Str::dateEngToRu(date('d F H:i', $data->dt_helping)) : '';
			},
			'filter' => false,
			'width' => '135px',
		],
		[
			'attribute' => 'dt_create',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->dt_create ? Str::dateEngToRu(date('d F H:i', $data->dt_create)) : '';
			},
			'filter' => false,
			'width' => '135px',
		],
		[
			'attribute' => 'dt_update',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->dt_update ? Str::dateEngToRu(date('d F H:i', $data->dt_update)) : '';
			},
			'filter' => false,
			'width' => '135px',
		],
		[
			'class' => yii\grid\ActionColumn::className(),
			'template' => '{check} {update} {delete}',
			'options' => [
				'width' => '55px',
			]
		]
	]
]);