<?


/* @var $this \yii\web\View */
/* @var $model \app\models\Items */
$this->params['breadcrumbs'][] = ['label' => $this->context->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Массовое редактирование ВСЕ НЕПУСТЫЕ ПОЛЯ ЗАМЕНЯЮТ СОБОЙ ТО, ЧТО БЫЛО РАНЬШЕ'];

echo $this->render('partial/mass-form', ['model' => $model]);
