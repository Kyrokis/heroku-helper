<?php

use yii\db\Migration;

/**
 * Class m210121_090943_add_columns_include_and_exclude_in_items_table
 */
class m210121_090943_add_columns_include_and_exclude_in_items_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('items', 'include', "varchar(255)[] DEFAULT '{}'");
		$this->addColumn('items', 'exclude', "varchar(255)[] DEFAULT '{}'");
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('items', 'include');
		$this->dropColumn('items', 'exclude');
	}
}
