<?php

use yii\db\Migration;

/**
 * Class m191119_132445_add_column_id_telegram
 */
class m191119_132445_add_column_id_telegram extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->addColumn('items', 'id_telegram', $this->integer(10)->null()->after('id'));	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('items', 'id_telegram');
	}
}
