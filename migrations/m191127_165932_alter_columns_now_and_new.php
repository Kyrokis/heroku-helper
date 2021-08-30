<?php

use yii\db\Migration;

/**
 * Class m191127_165932_alter_columns_now_and_new
 */
class m191127_165932_alter_columns_now_and_new extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->alterColumn('items', 'now', 'varchar default null');
		$this->alterColumn('items', 'new', 'varchar default null');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->alterColumn('items', 'now', $this->string()->null());
		$this->alterColumn('items', 'new', $this->string()->null());
	}
}
