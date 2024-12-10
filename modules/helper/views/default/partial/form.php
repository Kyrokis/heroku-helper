<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;
use app\modules\template\models\Template;

$user = Yii::$app->user;
$readonly = $this->context->action->id == 'view';
$form = ActiveForm::begin(['options' => ['autocomplete' => 'off', 'class' => 'items-add'], 'disabled' => $readonly]);
$attributes = [
	'link' => [],
	[
		'columns' => 2,
		'labelOptions' => ['class' => 'hidden'],
		'attributes' => [
			'id_template' => [
				'label' => 'Шаблон',
				'type' => Form::INPUT_DROPDOWN_LIST,
				'items' => Template::all(),
			],
			'offset' => [
				'label' => 'Смещение',
				'type' => Form::INPUT_TEXT,
			],
		],
	],
	[
		'columns' => 2,
		'labelOptions' => ['class' => 'hidden'],
		'attributes' => [
			'title' => ['label' => 'Название'],
			'link_new' => ['label' => 'Ссылка на новинку'],
		],
	],
	'link_alter' => [],
	'now' => ['type' => Form::INPUT_TEXTAREA],
	'new' => ['type' => Form::INPUT_TEXTAREA],
	[
		'columns' => 2,
		'labelOptions' => ['class' => 'hidden'],
		'attributes' => [
			'include' => [
				'label' => 'Это должно быть',
				'type' => Form::INPUT_LIST_BOX,
				'items' => ($model->include ? : []),
				'options' => ['multiple' => true],
				'fieldConfig' => [
					'addon' => [
						'contentAfter' => 	Html::tag('input', null, ['class' => 'form-control new-word']) . 
											Html::button('Добавить', ['type' => 'button', 'class' => 'btn btn-info add-word']) . 
											Html::button('Удалить', ['type' => 'button', 'class' => 'btn btn-info del-word']),
						'groupOptions' => ['class'=>'group-include']
					]
				]
			],
			'exclude' => [
				'label' => 'Это не должно быть',
				'type' => Form::INPUT_LIST_BOX,
				'items' => ($model->exclude ? : []),
				'options' => ['multiple' => true],
				'fieldConfig' => [
					'addon' => [
						'contentAfter' => 	Html::tag('input', null, ['class' => 'form-control new-word']) . 
											Html::button('Добавить', ['type' => 'button', 'class' => 'btn btn-info add-word']) . 
											Html::button('Удалить', ['type' => 'button', 'class' => 'btn btn-info del-word']),
						'groupOptions' => ['class'=>'group-exclude']
					]
				]
			],
		],
	],
	'link_img' => [],
];
if ($user->identity->admin) {
	$attributes[] = [
		'columns' => 2,
		'labelOptions' => ['class' => 'hidden'],
		'attributes' => [
			'user_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => User::all(), 'label' => 'Пользователь'],
			'error' => ['type' => Form::INPUT_CHECKBOX, 'label' => 'Ошибка', 'enclosedByLabel' => false],
			'del' => ['type' => Form::INPUT_CHECKBOX, 'label' => 'Удален', 'enclosedByLabel' => false],
		]
	];
}

echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 2,
	'attributes' => $attributes,
]);
if (!$readonly) {
	echo Html::button('Загрузить', ['type' => 'button', 'class' => 'btn btn-info get-data']);
	echo Html::button('Сохранить', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
} else {
	echo Html::button('Копировать', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
}
ActiveForm::end();
?>