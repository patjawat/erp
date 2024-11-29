<?php

use yii\db\Migration;

/**
 * Class m241106_073101_add_branch_to_employee
 */
class m241106_073101_add_branch_to_employee extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%employees}}'; // ใส่ชื่อตารางที่ต้องการเช็ค

        // เช็คว่าฟิลด์ helpdesk_id มีอยู่หรือไม่
        if (!array_key_exists('branch', Yii::$app->db->schema->getTableSchema($table)->columns)) {
            $this->addColumn($table, 'branch',"ENUM('MAIN', 'BRANCH') NOT NULL COMMENT 'ประเภทการเคลื่อนไหว (MAIN = โรงพยาบาล, BRANCH = รพสต.)'"); // เพิ่มฟิลด์ helpdesk_id
        }
    }

    public function safeDown()
    {
        $table = '{{%stock_events}}';

        // ลบฟิลด์ helpdesk_id หากมีการ rollback migration
        // if (array_key_exists('branch', Yii::$app->db->schema->getTableSchema($table)->columns)) {
        //     $this->dropColumn($table, 'branch');
        // }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241106_073101_add_branch_to_employee cannot be reverted.\n";

        return false;
    }
    */
}
