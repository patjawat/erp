<?php

use yii\db\Migration;

/**
 * Class m241102_101420_add_helpdesk_id
 */
class m241102_101420_add_helpdesk_id extends Migration
{
    public function safeUp()
    {
        $table = '{{%stock_events}}'; // ใส่ชื่อตารางที่ต้องการเช็ค

        // เช็คว่าฟิลด์ helpdesk_id มีอยู่หรือไม่
        if (!array_key_exists('helpdesk_id', Yii::$app->db->schema->getTableSchema($table)->columns)) {
            $this->addColumn($table, 'helpdesk_id', $this->integer()->comment('รหัสงาน')); // เพิ่มฟิลด์ helpdesk_id
        }
    }

    public function safeDown()
    {
        $table = '{{%stock_events}}';

        // ลบฟิลด์ helpdesk_id หากมีการ rollback migration
        if (array_key_exists('helpdesk_id', Yii::$app->db->schema->getTableSchema($table)->columns)) {
            $this->dropColumn($table, 'helpdesk_id');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241102_101420_add_helpdesk_id cannot be reverted.\n";

        return false;
    }
    */
}
