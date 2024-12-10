<?php

use yii\db\Migration;

/**
 * Class m241210_092651_add_column_checked_in_items_history_table
 */
class m241210_092651_add_column_checked_in_items_history_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('items_history', 'checked', "bool_enum NOT NULL DEFAULT '0'");	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('items_history', 'checked');
	}
}
