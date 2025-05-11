<?

namespace app\modules\helper\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;
use app\modules\template\models\Template;

/**
 * This is the model class for table "items".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $link
 * @property string $link_img
 * @property string $link_new
 * @property string $now
 * @property string $new
 * @property integer $id_template
 * @property integer $offset
 * @property integer $dt_update
 * @property string $error
 * @property string $del
 */
class Items extends ActiveRecord {

	const SCENARIO_SEARCH = 'search';

	const SCENARIO_CREATE = 'create';

	const SCENARIO_MASSUPDATE = 'mass-update';

	/**
	 * @var string linkSearch
	 */
	public $linkSearch;

	/**
	 * @var string linkReplace
	 */
	public $linkReplace;

	/**
	 * @var string titleSearch
	 */
	public $titleSearch;

	/**
	 * @var string titleReplace
	 */
	public $titleReplace;

	/**
	 * @return string
	 */
	public static function tableName() {
		return 'items';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['id', 'user_id', 'id_template', 'offset', 'dt_update'], 'integer'],
			[['title', 'link', 'link_img', 'link_new', 'link_alter', 'now', 'new', 'error', 'del', 'linkSearch', 'linkReplace', 'titleSearch', 'titleReplace'], 'string'],
			[['title', 'link', 'id_template'], 'required', 'on' => self::SCENARIO_CREATE],
			[['title', 'link', 'include', 'exclude'], 'safe', 'on' => self::SCENARIO_SEARCH],
			[['id'], 'required', 'on' => self::SCENARIO_MASSUPDATE],
			[['include', 'exclude'], 'safe'],
		];
	}

	/**
	 * User
	 * @return \app\modules\user\models\User
	 */
	public function getUser() {
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	/**
	 * Template
	 * @return \app\modules\template\models\Template
	 */
	public function getTemplate() {
		return $this->hasOne(Template::className(), ['id' => 'id_template']);
	}

	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getHistory() {
		return $this->hasMany(ItemsHistory::className(), ['item_id' => 'id'])->orderBy('id desc');
	}

	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getPrevValue() {
		return $this->hasOne(ItemsHistory::className(), ['item_id' => 'id'])->offset(1)->orderBy('dt desc');
	}

	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getLastValue() {
		return $this->hasOne(ItemsHistory::className(), ['item_id' => 'id'])->orderBy('dt desc');
	}

	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getLastChecked() {
		return $this->hasOne(ItemsHistory::className(), ['item_id' => 'id'])->andFilterWhere(['checked' => '1'])->orderBy('dt desc');
	}

	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getFirstUnchecked() {
		return $this->hasOne(ItemsHistory::className(), ['item_id' => 'id'])->andFilterWhere(['checked' => '0'])->orderBy('dt asc');
	}
	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getLastUnchecked() {
		return $this->hasOne(ItemsHistory::className(), ['item_id' => 'id'])->andFilterWhere(['checked' => '0'])->orderBy('dt desc');
	}

	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getUncheckedCount() {
		return $this->hasMany(ItemsHistory::className(), ['item_id' => 'id'])->andFilterWhere(['checked' => '0'])->count();
	}

	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getPrevPrevValue() {
		return $this->hasOne(ItemsHistory::className(), ['item_id' => 'id'])->offset(2)->orderBy('dt desc');
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'user_id' => 'ID юзера',
			'title' => 'Название',
			'link' => 'Ссылка',
			'link_img' => 'Ссылка на постер (Не используется)',
			'link_new' => 'Ссылка на новинку',
			'link_alter' => 'Альтернативная ссылка',
			'now' => 'Сейчас',
			'new' => 'Новый',
			'id_template' => 'Шаблон',
			'offset' => 'Смещение',
			'dt_update' => 'Дата новинки',
			'error' => 'Ошибка',
			'del' => 'Удален',
			'include' => 'Это должно быть',
			'exclude' => 'Это не должно быть',
			'linkSearch' => 'Ссылка что',
			'linkReplace' => 'Ссылка на что',
			'titleSearch' => 'Название что',
			'titleReplace' => 'Название на что',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert) {
		$this->now = str_replace("\r\n", "\n", $this->now);
		if ($insert) {
			if ($this->new == '') {
				$this->new = $this->now;
				$this->dt_update = time();
			}
			$this->user_id = Yii::$app->user->id;
		}
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($insert, $changedAttributes) {
		if ($insert || isset($changedAttributes['new']) || isset($changedAttributes['link_new'])) {
			$fullLink = Template::getFullLink($this->link_new, $this->id_template);
			if (!ItemsHistory::find()->andFilterWhere(['now' => $this->new, 'link' => $fullLink])->one()) {
				ItemsHistory::add($this->id, $this->new, $fullLink);
			}
		}
		return parent::afterSave($insert, $changedAttributes);
	}

	/**
	 * @inheritdoc
	 */
	public function afterFind() {
		if ($this->link) {
			//$this->link = str_replace('nyaa.si', 'https://freeproxy.io/o.php?b=4&u=' . urlencode($this->link), $this->link);
		}

		parent::afterFind();
	}

	/**
	 * @inheritdoc
	 */
	public function delete() {
		if ($this->del == '1') {
			self::deleteAll(['id' => $this->id]);
		} else {
			$this->del = '1';
		}
		if ($this->save(FALSE, ['del'])) {
			ItemsHistory::deleteAll(['item_id' => $this->id]);
			return true;
		}
		return false;
	}

	/**
	 * Delete all "deleted" items
	 */
	public static function deleteAllDeleted() {
		return self::deleteAll(['del' => '1']);
	}

	/**
	 * Create DataProvider for GridView.
	 * @return \yii\data\ActiveDataProvider
	 */
	public function searchQuery() {
		//$user_id = Yii::$app->user->identity->admin ? $this->user_id : Yii::$app->user->id;
		$user_id = $this->user_id;
		$del = $this->del ? : '0';
		$query = self::find()
				->joinWith('history')
				->andFilterWhere([
					'id' => $this->id,
					'link' => $this->link,
					'user_id' => $user_id,
					'id_template' => $this->id_template,
					'error' => $this->error,
					'del' => $del,
				])
				->andFilterWhere(['ILIKE', 'title', $this->title])
				->groupBy('items.id')
				->orderBy("user_id, (dt_update is null), (COUNT(CASE WHEN items_history.checked = '0' THEN 1 END) > 0) desc, dt_update desc, id");

		return $query;
	}

	/**
	 * Create DataProvider for GridView.
	 * @return \yii\data\ActiveDataProvider
	 */
	public function search() {
		return new \yii\data\ActiveDataProvider(['query' => self::searchQuery(), 'pagination' => false, 'sort' => false]);
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
	 * List of id => title
	 * @return array
	 */
	public static function all($item_id = null) {
		//$user_id = Yii::$app->user->identity->admin ? null : Yii::$app->user->id;
		$user_id = Yii::$app->user->id;
		return ArrayHelper::map(self::find()->select(['id', 'title'])->where(['del' => '0'])->andFilterWhere(['OR', ['user_id' => $user_id], ['id' => $item_id]])->orderBy('id')->all(), 'id', 'title');
	}

	/**
	 * Get estimate time for update
	 * @return integer
	 */
	public function getEstimate() {
		$dates = ArrayHelper::getColumn(ItemsHistory::find()->select(['dt'])->where(['item_id' => $this->id])->orderBy('dt desc')->all(), 'dt');
		if (count($dates) > 1) {
			for ($i = 0; $i < count($dates) - 1; $i++) { 
				$ranges[] = $dates[$i] - $dates[$i + 1];
			}
			$count = count($ranges);
			if ($count > 0) {
				while (true) {
					$avg = round(array_sum($ranges) / $count);
					$variance = [];
					foreach ($ranges as $range) {
						$variance[] = pow($range - $avg, 2);
					}
					$deviation = round(sqrt(array_sum($variance) / $count));
					if ($deviation < ($avg * 0.25) || $count == 1) {
						return [$avg - $deviation, $avg + $deviation];
					}
					unset($ranges[array_search(max($variance), $variance)]);
					$ranges = array_values($ranges);
					$count--;
				}

			}
		}
		return 0;
	}


	/**
	 * Get estimate date for update
	 * @return integer
	 */
	public function getDt_estimated() {
		$dt = $this->lastValue->dt;
		$estimate = $this->estimate;
		if ($estimate) {
			$dt_estimated = [$dt + $estimate[0], $dt + $estimate[1]];
			return (time() > $dt_estimated[0] && time() < $dt_estimated[1]) ? time() : (time() < $dt_estimated[0] ? $dt_estimated[0] : $dt_estimated[1]);
		}
		return 0;
	}

	/**
	 * Create DataProvider for GridView.
	 * @return \yii\data\ActiveDataProvider
	 */
	public function calendar() {
		$user_id = $this->user_id ? : Yii::$app->user->id;
		$query = self::find()->andFilterWhere([
					'id' => $this->id,
					'user_id' => $user_id,
					'del' => '0'
				])
				->orderBy('(dt_update is null), dt_update, id');
		return new \yii\data\ActiveDataProvider(['query' => $query]);
	}
}