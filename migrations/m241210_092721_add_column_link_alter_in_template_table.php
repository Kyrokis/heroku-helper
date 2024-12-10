<?php

use yii\db\Migration;

/**
 * Class m241210_092721_add_column_link_alter_in_template_table
 */
class m241210_092721_add_column_link_alter_in_template_table extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn('template', 'link_alter', 'varchar default null');	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('template', 'link_alter');
	}
}
