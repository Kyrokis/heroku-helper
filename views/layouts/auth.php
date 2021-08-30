<?

use yii\helpers\Html;
use app\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);

$title = isset($this->context->title) ? $this->context->title : '';
?>
<? $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
	<head>
		<meta charset="<?= Yii::$app->charset ?>"/>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<? /* <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'> */ ?>
		<?= Html::csrfMetaTags() ?>
		<title><?= $title ?></title>
		<? $this->head() ?>
	</head>
	<body>
		<? $this->beginBody() ?>

		<?= $content ?>

		<? $this->endBody() ?>
	</body>
</html>
<? $this->endPage() ?>
