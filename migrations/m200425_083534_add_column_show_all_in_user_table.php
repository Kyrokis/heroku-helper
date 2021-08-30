<?php

use yii\db\Migration;

/**
 * Class m200425_083534_add_column_show_all_in_user_table
 */
class m200425_083534_add_column_show_all_in_user_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->addColumn('user', 'show_all', "bool_enum NOT NULL DEFAULT '0'");	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('user', 'show_all');
	}
}
