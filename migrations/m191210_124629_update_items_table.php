<?php

use yii\db\Migration;

/**
 * Class m191210_124629_update_items_table
 */
class m191210_124629_update_items_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->dropColumn('items', 'id_telegram');
		$this->addColumn('items', 'user_id', $this->integer(10)->null()->after('id'));	
		$this->addForeignKey('items_user_id', 'items', 'user_id', 'user', 'id');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('items_user_id', 'items');
		$this->dropColumn('items', 'user_id');
		$this->addColumn('items', 'id_telegram', $this->integer(10)->null()->after('id'));	
	}
}
