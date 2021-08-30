<?php

use yii\db\Migration;

/**
 * Class m210523_104007_add_column_update_type_in_template_table
 */
class m210523_104007_add_column_update_type_in_template_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('template', 'update_type', "bool_enum NOT NULL DEFAULT '0'");	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('template', 'update_type');
	}
}
