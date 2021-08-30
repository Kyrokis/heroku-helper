<?php

use yii\db\Migration;

/**
 * Class m200428_074151_add_column_error_in_items_table
 */
class m200428_074151_add_column_error_in_items_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->addColumn('items', 'error', "bool_enum NOT NULL DEFAULT '0'");	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('items', 'error');
	}
}
