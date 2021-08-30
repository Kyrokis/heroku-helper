<?


/* @var $this \yii\web\View */
/* @var $model \app\models\User */
$this->params['breadcrumbs'][] = ['label' => $this->context->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Редактирование'];

echo $this->render('partial/form', ['model' => $model]);
