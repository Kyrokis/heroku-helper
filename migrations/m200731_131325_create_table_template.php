<?php

use yii\db\Migration;

/**
 * Class m200731_131325_create_table_template
 */
class m200731_131325_create_table_template extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->createTable('template', [
			'id' => $this->primaryKey(),
			'name' => $this->string()->notNull(),
			'title' => "varchar(255)[] DEFAULT NULL",
			'new' => "varchar(255)[] DEFAULT NULL",
			'link_new' => "varchar(255)[] DEFAULT NULL",
			'link_img' => "varchar(255)[] DEFAULT NULL",
			'full_link' => "varchar(255)[] DEFAULT NULL",
			'user_id' => $this->integer(10)->null(),
			'type' => $this->string()->notNull(),
			'del' => "bool_enum NOT NULL DEFAULT '0'",
		]);
		$this->addForeignKey('template_user_id', 'template', 'user_id', 'user', 'id');
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropForeignKey('template_user_id', 'template');
		$this->dropTable('template');
	}
}
