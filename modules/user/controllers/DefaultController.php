<?
namespace app\modules\user\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\modules\user\models\User;
use app\models\LoginForm;


/**
 * Controller for User module
 */
class DefaultController extends Controller {

	public $title = 'User';

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action) {		
		$user = Yii::$app->user;
		if ($action->id != 'signup' && $action->id != 'login' && $user->isGuest) {
			$this->redirect($user->loginUrl);
		}
		if (!$user->isGuest && !$user->identity->admin) {
			if ($action->id == 'update') {
				$userId = Yii::$app->request->get('id');
				if ($userId != $user->id) {
					$this->goHome();
				}
			} else {
				$this->goHome();
			}
		}

		return parent::beforeAction($action);
	}

	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * Index
	 * @return json
	 */
	public function actionIndex() {
		$model = new User();
		$model->setScenario(User::SCENARIO_SEARCH);
		$model->load(\Yii::$app->request->get());
		return $this->render('index', ['model' => $model]);
	}

	/**
	 * Creates a new User model.
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new User();
		$model->setScenario(User::SCENARIO_CREATE);
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else {
			$model->password = '';
		}

		return $this->render('create', ['model' => $model]);
	}

	/**
	 * Updates an existing User model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate($id) {
		$model = $this->findModel($id);
		$model->setScenario(User::SCENARIO_UPDATE);
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else {
			$model->password = '';
		}

		return $this->render('update', ['model' => $model]);
	}

	/**
	 * Deletes an existing User model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id) {
		$this->findModel($id)->delete();

		return $this->redirect(['index']);
	}

	/**
	 * Creates a new User model.
	 * @return mixed
	 */
	public function actionSignup() {
		if (!Yii::$app->user->isGuest) {
			return $this->goHome();
		}
		$this->layout = '/auth';
		$model = new User();
		$model->setScenario(User::SCENARIO_CREATE);
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			if (\Yii::$app->user->login($model)) {
				return $this->goHome();
			}
		} else {
			$model->password = '';
		}

		return $this->render('signup', ['model' => $model]);
	}

	/**
	 * Login
	 */
	public function actionLogin() {
		if (!Yii::$app->user->isGuest) {
			return $this->goHome();
		}
		$this->layout = '/auth';
		$model = new LoginForm();
		if ($model->load(\Yii::$app->request->post()) && $model->login()) {
			return $this->goHome();
		}
		return $this->render('login', ['model' => $model]);
	}

	/**
	 * Logout
	 */
	public function actionLogout() {
		\Yii::$app->user->logout();
		return $this->goHome();
	}

	/**
	 * Finds the User model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return User the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = User::findOne($id)) !== null) {
			return $model;
		}
		
		throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
	}

	public function actionTest() {
	}
}