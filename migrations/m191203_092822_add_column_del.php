<?php

use yii\db\Migration;

/**
 * Class m191203_092822_add_column_del
 */
class m191203_092822_add_column_del extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{   
		$this->addColumn('items', 'del', "bool_enum NOT NULL DEFAULT '0'");   
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('items', 'del');
	}
}
