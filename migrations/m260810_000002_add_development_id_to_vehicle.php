<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ผูกใบจองรถกลับไปยังใบขออนุญาตไปราชการที่เป็นต้นเรื่อง
 *
 * ใช้คอลัมน์จริงแทนการเก็บใน vehicle.data_json เพราะทะเบียนไปราชการต้อง
 * ถามว่า "ใบนี้จองรถแล้วหรือยัง" ต่อแถว — ค้นใน json บนตารางหลักหมื่นแถวช้าเกินไป
 */
final class m260810_000002_add_development_id_to_vehicle extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%vehicle}}', 'development_id', $this->integer()->null()->comment('ใบขออนุญาตไปราชการต้นเรื่อง (NULL = จองรถโดยตรง)'));
        $this->createIndex('idx-vehicle-development', '{{%vehicle}}', 'development_id');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-vehicle-development', '{{%vehicle}}');
        $this->dropColumn('{{%vehicle}}', 'development_id');
    }
}
