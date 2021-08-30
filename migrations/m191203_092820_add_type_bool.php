<?php

use yii\db\Migration;

/**
 * Class m191203_092820_add_type_bool
 */
class m191203_092820_add_type_bool extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{	
		$this->execute("CREATE TYPE bool_enum AS ENUM ('0','1')");
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->execute('DROP TYPE bool_enum');
	}
}
