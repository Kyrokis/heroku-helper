<?

namespace app\modules\user\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\Items;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $login
 * @property string $password
 * @property integer $id_telegram
 * @property integer $dt_create
 * @property integer $dt_update
 * @property integer $dt_helping
 * @property string $admin
 * @property string $show_all
 * @property string $copying
 * @property string $del
 */
class User extends ActiveRecord implements \yii\web\IdentityInterface {

	const SCENARIO_SEARCH = 'search';
	const SCENARIO_CREATE = 'create';
	const SCENARIO_UPDATE = 'update';

	/**
	 * @return string
	 */
	public static function tableName() {
		return 'user';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['id', 'id_telegram', 'dt_create', 'dt_update', 'dt_helping'], 'integer'],
			[['login', 'password', 'admin', 'del', 'show_all', 'copying'], 'string'],
			[['login', 'password'], 'required', 'on' => self::SCENARIO_CREATE],
			[['login'], 'required', 'on' => self::SCENARIO_UPDATE],
			[['login'], 'string', 'max' => 50],
			[['title', 'link'], 'safe', 'on' => self::SCENARIO_SEARCH],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'login' => 'Логин',
			'password' => 'Пароль',
			'id_telegram' => 'ID в телеграме',
			'dt_create' => 'Дата создания',
			'dt_update' => 'Дата обновления',
			'dt_helping' => 'Дата хелпинга',
			'admin' => 'Администратор',
			'show_all' => 'Показывать все',
			'copying' => 'Можно копировать записи',
			'del' => 'Удален',
		];
	}

	/**
	 * Get user items
	 * @return UserFile
	 */
	public function getItems() {
		return $this->hasMany(Items::className(), ['user_id' => 'id']);
	}

	/**
	 * List of id => login
	 * @return array
	 */
	public static function all() {
		return \yii\helpers\ArrayHelper::map(self::find()->select(['id', 'login'])->where(['del' => '0'])->orderBy('id')->all(), 'id', 'login');
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert) {
		if ($insert) {
			$this->dt_create = time();
			$this->auth_key = Yii::$app->security->generateRandomString();
		}
		if ($this->password) {
			$this->password = Yii::$app->security->generatePasswordHash($this->password);
		} else {
			unset($this->password);
		} 
		$this->dt_update = time();
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function delete() {
		$this->del = '1';
		return $this->save(FALSE, ['del']);
	}

	/**
	 * Create DataProvider for GridView.
	 * @return \yii\data\ActiveDataProvider
	 */
	public function search() {
		$query = self::find()->andFilterWhere([
					'id' => $this->id,
					'id_telegram' => $this->id_telegram,
					'del' => '0',
				])
				->andFilterWhere(['ILIKE', 'login', $this->login])
				->orderBy('dt_create, id_telegram, id');

		return new \yii\data\ActiveDataProvider(['query' => $query, 'sort' => false]);
	}

	/**
	 * Count $this->search() entries.
	 * @return int
	 */
	public function count() {
		$model = new self;
		return $model->search()->query->count();
	}

	/**
	 * Finds user by login
	 *
	 * @param string $login
	 * @return static|null
	 */
	public static function findByLogin($login) {
		return static::find()->where(['ILIKE', 'login', $login, false])->andWhere(['del' => '0'])->one();
	}

	/**
	 * {@inheritdoc}
	 */
	public static function findIdentity($id) {
		return static::findOne($id);
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword($password) {
		return Yii::$app->security->validatePassword($password, $this->password);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null) {
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthKey() {
		return $this->auth_key;
	}

	/**
	 * @inheritdoc
	 */
	public function validateAuthKey($authKey) {
		return $this->authKey === $authKey;
	}

	/**
	 * Get time from last helping
	 * @return string
	 */
	public static function getTimeLastHelping($id) {
		$user = static::findOne($id);
		return $user?->dt_helping ? \app\components\Str::dateEngToRu(date('d F H:i', $user->dt_helping)) : '';
	}

}
