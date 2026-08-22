<?php

use yii\db\Migration;

class m260822_090000_create_stock_repair_audit extends Migration
{
    private const PERMISSION = 'inventoryStockRepair';
    private const PERMISSION_MARKER = 'สร้างโดย migration m260822_090000_create_stock_repair_audit';

    public function safeUp()
    {
        $this->createTable('{{%stock_repair_audit}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->notNull(),
            'warehouse_id' => $this->integer()->notNull(),
            'item_code' => $this->string(50)->notNull(),
            'scope' => $this->string(10)->notNull(),
            'lot_number' => $this->string(100)->null(),
            'repair_mode' => $this->string(30)->notNull(),
            'reason' => $this->text()->notNull(),
            'before_json' => $this->json()->notNull(),
            'after_json' => $this->json()->notNull(),
            'fingerprint' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-stock-repair-audit-ref', '{{%stock_repair_audit}}', 'ref', true);
        $this->createIndex('idx-stock-repair-audit-scope', '{{%stock_repair_audit}}', ['warehouse_id', 'item_code', 'created_at']);
        $this->createIndex('ux-stock-repair-audit-fingerprint', '{{%stock_repair_audit}}', 'fingerprint', true);

        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(self::PERMISSION);
        if ($permission === null) {
            $permission = $auth->createPermission(self::PERMISSION);
            $permission->description = 'ซ่อมยอดคลังจากหน้าตรวจสุขภาพสต็อก (' . self::PERMISSION_MARKER . ')';
            $auth->add($permission);
        }
        $admin = $auth->getRole('admin');
        if ($admin !== null && !$auth->hasChild($admin, $permission)) $auth->addChild($admin, $permission);
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission(self::PERMISSION);
        // Do not remove a permission that existed before this migration.
        if ($permission !== null && strpos((string) $permission->description, self::PERMISSION_MARKER) !== false) {
            $auth->remove($permission);
        }
        $this->dropTable('{{%stock_repair_audit}}');
    }
}
