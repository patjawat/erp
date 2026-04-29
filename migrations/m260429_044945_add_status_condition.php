<?php

use yii\db\Migration;

class m260429_044945_add_status_condition extends Migration
{
    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb3';

        // =====================================================
        // ลบ column life ถ้ามี
        // =====================================================
        $table = Yii::$app->db->schema->getTableSchema('asset');
        
        if (isset($table->columns['life'])) {
            $this->dropColumn('asset', 'life');
        }

        // =====================================================
        // 1. ตาราง asset_status
        // =====================================================
        $this->createTable('asset_status', [
            'id' => $this->string(20)->notNull(),
            'name' => $this->string(100)->notNull(),
            'color_css' => $this->string(50)->comment('ชื่อ CSS Class เช่น success, warning, danger'),
            'color_code' => $this->string(10)->comment('รหัสสี Hex Code เช่น #28a745'),
            'sort_order' => $this->tinyInteger()->unsigned()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $tableOptions);

        $this->addPrimaryKey('pk_asset_status','asset_status','id');

        $this->batchInsert('asset_status', 
            ['id', 'name', 'color_css', 'color_code', 'sort_order'], 
            [
                ['active',       'ใช้งาน',          'success',   '#28a745', 1],
                ['borrowed',     'ถูกยืม',         'info',      '#17a2b8', 2],
                ['repair',       'ส่งซ่อม',        'warning',   '#ffc107', 3],
                ['wait_dispose', 'รอจำหน่าย',     'secondary', '#6c757d', 4],
                ['disposed',     'จำหน่ายแล้ว',    'danger',    '#dc3545', 5],
            ]
        );

        // =====================================================
        // 2. ตาราง asset_condition
        // =====================================================
        $this->createTable('asset_condition', [
            'id' => $this->string(20)->notNull(),
            'name' => $this->string(50)->notNull(),
            'sort_order' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ], $tableOptions);

        $this->addPrimaryKey('pk_asset_condition','asset_condition','id');

        $this->batchInsert('asset_condition',['id','name','sort_order'],[
            ['good','ดี',1],
            ['fair','พอใช้',2],
            ['damaged','ชำรุด',3],
            ['worn','เสื่อมสภาพ',4],
        ]);

        // =====================================================
        // 3. ปรับ column asset_status ให้เป็น string ก่อนอัปเดตข้อมูล
        // =====================================================
        $this->alterColumn(
            'asset',
            'asset_status',
            $this->string(20)->defaultValue('active')
        );

        // -----------------------------------------------------
        // * อัปเดตข้อมูลสถานะเดิม ให้เป็นรหัสสถานะใหม่ *
        // -----------------------------------------------------
        $this->update('asset', ['asset_status' => 'active'],       ['asset_status' => '1']);
        $this->update('asset', ['asset_status' => 'disposed'],     ['asset_status' => '2']);
        $this->update('asset', ['asset_status' => 'wait_dispose'], ['asset_status' => '3']);
        $this->update('asset', ['asset_status' => 'borrowed'],     ['asset_status' => '4']);
        $this->update('asset', ['asset_status' => 'repair'],       ['asset_status' => '5']);
        $this->update('asset', ['asset_status' => 'active'],       ['asset_status' => '0']); 
        $this->update('asset', ['asset_status' => 'active'],       ['asset_status' => null]);
        $this->update('asset', ['asset_status' => 'active'],       ['asset_status' => '']); // ดักค่า String ว่าง

        // [เพิ่มใหม่] คลีนอัพข้อมูลขยะที่ไม่ได้อยู่ใน List ให้กลายเป็น active ทั้งหมด
        $validStatuses = ['active', 'borrowed', 'repair', 'wait_dispose', 'disposed'];
        $this->update('asset', ['asset_status' => 'active'], ['NOT IN', 'asset_status', $validStatuses]);

        // =====================================================
        // 4. เพิ่ม asset_condition
        // =====================================================
        $this->addColumn(
            'asset',
            'asset_condition',
            "VARCHAR(20) NOT NULL DEFAULT 'good' COMMENT 'FK → asset_condition.id' AFTER asset_status"
        );

        // =====================================================
        // 5. index
        // =====================================================
        $this->createIndex('idx_asset_status','asset','asset_status');
        $this->createIndex('idx_asset_condition','asset','asset_condition');

        // =====================================================
        // 6. FK 
        // =====================================================
        $this->addForeignKey(
            'fk_asset_status',
            'asset',
            'asset_status',
            'asset_status',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_asset_condition',
            'asset',
            'asset_condition',
            'asset_condition',
            'id',
            'RESTRICT',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_asset_condition','asset');
        $this->dropForeignKey('fk_asset_status','asset');

        $this->dropIndex('idx_asset_condition','asset');
        $this->dropIndex('idx_asset_status','asset');

        $this->dropColumn('asset','asset_condition');

        $this->update('asset', ['asset_status' => '1'], ['asset_status' => 'active']);
        $this->update('asset', ['asset_status' => '2'], ['asset_status' => 'disposed']);
        $this->update('asset', ['asset_status' => '3'], ['asset_status' => 'wait_dispose']);
        $this->update('asset', ['asset_status' => '4'], ['asset_status' => 'borrowed']);
        $this->update('asset', ['asset_status' => '5'], ['asset_status' => 'repair']);

        $this->dropTable('asset_condition');
        $this->dropTable('asset_status');
    }
}