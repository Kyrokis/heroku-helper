<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;
use app\modules\Helper\models\Items;
use app\modules\template\models\Template;
use kartik\select2\Select2;

$user = Yii::$app->user;
$readonly = $this->context->action->id == 'view';
$form = ActiveForm::begin(['options' => ['autocomplete' => 'off', 'class' => 'items-add'], 'disabled' => $readonly]);
$attributes = [
	'id' => [
		'label' => 'Записи',
		'type' => Form::INPUT_WIDGET,
		'widgetClass' => Select2::classname(),
		'options' => [
			'data' => Items::all(),
			'options' => ['multiple' => true]
		],
	],
	[
		'columns' => 2,
		'label' => 'Редактирование ссылки',
		'attributes' => [
			'linkSearch' => [
				'label' => 'Что',
				'type' => Form::INPUT_TEXT,
			],
			'linkReplace' => [
				'label' => 'На что',
				'type' => Form::INPUT_TEXT,
			],
		],
	],
	[
		'columns' => 2,
		'label' => 'Редактирование названия',
		'attributes' => [
			'titleSearch' => [
				'label' => 'Что',
				'type' => Form::INPUT_TEXT,
			],
			'titleReplace' => [
				'label' => 'На что',
				'type' => Form::INPUT_TEXT,
			],
		],
	],
	[
		'columns' => 4,
		'labelOptions' => ['class' => 'hidden'],
		'attributes' => [
			'id_template' => [
				'label' => 'Шаблон',
				'type' => Form::INPUT_DROPDOWN_LIST,
				'items' => Template::all(),
				'options' => ['prompt' => '---'],
			],
			'offset' => [
				'label' => 'Смещение',
				'type' => Form::INPUT_TEXT,
			],
			'include' => [
				'label' => 'Это должно быть',
				'type' => Form::INPUT_LIST_BOX,
				'items' => [],
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
				'items' => [],
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
];
if ($user->identity->admin) {
	$attributes[] = [
		'columns' => 2,
		'labelOptions' => ['class' => 'hidden'],
		'attributes' => [
			'user_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => User::all(), 'label' => 'Пользователь', 'options' => ['prompt' => '---']],
			'error' => ['type' => Form::INPUT_CHECKBOX, 'label' => 'Ошибка', 'enclosedByLabel' => false],
			'del' => ['type' => Form::INPUT_CHECKBOX, 'label' => 'Удален', 'enclosedByLabel' => false],
		]
	];
}

echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 1,
	'attributes' => $attributes,
]);
echo Html::button('Сохранить', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
ActiveForm::end();
?>