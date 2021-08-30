<?php

use yii\db\Migration;

/**
 * Class m191210_123935_create_table_user
 */
class m191210_123935_create_table_user extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('user', [
			'id' => $this->primaryKey(),
			'login' => $this->string(50)->notNull(),
			'password' => $this->string()->notNull(),
			'id_telegram' => $this->integer()->null(),
			'dt_create' => $this->integer(10)->unsigned()->notNull(),
			'dt_update' => $this->integer(10)->unsigned()->notNull(),
			'auth_key' => $this->string(32)->notNull(),
			'admin' => "bool_enum NOT NULL DEFAULT '0'",
			'del' => "bool_enum NOT NULL DEFAULT '0'",
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropTable('user');
	}
}
