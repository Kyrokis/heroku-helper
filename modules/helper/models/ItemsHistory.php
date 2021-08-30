<?

namespace app\modules\helper\models;

use Yii;
use Yii\db\ActiveRecord;
use app\modules\template\models\Template;

/**
 * This is the model class for table "items_history".
 *
 * @property integer $id
 * @property integer $item_id
 * @property string $now
 * @property string $link
 * @property integer $dt
 */
class ItemsHistory extends ActiveRecord {

	const SCENARIO_SEARCH = 'search';

	/**
	 * @var string dt start
	 */
	public $dt_start;

	/**
	 * @var string dt end
	 */
	public $dt_end;

	/**
	 * @return string
	 */
	public static function tableName() {
		return 'items_history';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['id', 'item_id', 'dt'], 'integer'],
			[['now', 'link'], 'string'],
			[['item_id', 'dt'], 'required'],
			[['dt_start', 'dt_end'], 'safe'],
		];
	}

	/**
	 * Item
	 * @return \app\models\Items
	 */
	public function getItem() {
		return $this->hasOne(Items::className(), ['id' => 'item_id']);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'item_id' => 'ID итема',
			'now' => 'Значение',
			'link' => 'Ссылка',
			'dt' => 'Дата изменения',
			'dt_start' => 'Начало периода',
			'dt_end' => 'Конец периода',
		];
	}

	/**
	 * Add item's history
	 * @param int $item_id - ID item
	 * @param stirng $now - Now value
	 * @param stirng $link - Now link
	 * @return boolean
	 */
	public static function add($item_id, $now, $link) {
		$model = new self;
		$model->item_id = $item_id;
		$model->now = $now;
		$model->link = $link;
		$model->dt = time();
		return $model->save();
	}

	/**
	 * Create DataProvider for GridView.
	 * @return \yii\data\ActiveDataProvider
	 */
	public function search() {
		if ($this->item_id) {
			$itemIds = $this->item_id;
		} else {
			//$user_id = Yii::$app->user->identity->admin ? null : Yii::$app->user->id;		
			$user_id = Yii::$app->user->id;		
			$itemIds = Yii\helpers\ArrayHelper::getColumn(Items::find()->select(['id'])->where(['del' => '0'])->andFilterWhere(['user_id' => $user_id])->all(), 'id');	
		}
		$query = self::find()->andFilterWhere([
					'id' => $this->id,
					'item_id' => $itemIds
				])
				->andFilterWhere(['between', 'dt', strtotime($this->dt_start), strtotime($this->dt_end . '+1 day') - 1])
				->orderBy('dt desc, id');
		return new \yii\data\ActiveDataProvider(['query' => $query, 'pagination' => false, 'sort' => false]);
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
	 * Create DataProvider for GridView.
	 * @return \yii\data\ActiveDataProvider
	 */
	public function calendar() {
		if ($this->item_id) {
			$itemIds = $this->item_id;
		} else {
			//$user_id = Yii::$app->user->identity->admin ? null : Yii::$app->user->id;		
			$user_id = Yii::$app->user->id;		
			$itemIds = Yii\helpers\ArrayHelper::getColumn(Items::find()->select(['id'])->where(['del' => '0'])->andFilterWhere(['user_id' => $user_id])->all(), 'id');	
		}
		$query = self::find()->andFilterWhere([
					'id' => $this->id,
					'item_id' => $itemIds
				])
				->andFilterWhere(['between', 'dt', strtotime($this->dt_start), strtotime($this->dt_end . '+1 day') - 1])
				->orderBy('dt desc, id');
		return new \yii\data\ActiveDataProvider(['query' => $query]);
	}
}
