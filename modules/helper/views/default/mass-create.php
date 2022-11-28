<?
use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \app\models\Items */
$this->params['breadcrumbs'][] = ['label' => $this->context->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Массовое добавление'];

$user = Yii::$app->user;
$form = ActiveForm::begin(['options' => ['autocomplete' => 'off', 'class' => 'items-add']]);
$attributes = [
	'import' => ['type' => Form::INPUT_TEXTAREA],
];

echo Form::widget([
	'model' => $model,
	'form' => $form,
	'attributes' => $attributes,
]);
echo Html::button('Сохранить', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
ActiveForm::end();
