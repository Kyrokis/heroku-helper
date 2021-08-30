<?php

use yii\db\Migration;

/**
 * Class m201110_083849_create_table_items_history
 */
class m201110_083849_create_table_items_history extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('items_history', [
			'id' => $this->primaryKey(),
			'item_id' => $this->integer(10)->notNull(),
			'now' => 'varchar default null',
			'link' => $this->string()->null(),
			'dt' => $this->integer(10)->notNull(),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable('items_history');
	}
}
