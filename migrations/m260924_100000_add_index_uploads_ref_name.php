<?php

use yii\db\Migration;

/**
 * ดัชนี uploads(ref, name)
 *
 * รูปโปรไฟล์ทุกจุดในระบบค้นด้วย Uploads::find()->where(['ref' => ..., 'name' => 'avatar'])
 * (Employees::ShowAvatar) แต่ตาราง uploads (~100k แถว) มีแค่ PRIMARY KEY จึง full scan
 * ครั้งละ ~45ms — หน้ารายการที่มี avatar 20-60 รูปช้าไปหลายวินาที เช่น /purchase/order
 */
class m260924_100000_add_index_uploads_ref_name extends Migration
{
    public function safeUp()
    {
        $this->createIndex('idx-uploads-ref-name', '{{%uploads}}', ['ref', 'name']);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-uploads-ref-name', '{{%uploads}}');
    }
}
