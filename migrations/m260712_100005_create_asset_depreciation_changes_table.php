<?php

use yii\db\Migration;

/**
 * ประวัติการเปลี่ยนเกณฑ์ค่าเสื่อมของทรัพย์สินรายชิ้น
 *
 * หลักการ: เปลี่ยนเกณฑ์กลางปีต้องไม่กระทบงวดที่ปิดแล้ว — บันทึกการเปลี่ยนพร้อม scope
 *  - future_periods    : ใช้เกณฑ์ใหม่เฉพาะงวดในอนาคต
 *  - unposted_periods  : ใช้กับงวดที่ยังไม่ post
 *  - with_adjustment   : ปรับย้อนหลังด้วยรายการ adjustment
 */
class m260712_100005_create_asset_depreciation_changes_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%asset_depreciation_changes}}', [
            'id' => $this->primaryKey(),
            'asset_id' => $this->integer()->notNull()->comment('FK asset.id'),
            'old_depreciation_profile_id' => $this->integer()->null(),
            'new_depreciation_profile_id' => $this->integer()->null(),
            'old_useful_life_months' => $this->integer()->null(),
            'new_useful_life_months' => $this->integer()->null(),
            'old_rate' => $this->decimal(8, 4)->null(),
            'new_rate' => $this->decimal(8, 4)->null(),
            'effective_date' => $this->date()->notNull()->comment('วันที่มีผลของเกณฑ์ใหม่'),
            'change_scope' => $this->string(30)->notNull()->comment('future_periods|unposted_periods|with_adjustment'),
            'reason' => $this->string(500)->null(),
            'document_reference' => $this->string(255)->null(),
            'approved_by' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
        ], $tableOptions);

        $this->createIndex('idx_asset_dep_changes_asset', '{{%asset_depreciation_changes}}', 'asset_id');
        $this->createIndex('idx_asset_dep_changes_effective', '{{%asset_depreciation_changes}}', 'effective_date');

        $this->addForeignKey(
            'fk_asset_dep_changes_asset',
            '{{%asset_depreciation_changes}}',
            'asset_id',
            '{{%asset}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_asset_dep_changes_asset', '{{%asset_depreciation_changes}}');
        $this->dropTable('{{%asset_depreciation_changes}}');
    }
}
