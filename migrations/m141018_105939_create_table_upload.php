<?php

use yii\db\Migration;

/**
 * Migration table upluad
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class m141018_105939_create_table_upload extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%uploads}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'ref' => $this->string(),
            'filename' => $this->string(),
            'file_name' => $this->string(),
            'real_filename' => $this->string(),
            'size' => $this->integer(),
            'type' => $this->string(64),
            ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%uploads}}');
    }
}