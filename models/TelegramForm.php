<?

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * TelegramForm is the model behind the telegram form.
 */
class TelegramForm extends Model {

	/**
	 * Chat id
	 * @var string/int
	 */
	public $chatId = 197239226;

	/**
	 * Message text
	 * @var string
	 */
	public $text;

	/**
	 * Message text
	 * @var string
	 */
	public $sendMsg;

	/**
	 * Message text
	 * @var string
	 */
	public $filler;

	/**
	 * @return array the validation rules.
	 */
	public function rules() {
		return [
			[['chatId', 'text'], 'string'],
			[['sendMsg', 'filler'], 'safe']
		];
	}

	/**
	 * @return array customized attribute labels
	 */
	public function attributeLabels() {
		return [
			'chatId' => 'ID юзера',
			'text' => 'Сообщение',
		];
	}

}
