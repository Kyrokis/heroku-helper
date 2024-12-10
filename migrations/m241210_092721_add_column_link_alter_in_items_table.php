<?php

use yii\db\Migration;

/**
 * Class m241210_092721_add_column_link_alter_in_items_table
 */
class m241210_092721_add_column_link_alter_in_items_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('items', 'link_alter', 'varchar default null');	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('items', 'link_alter');
	}
}
