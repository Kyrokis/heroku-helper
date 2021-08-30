<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \app\models\User */

$this->params['breadcrumbs'][] = ['label' => $this->context->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Регистрация'];
?>
<div class="login-box">
	<div class="login-logo">
        Регистрация
	</div>
	<div class="login-box-body">
		<?
			$form = ActiveForm::begin();
			echo Form::widget([
				'model' => $model,
				'form' => $form,
				'attributes' => [
					'login' => [],
					'id_telegram' => [],
					'password' => [
						'type' => Form::INPUT_PASSWORD,
					],
				], 
			]);
			
			echo Html::submitButton('Зарегистрироваться', ['class'=>'btn btn-primary']);
			echo Html::a('Уже есть аккаунт', ['login'], ['class'=>'btn btn-primary pull-right']);
			ActiveForm::end(); 
		?>
	</div>
</div>