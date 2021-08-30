<?

use yii\helpers\Html;
use app\components\Str;
use yii\helpers\StringHelper;
use marekpetras\calendarview\CalendarView;

/* @var $this \yii\web\View */
/* @var $model \app\modules\helper\models\Items */

$this->params['breadcrumbs'][] = ['label' => $this->context->title, 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => 'History', 'url' => ['default/history']];
$this->params['breadcrumbs'][] =  $fields['title'];
echo CalendarView::widget([
	'dataProvider'  => $model->calendar(),
	'title' 		=> false,
	'dateField'     => $fields['dateField'],
	'valueField'    => $fields['valueField'],
	'unixTimestamp' => true,
	'dayRender'		=> function($data, $calendar) use($fields) {
		$out = '';
		$test = 'title';
		$title = $fields['type'] == 'history' ? $data->item->title : $data->title;
		$text = nl2br(StringHelper::truncate($title, 100, '...', null, true));
		$tooltip = Html::tag('span', $text, [
			'title' => $fields['type'] == 'history' ? $data->now : Str::dateEngToRu(date('d F H:i', $data->dt_estimated)),
			'data-toggle' => 'tooltip',
		]);
		if ($data->link) {
			$out = Html::a($tooltip, $data->link, ['target' => '_blank']);
		} else {
			$out = $tooltip;
		}
		return '<p style="border-bottom:solid;">' . $out . '</p>';
	},
]);