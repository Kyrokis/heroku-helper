<?php

use yii\db\Migration;

/**
 * Class m200702_133737_add_column_copying_in_user_table
 */
class m200702_133737_add_column_copying_in_user_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->addColumn('user', 'copying', "bool_enum NOT NULL DEFAULT '0'");	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('user', 'copying');
	}
}
