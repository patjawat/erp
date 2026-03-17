<?php

use yii\db\Migration;

/**
 * Add data_source_id to pdf_templates (e.g. 'hr.development', 'leave').
 * เก็บว่าเทมเพลตนี้เลือกแหล่งข้อมูลใด เพื่อโหลดฟิลด์และ dropdown กลับมาได้หลัง reload
 */
class m260316_100000_add_data_source_id_to_pdf_templates extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%pdf_templates}}', 'data_source_id', $this->string(64)->null()->comment('แหล่งข้อมูล: hr.development, leave'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%pdf_templates}}', 'data_source_id');
    }
}
