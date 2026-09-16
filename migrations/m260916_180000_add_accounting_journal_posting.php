<?php

use yii\db\Migration;

/** Adds an auditable posting boundary between journal drafts and the general ledger. */
class m260916_180000_add_accounting_journal_posting extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%accounting_journal_draft}}', 'period_id', $this->integer()->null()->after('fiscal_year'));
        $this->addColumn('{{%accounting_journal_draft}}', 'posted_at', $this->dateTime()->null()->after('status'));
        $this->addColumn('{{%accounting_journal_draft}}', 'posted_by', $this->integer()->null()->after('posted_at'));
        $this->createIndex('idx-accounting_journal_draft-posted', '{{%accounting_journal_draft}}', ['status', 'document_date']);
        $this->addForeignKey('fk-accounting-journal-period', '{{%accounting_journal_draft}}', 'period_id', '{{%accounting_periods}}', 'id', 'RESTRICT', 'CASCADE');

        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission('accountingPost');
        if ($permission === null) {
            $permission = $auth->createPermission('accountingPost');
            $permission->description = 'ผ่านรายการบัญชีเข้าสู่บัญชีแยกประเภท';
            $auth->add($permission);
        }
        foreach (['accountingApprover'] as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role !== null && !$auth->hasChild($role, $permission)) $auth->addChild($role, $permission);
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($permission = $auth->getPermission('accountingPost')) $auth->remove($permission);
        $auth->invalidateCache();
        $this->dropForeignKey('fk-accounting-journal-period', '{{%accounting_journal_draft}}');
        $this->dropIndex('idx-accounting_journal_draft-posted', '{{%accounting_journal_draft}}');
        $this->dropColumn('{{%accounting_journal_draft}}', 'posted_by');
        $this->dropColumn('{{%accounting_journal_draft}}', 'posted_at');
        $this->dropColumn('{{%accounting_journal_draft}}', 'period_id');
    }
}
