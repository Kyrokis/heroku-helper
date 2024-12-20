<?php

/**
 * @author Marek Petras <mark@markpetras.eu>
 * @link https://github.com/marekpetras/yii2-calendarview-widget
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version 1.0.1
 */

namespace app\components\calendarview;

use app\components\calendarview\CalendarViewDateTime;

use yii\helpers\Html;

use Yii;
use Closure;
use Exception;

class CalendarView extends \marekpetras\calendarview\CalendarView
{

	/**
	 * @var \yii\data\DataProviderInterface the data provider for the view. This property is required.
	 */
	public $dataProvider;

	/**
	 * @var str which field to take as a value for the date (to display in the calendar)
	 */
	public $valueField;

	/**
	 * @var str which field to take as a datefield
	 */
	public $dateField;

	/**
	 * @var int which day display as first in the calendar, should be date('w')
	 */
	public $weekStart = 1; // date('w')

	/**
	 * @var str calendar title displayed above the calendar
	 */
	public $title = 'Calendar';

	/**
	 * @var mixed link on each calendar entry
	 *
	 * bool false to not have a link
	 * mixed value to pass to \yii\web\Html::a( $model->{$valueField}, $link
	 * Closure to return value to pass to \yii\web\Html::a( $model->{$valueField}, $link )
	 * Closure signature is function ($model, CalendarView $widget)
	 */
	public $link = false;

	/**
	 * @var mixed day renderer
	 *
	 * bool false to render each item by default as <p><a href=$this->link>$model->valueField</a></p>
	 * Closure to return the markup to pass into the day render view
	 * Closure signature is function ($model, CalendarView $widget)
	 */
	public $dayRender = false;

	/**
	 * @var array predefined views, override for your own, make sure its readable by $this->view->render()
	 */
	public $views = [
		'calendar' => '@vendor/marekpetras/yii2-calendarview-widget/views/calendar',
		'month' => '@vendor/marekpetras/yii2-calendarview-widget/views/month',
		'day' => '@vendor/marekpetras/yii2-calendarview-widget/views/day',
	];

	/**
	 * @var int which year to render from
	 */
	public $startYear = null;

	/**
	 * @var int which year to render to
	 */
	public $endYear = null;

	/**
	 * @var bool if we use unix timestamp in $dateField in $dataProvider
	 */
	public $unixTimestamp = false;

	// local
	private $models = [];
	private $calendarFormat = 'Y-m-d';

   /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerAssets();

        // load all models
        $this->dataProvider->pagination->pageSize = 0;
        $this->dataProvider->setSort(['attributes'=>[$this->dateField]]);

        foreach ( $this->dataProvider->getModels() as $model ) {
            $time = $model->{$this->dateField};

            if ( !$this->unixTimestamp ) {
                $time = strtotime($time);
            }

            try {
                $this->models[date($this->calendarFormat,$time)][] = $model;
            }
            catch (Exception $e) {
                throw new InvalidConfigException('Invalid dateField/unixTimestamp combination.
                        If you are using Unix Timestamp in your dateField in your data provider,
                        set the $unixTimestamp to true in your widget configuration.');
            }
        }

        $html = $this->renderCalendar();

        return $html;
    }



    /**
     * Renders a calendar month with models from the data provider
     * @param integer $month month for which to be rendered
     * @param integer $year year for which to be rendered
     * @return string the rendering result
     */
    protected function renderMonth($month = null, $year = null)
    {
        // set current ones if none passed
        if ( !$month ) {
            $month = date('m');
        }

        if ( !$year ) {
            $year = date('Y');
        }

        // set range for this month
        $first = new CalendarViewDateTime($year.'-'.$month.'-1');
        $last = new CalendarViewDateTime($first->format('Y-m-t'));

        $start = true;

        // create a modelable instance
        $current = clone $first;

        // store html
        $html = '';
        $html .= Html::beginTag('tr');

        while ($current <= $last)
        {
            // render week column
            if ( $current->format('w') == 1 ) {
                $html .= Html::beginTag('tr');
                $html .= Html::tag('td',Html::tag('div',$current->format('W')),['class'=>'week-number']);
            }

            // render days
            if ( $start && $current->format('w') != 1 ) {
                $html .= Html::tag('td',Html::tag('div',$current->format('W')),['class'=>'week-number']);

                $days = ($current->format('w')-2);

                foreach (range(0,$days) as $day ) {
                    $html .= Html::tag('td');
                }
                $start = false;
            }
            else {
                $start = false;
            }

            // fill in model data
            $models = array_key_exists($current->date(), (array) $this->models) ? $this->models[$current->date()] : [];
            $dayRender = '';
            foreach ( $models as $model ) {

                if ($this->dayRender instanceof Closure) {
                    $dayRender .= call_user_func($this->dayRender, $model, $this);
                }
                else {
                    $text = $model->{$this->valueField};
                    $link = null;

                    if ( $this->link instanceof Closure ) {
                        $link = call_user_func($this->link, $model, $this);
                    }

                    $dayRender .= Html::tag('p',Html::a($text,$link),['class'=>''/*'btn btn-info'*/]);
                }

            }

            $html .= $this->view->render($this->views['day'],[
                'models' => $models,
                'valueField' => $this->valueField,
                'date' => $current,
                'link' => $this->link,
                'dayRender' => $dayRender,

            ]);

            // new row after each week
            if ( $current->format('w') == 7 ) {
                $html .= Html::endTag('tr');
            }

            // move to next day
            $current->next();
        }

        return $this->view->render($this->views['month'],[
            'title' => Yii::t('calendar',$first->format('F')).$first->format(' Y'),
            'content' => $html,
            'monthRendered' => $first->format('Y')*12+$first->format('m'),
        ]);
    }
}