<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%leave_level}}`.
 */
class m240911_125838_create_leave_level_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%leave_level}}', [
            'id' => $this->primaryKey(),
            'leave_type_id' => $this->integer()->comment('ประเภทการลา'),
            'point' => $this->boolean()->comment('สะสมวันลา'),
            'point_days' => $this->integer()->comment('จำนวนวัน')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%leave_level}}');
    }
}
