<?php

use yii\db\Migration;

/**
 * Class m250720_233938_add_column_dt_in_template_table
 */
class m250720_233938_add_column_dt_in_template_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('template', 'dt', "varchar(255)[] DEFAULT NULL");	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('template', 'dt');
	}
}
