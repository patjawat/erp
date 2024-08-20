<?php

use yii\db\Migration;

/**
 * Class m240215_041352_asset_detail_table
 */
class m240215_041352_asset_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%asset_detail}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'date' => $this->date(),
            'code' => $this->string(255),
            'name' => $this->string(255),
            'user_id' => $this->integer(),
            'emp_id' => $this->integer(),
            'date_start' => $this->date(),   
            'date_end' => $this->date(),   
            'data_json' => $this->json(),
            'ma_items' => $this->json()->comment('การบำรุงรักษา'),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'updated_at' => $this->dateTime(),
            'created_at' => $this->dateTime(),   
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // echo "m240215_041352_asset_detail_table cannot be reverted.\n";

        // return false;
        $this->dropTable('{{%asset_detail}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240215_041352_asset_detail_table cannot be reverted.\n";

        return false;
    }
    */
}
