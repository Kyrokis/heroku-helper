<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\ArrayHelper;
use yii\widgets\Breadcrumbs;
use app\modules\user\models\User;
use app\assets\AppAsset;

AppAsset::register($this);

$title = isset($this->context->title) ? $this->context->title : '';
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
	<head>
		<meta charset="<?= Yii::$app->charset ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php $this->registerCsrfMetaTags() ?>
		<title><?= Html::encode($title) ?></title>
		<?php $this->head() ?>
	</head>
	<body class="skin-blue sidebar-mini <?= 'sidebar-collapse' ?>">
		<?php $this->beginBody() ?>
		<div class="wrapper">
			<header class="main-header">
				<? /* Logo */ ?>
				<a href="/" class="logo">
					<span class="logo-mini"><i class="fa fa-eye"></i></span>
					<span class="logo-lg"><?= Yii::$app->name ?></span>
				</a>
				<? /* Header Navbar: style can be found in header.less */ ?>
				<nav class="navbar navbar-static-top" role="navigation">
					<? /* Sidebar toggle button */ ?>
					<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
						<span class="sr-only">Открыть/Cкрыть меню</span>
					</a>

					<div class="navbar-custom-menu">
						<ul class="nav navbar-nav">
							
							<? /* User Account: style can be found in dropdown.less */ ?>
							<li class="dropdown user user-menu">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">
									<i class="glyphicon glyphicon-user"></i>
									<span class="hidden-xs"><?= Yii::$app->user->identity?->login ?></span>
								</a>
								<ul class="dropdown-menu">
									<? /* User image */ ?>
									<li class="user-header" data-user_id="<?= Yii::$app->user->id ?>">
										<p>
											Прошлый раз <?= User::getTimeLastHelping(Yii::$app->user->id) ?>
										</p>
									</li>
									<? /* Menu Footer */ ?>
									<li class="user-footer">
										<div class="pull-left">
											<a href="<?= Url::to(['/user/default/update', 'id' => Yii::$app->user->id]) ?>" class="btn btn-default btn-flat">Редактировать аккаунт</a>
										</div>
										<div class="pull-right">
											<a href="<?= Url::to(['/user/default/logout']) ?>" class="btn btn-default btn-flat">Выход</a>
										</div>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</nav>
			</header>
			<aside class="main-sidebar">
				<section class="sidebar">
					<ul class="sidebar-menu tree" data-widget="tree">
						<li class="header">Ссылки</li>
						<li><a href="<?= Url::to(['/user']) ?>"><i class="fa fa-user"></i> <span>User</span></a></li>
						<li><a href="<?= Url::to(['/telegram']) ?>"><i class="fa fa-laptop"></i> <span>Telegram</span></a></li>
						<li><a href="<?= Url::to(['/template']) ?>"><i class="fa fa-list-alt"></i> <span>Template</span></a></li>
						<li><a href="<?= Url::to(['/helper']) ?>"><i class="fa fa-list"></i> <span>Helper</span></a></li>
					</ul>
					<?//= Yii::$app->c->widget('SideMenu') ?>
				</section>
			</aside>
			<div class="content-wrapper">
				<? // Pjax::begin(['id' => 'pjax-content', 'linkSelector' => '.pjax-content']) ?>
		
				<section class="content-header">
					<?
					$breadcrumbs = ArrayHelper::getValue($this->params, 'breadcrumbs', []);
		
					// добавляем класс для pjax
					foreach ($breadcrumbs as $k => $b) {
						if (is_array($b)) {
							$breadcrumbs[$k]['class'] = isset($breadcrumbs[$k]['class']) ? $breadcrumbs[$k]['class'] . ' pjax-content' : 'pjax-content';
						}
					}
					echo Breadcrumbs::widget([
						'homeLink' => false,
						'links' => $breadcrumbs,
					]);
					?>
					<? if ($note = ArrayHelper::getValue($this->params, 'note')): ?>
						<span class="hidden-xs text-muted">(<?= $note ?>)</span>
					<? endif; ?>
						
					<?
					$menu = ArrayHelper::getValue($this->params, 'menu', []);
					foreach ($menu as $k => $m) {
						if (is_array($m)) {
							// добавляем класс для pjax
							$menu[$k]['linkOptions']['class'] = isset($menu[$k]['linkOptions']['class']) ? $menu[$k]['linkOptions']['class'] . ' pjax-content' : 'pjax-content';
							
							if (isset($m['icon'])) {
								$menu[$k]['label'] = '<i class="' . $m['icon'] . '"></i> <span class="hidden-xs">' . $menu[$k]['label'] . '</span>';
							}
						}
					}
					echo Nav::widget([
						'encodeLabels' => false,
						'items' => $menu,
						'options' => ['class' => 'nav-pills'], // set this to nav-tab to get tab-styled navigation
					]);
					?>
					
					<div class="pull-right">
						<?
						$menuSide = ArrayHelper::getValue($this->params, 'menuSide', []);
						// добавляем класс для pjax
						foreach ($menuSide as $k => $m) {
							if ($k == 'create') {
								$defaultCreate = ['label' => '<i class="glyphicon glyphicon-plus"></i><span class="hidden-sm hidden-xs"> Добавить</span>', 'url' => ['create'], 'linkOptions' => ['class' => 'btn btn-success']];
								if ($m === true) {
									$menuSide[$k] = $defaultCreate;
								} elseif (is_array($m)) {
									$menuSide[$k] = array_merge($defaultCreate, $menuSide[$k]);
								}
							}
							
							if (is_array($m)) {
								$menuSide[$k]['linkOptions']['class'] = isset($menuSide[$k]['linkOptions']['class']) ? $menuSide[$k]['linkOptions']['class'] . ' pjax-content' : 'pjax-content';
							}
						}
						echo Nav::widget([
							'encodeLabels' => false,
							'items' => $menuSide,
							'options' => ['class' => ['widget' => 'pull-left list-unstyled list-inline'], 'style' => 'margin: 4px 15px 15px 0'], // set this to nav-tab to get tab-styled navigation
						]);
						?>
		
						<? $buttons = ArrayHelper::getValue($this->params, 'buttons', []); ?>
					</div>
					<div class="clearfix"></div>
				</section>
				<section class="content">
					<?= $content ?>
				</section>
				<? // Pjax::end() ?>
			</div>
			<footer class="main-footer">
			</footer>
		</div>
		<?php $this->endBody() ?>
	</body>
</html>
<?php $this->endPage() ?>
