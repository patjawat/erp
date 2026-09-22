<?php

use yii\db\Migration;

/**
 * จุดส่งต่อ การเงิน → บัญชี (explicit handoff)
 * การเงินอนุมัติเข้าทะเบียนแล้ว กด "ส่งบัญชี" → บัญชีเห็นในคิว "รอลงบัญชี" เพื่อตรวจแล้วลง GL
 */
class m260922_130000_add_payable_sent_accounting extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%finance_payable}}', 'sent_accounting_at', $this->dateTime()->null()->comment('เวลาที่การเงินส่งให้บัญชี'));
        $this->addColumn('{{%finance_payable}}', 'sent_accounting_by', $this->integer()->null()->comment('ผู้ส่งบัญชี'));
        $this->createIndex('idx-finance_payable-sent', '{{%finance_payable}}', 'sent_accounting_at');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-finance_payable-sent', '{{%finance_payable}}');
        $this->dropColumn('{{%finance_payable}}', 'sent_accounting_at');
        $this->dropColumn('{{%finance_payable}}', 'sent_accounting_by');
    }
}
