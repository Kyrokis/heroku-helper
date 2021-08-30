<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use yii\helpers\Url;

$form = ActiveForm::begin();
echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 2,
	'attributes' => [
		'send-msg' => [
			'columns' => 1,
			'attributes' => [
				'chatId' => [],
				'text' => [
					'type' => Form::INPUT_TEXTAREA,
				],
			]
		],
		'filler' => [],
	], 
]);
echo Html::button('Отправить сообщение', ['type'=>'button', 'class'=>'btn btn-primary send-msg']);
ActiveForm::end();
?>