<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ upload_id ให้ media ของขั้นตอนอ้างอิงไฟล์ในระบบ filemanager (ตาราง uploads)
 */
class m260722_100000_add_upload_id_to_medsop_step_media extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%medsop_document_step_media}}', 'upload_id', $this->integer()->null()->after('step_id'));
        $this->createIndex('ix_medsop_step_media_upload', '{{%medsop_document_step_media}}', 'upload_id');
    }

    public function safeDown()
    {
        $this->dropIndex('ix_medsop_step_media_upload', '{{%medsop_document_step_media}}');
        $this->dropColumn('{{%medsop_document_step_media}}', 'upload_id');
    }
}
