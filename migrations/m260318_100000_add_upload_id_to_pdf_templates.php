<?php

use yii\db\Migration;

/**
 * เพิ่ม upload_id สำหรับเก็บไฟล์เทมเพลตผ่าน filemanager (uploads table).
 */
class m260318_100000_add_upload_id_to_pdf_templates extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%pdf_templates}}', 'upload_id', $this->integer()->null()->comment('FK to filemanager uploads.id'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%pdf_templates}}', 'upload_id');
    }
}
