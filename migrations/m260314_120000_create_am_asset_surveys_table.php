<?php

use yii\db\Migration;

/**
 * Creates table `am_asset_surveys` (survey campaigns).
 */
class m260314_120000_create_am_asset_surveys_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%am_asset_surveys}}', [
            'id' => $this->primaryKey(),
            'survey_name' => $this->string(255)->notNull()->comment('ชื่อโครงการสำรวจ'),
            'survey_year' => $this->integer()->notNull()->comment('ปีสำรวจ พ.ศ.'),
            'department_id' => $this->integer()->null()->comment('หน่วยงานที่รับผิดชอบ (tree.id)'),
            'started_at' => $this->dateTime()->null()->comment('วันเริ่มสำรวจ'),
            'finished_at' => $this->dateTime()->null()->comment('วันสิ้นสุดสำรวจ'),
            'created_by' => $this->integer()->null()->comment('ผู้สร้าง'),
            'created_at' => $this->dateTime()->null()->comment('วันเวลาสร้าง'),
            'status' => $this->string(50)->notNull()->defaultValue('draft')->comment('draft|active|closed'),
        ]);

        $this->createIndex('idx_am_asset_surveys_year', '{{%am_asset_surveys}}', 'survey_year');
        $this->createIndex('idx_am_asset_surveys_status', '{{%am_asset_surveys}}', 'status');
        $this->createIndex('idx_am_asset_surveys_department', '{{%am_asset_surveys}}', 'department_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%am_asset_surveys}}');
    }
}
