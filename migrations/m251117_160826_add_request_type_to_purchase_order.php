<?php

use yii\db\Migration;

class m251117_160826_add_request_type_to_purchase_order extends Migration
{
    /**
     * {@inheritdoc}
     */
     public function safeUp()
    {
        // เพิ่มคอลัมน์ request_type
        $this->addColumn('{{%orders}}', 'request_type', "ENUM('planned','unplanned') NOT NULL DEFAULT 'planned' COMMENT 'ประเภทจัดซื้อ'");
    }

    public function safeDown()
    {
        // ลบคอลัมน์ request_type
        $this->dropColumn('{{%orders}}', 'request_type');
    }
}
