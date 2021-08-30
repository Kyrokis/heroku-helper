<?

namespace app\models;

use Yii;
use app\modules\user\models\User;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends \yii\base\Model {

	/**
	 * Login
	 * @var string
	 */
	public $login;

	/**
	 * Password
	 * @var stirng
	 */
	public $password;

	/**
	 * Remember user auth
	 * @var boolean
	 */
	public $rememberMe = true;

	/**
	 * User data
	 * @var User
	 */
	private $_user = false;

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['login', 'password'], 'required'],
			['rememberMe', 'boolean'],
			['password', 'validatePassword'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'login' => 'Логин',
			'password' => 'Пароль',
			'rememberMe' => 'Запомнить меня'
		];
	}

	/**
	 * Validates the password.
	 * This method serves as the inline validation for password.
	 *
	 * @param string $attribute the attribute currently being validated
	 * @param array $params the additional name-value pairs given in the rule
	 */
	public function validatePassword($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$user = $this->getUser();
			if (!$user || !$user->validatePassword($this->password)) {
				$this->addError($attribute, 'Неверный логин или пароль.');
			}
		}
	}

	/**
	 * Logs in a user using the provided username and password.
	 * @return bool whether the user is logged in successfully
	 */
	public function login()
	{
		if ($this->validate()) {
			return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
		}
		return false;
	}

	/**
	 * Get user data
	 * @return User
	 */
	public function getUser() {
		if ($this->_user === false) {
			$this->_user = User::findByLogin($this->login);
		}

		return $this->_user;
	}
}
