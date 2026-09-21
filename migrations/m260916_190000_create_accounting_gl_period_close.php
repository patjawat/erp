<?php

use yii\db\Migration;

/** Independent GL close state; accounting_periods.status remains owned by shared period workflows. */
class m260916_190000_create_accounting_gl_period_close extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%accounting_gl_period_close}}', [
            'id' => $this->primaryKey(),
            'period_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->smallInteger()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('closed'),
            'journal_count' => $this->integer()->notNull(),
            'total_debit' => $this->decimal(15, 2)->notNull(),
            'total_credit' => $this->decimal(15, 2)->notNull(),
            'snapshot_hash' => $this->string(64)->notNull(),
            'closed_at' => $this->dateTime()->notNull(),
            'closed_by' => $this->integer()->null(),
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-accounting_gl_period_close-period', '{{%accounting_gl_period_close}}', 'period_id', true);
        $this->createIndex('uq-accounting_gl_period_close-ref', '{{%accounting_gl_period_close}}', 'ref', true);
        $this->createIndex('idx-accounting_gl_period_close-year-status', '{{%accounting_gl_period_close}}', ['fiscal_year', 'status']);
        $this->addForeignKey('fk-accounting-gl-close-period', '{{%accounting_gl_period_close}}', 'period_id', '{{%accounting_periods}}', 'id', 'RESTRICT', 'CASCADE');

        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission('accountingClosePeriod');
        if ($permission === null) {
            $permission = $auth->createPermission('accountingClosePeriod');
            $permission->description = 'ยืนยันปิดงวดบัญชีแยกประเภท';
            $auth->add($permission);
        }
        if (($role = $auth->getRole('accountingApprover')) !== null && !$auth->hasChild($role, $permission)) $auth->addChild($role, $permission);
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($permission = $auth->getPermission('accountingClosePeriod')) $auth->remove($permission);
        $auth->invalidateCache();
        $this->dropForeignKey('fk-accounting-gl-close-period', '{{%accounting_gl_period_close}}');
        $this->dropTable('{{%accounting_gl_period_close}}');
    }
}
