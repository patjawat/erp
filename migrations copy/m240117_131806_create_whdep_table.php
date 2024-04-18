<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%whdep}}`.
 */
class m240117_131806_create_whdep_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%whdep}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'depsup_code' => $this->string(255)->comment('รหัส'),
            'depsup_detail' => $this->string(255)->comment('รหัส'),
            'depsup_type' => $this->string(255)->comment('รหัส'),
            'depsup_store' => $this->string(255)->comment('รหัส'),
            'depsup_unit' => $this->string(20)->comment('รหัส'),
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime()->comment('วันเวลาแก้ไข'),
            'created_at' => $this->dateTime()->comment('วันเวลาสร้าง'), 
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%whdep}}');
    }
}
