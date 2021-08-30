<?php

use yii\db\Migration;

/**
 * Class m210830_160127_alter_columns_link_new_and_link
 */
class m210830_160127_alter_columns_link_new_and_link extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->alterColumn('items', 'link_new', 'varchar default null');
		$this->alterColumn('items_history', 'link', 'varchar default null');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->alterColumn('items', 'link_new', $this->string()->null());
		$this->alterColumn('items_history', 'link', $this->string()->null());
	}
}
