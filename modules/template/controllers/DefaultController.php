<?
namespace app\modules\template\controllers;

use Yii;
use yii\web\Controller;
use app\modules\template\models\Template;


/**
 * Controller for template module
 */
class DefaultController extends Controller {

	public $title = 'Template';

	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action) {
		$user = Yii::$app->user;
		if ($user->isGuest) {
			$this->redirect($user->loginUrl);
		}

		return parent::beforeAction($action);
	}

	/**
	 * Index
	 * @return json
	 */
	public function actionIndex() {
		$model = new Template();
		$model->setScenario(Template::SCENARIO_SEARCH);
		$model->user_id = Yii::$app->user->id;
		$model->load(\Yii::$app->request->get());
		return $this->render('index', ['model' => $model]);
	}

	/**
	 * Creates a new Template model.
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new Template();
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			//return $this->redirect(['index']);
		} else if (!$model->type) {
			$model->type = 0;
		}

		return $this->render('create', ['model' => $model]);
	}

	/**
	 * Updates an existing Template model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate($id) {
		$model = $this->findModel($id);
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
		}
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			//return $this->redirect(['index']);
		}

		return $this->render('update', ['model' => $model]);
	}

	/**
	 * View an existing Template model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionView($id) {
		$model = $this->findModel($id);
		$model->load(null);
		if (\Yii::$app->request->post()) {
			$newModel = $this->copyModel($model);
			return $this->redirect(['index']);
			//return $this->redirect(['update', 'id' => $newModel->id]);
		}
		return $this->render('view', ['model' => $model]);
	}

	/**
	 * Copies an existing Template model to user.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionCopy($id) {
		$model = $this->findModel($id);
		if ($this->copyModel($model)) {
			return true;
		}
		return false;
	}

	/**
	 * Deletes an existing Template model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id) {
		$model = $this->findModel($id);
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
		}
		$model->delete();

		return $this->redirect(Yii::$app->request->referrer ? : ['index']);
	}

	/**
	 * Finds the Template model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Template the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = Template::findOne($id)) !== null) {
			return $model;
		}
		
		throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
	}

	private function copyModel($model) {
		$newModel = new Template();
		$newModel->load($model->attributes, '');
		unset($newModel->id);
		$newModel->user_id = Yii::$app->user->id;
		if ($newModel->save(false)) {
			return $newModel;
		}
		return false;
	}

	public function actionTest() {
	}
}