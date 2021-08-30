<?php

use yii\db\Migration;

/**
 * Class m191115_195158_add_column_dt_update
 */
class m191115_195158_add_column_dt_update extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->addColumn('items', 'dt_update', $this->integer(10)->unsigned()->null()->comment('Дата обновления'));	
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('items', 'dt_update');
	}
}
