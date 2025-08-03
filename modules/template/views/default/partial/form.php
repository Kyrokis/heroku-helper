<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\user\models\User;
use app\modules\template\models\Template;

$user = Yii::$app->user;
$readonly = $this->context->action->id == 'view';
$form = ActiveForm::begin(['options' => ['autocomplete' => 'off'], 'disabled' => $readonly]);
$attributes = [
	'type' => [
		'type' => Form::INPUT_DROPDOWN_LIST,
		'items' => Template::typeList(),
	],
	'name' => [],
	'title1' => [],
	'title2' => [],
	'new1' => [],
	'new2' => [],
	'link_new1' => [],
	'link_new2' => [],
	'dt1' => [],
	'dt2' => [],
	'link_img1' => [],
	'link_img2' => [],
	'full_link1' => [],
	'full_link2' => [],
	'update_type' =>  ['type' => Form::INPUT_CHECKBOX],	
];
if ($user->identity->admin) {
	$attributes['user_id'] = ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => User::all(), 'options' => ['prompt' => '---']];
}
echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 2,
	'attributes' => $attributes, 
]);

if (!$readonly) {
	echo Html::button('Сохранить', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
} else {
	echo Html::button('Копировать', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
}
ActiveForm::end();
?>