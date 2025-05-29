<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use kartik\widgets\DateTimePicker;
use kartik\datecontrol\DateControl;

$user = Yii::$app->user;
$form = ActiveForm::begin(['options' => ['autocomplete' => 'off', 'class' => 'items-add']]);
$attributes = [
	'now' => [],
	'link' => [],
	'dt' => [
		'type' => Form::INPUT_WIDGET,
		'widgetClass' => DateControl::className(),
		'options' => [
			'type' => DateControl::FORMAT_DATETIME,
			'displayFormat' => 'php:d.m.Y H:i',
			'saveFormat' => 'php:U',
			'ajaxConversion' => true,
			'widgetOptions' => [
				'pluginOptions' => [
					'autoclose' => true,
					'todayHighlight' => true,
				]
			]
		],
	],
];
echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 3,
	'attributes' => $attributes,
]);
echo Html::button('Сохранить', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
ActiveForm::end();
?>