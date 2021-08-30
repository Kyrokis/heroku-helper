<?


/* @var $this \yii\web\View */
/* @var $model \app\modules\template\models\Template */

$this->params['breadcrumbs'][] = ['label' => $this->context->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Просмотр'];

echo $this->render('partial/form', ['model' => $model]);
