<?php

use yii\db\Migration;

/**
 * Class m191111_092917_create_table_items
 */
class m191111_092917_create_table_items extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('items', [
			'id' => $this->primaryKey(),
			'title' => $this->string()->notNull(),
			'link' => $this->string()->notNull(),
			'link_img' => $this->string()->null(),
			'link_new' => $this->string()->null(),
			'now' => $this->string()->null(),
			'new' => $this->string()->null(),
			'id_template' => $this->integer()->notNull(),
			'offset' => $this->integer()->defaultValue(0),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable('items');
	}
}
