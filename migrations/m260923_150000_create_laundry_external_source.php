<?php

use yii\db\Migration;

/**
 * รับผ้าจากหน่วยงานภายนอก (เช่น รพ.เลย ส่งผ้ากลับมาหลัง Refer) เข้าคลังหลัก ผ่านหน้า นับ–รีด–QC
 *  - laundry_external_source : ทะเบียนหน่วยงานภายนอก (เพิ่มจากฟอร์มได้ทันที / แก้ที่ตั้งค่า)
 *  - laundry_finish.source_type : DRY = นับหลังอบ (ภายใน) | EXTERNAL = รับจากหน่วยงานภายนอก
 */
class m260923_150000_create_laundry_external_source extends Migration
{
    public function safeUp()
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%laundry_external_source}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull()->unique(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ], $options);

        $this->addColumn('{{%laundry_finish}}', 'source_type', $this->string(20)->notNull()->defaultValue('DRY')->after('finish_no'));
        $this->addColumn('{{%laundry_finish}}', 'external_source_id', $this->integer()->null()->after('dry_batch_id'));
        $this->createIndex('idx_laundry_finish_ext', '{{%laundry_finish}}', ['external_source_id']);

        $this->insert('{{%laundry_external_source}}', [
            'name' => 'โรงพยาบาลเลย', 'sort_order' => 1, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_laundry_finish_ext', '{{%laundry_finish}}');
        $this->dropColumn('{{%laundry_finish}}', 'external_source_id');
        $this->dropColumn('{{%laundry_finish}}', 'source_type');
        $this->dropTable('{{%laundry_external_source}}');
    }
}
