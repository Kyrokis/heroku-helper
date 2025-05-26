<?php

use yii\db\Migration;

/**
 * Class m250526_193613_add_column_link_additional_in_items_table
 */
class m250526_193613_add_column_link_additional_in_items_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('items', 'link_additional', 'varchar default null');	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('items', 'link_additional');
	}
}
