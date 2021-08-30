<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

$form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]);
$attributes = [
		'login' => [],
		'password' => [],
		'id_telegram' => [],
		'show_all' => ['type' => Form::INPUT_CHECKBOX],
		'copying' => ['type' => Form::INPUT_CHECKBOX],
	];
if (Yii::$app->user->identity->admin) {
	$attributes['admin'] = ['type' => Form::INPUT_CHECKBOX];
}
echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 3,
	'attributes' => $attributes,
]);

echo Html::button('Сохранить', ['type'=>'submitButton', 'class'=>'btn btn-primary']);
ActiveForm::end();
?>