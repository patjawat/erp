<?php

use yii\db\Migration;

/**
 * เพิ่มคอลัมน์ ref ให้เอกสาร (token ต่อ record) ตาม convention ระบบ filemanager
 * ไฟล์ทั้งหมดของเอกสารจะถูกเก็บใน fileupload/<ref>/ แยกชนิดด้วย name slot (cover, step_media)
 */
class m260722_099000_add_ref_to_medsop_document extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%medsop_document}}', 'ref', $this->string(50)->null()->after('id'));
        $this->createIndex('ix_medsop_document_ref', '{{%medsop_document}}', 'ref');

        // backfill ref ให้เอกสารเดิมที่ยังไม่มี
        $rows = (new \yii\db\Query())->select(['id'])->from('{{%medsop_document}}')
            ->where(['or', ['ref' => null], ['ref' => '']])->column($this->db);
        foreach ($rows as $id) {
            $this->update('{{%medsop_document}}', ['ref' => $this->generateRef()], ['id' => $id]);
        }
    }

    public function safeDown()
    {
        $this->dropIndex('ix_medsop_document_ref', '{{%medsop_document}}');
        $this->dropColumn('{{%medsop_document}}', 'ref');
    }

    private function generateRef(): string
    {
        return substr(Yii::$app->getSecurity()->generateRandomString(), 10);
    }
}
