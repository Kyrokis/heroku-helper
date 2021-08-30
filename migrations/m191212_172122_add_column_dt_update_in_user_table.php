<?php

use yii\db\Migration;

/**
 * Class m191212_172122_add_column_dt_update_in_user_table
 */
class m191212_172122_add_column_dt_update_in_user_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->addColumn('user', 'dt_helping', $this->integer(10)->null());	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('user', 'dt_update');
	}
}
