<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \app\models\User */

$this->params['breadcrumbs'][] = ['label' => $this->context->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Авторизация'];
?>
<div class="login-box">
	<div class="login-logo">
        Авторизация
	</div>
	<div class="login-box-body">
		<?
			$form = ActiveForm::begin();
			echo Form::widget([
				'model' => $model,
				'form' => $form,
				'attributes' => [
					'login' => [],
					'password' => [
						'type' => Form::INPUT_PASSWORD,
					],
					'rememberMe' => [
						'type' => Form::INPUT_CHECKBOX,
					],
				], 
			]);
			
			echo Html::submitButton('Войти', ['class'=>'btn btn-primary']);
			echo Html::a('Регистрация', ['signup'], ['class'=>'btn btn-primary pull-right']);
			ActiveForm::end(); 
		?>
	</div>
</div>