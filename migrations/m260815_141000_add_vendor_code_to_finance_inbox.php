<?php

use yii\db\Migration;

/** Keep the ERP vendor code even when it is not numeric. */
class m260815_141000_add_vendor_code_to_finance_inbox extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%finance_inbox}}',
            'vendor_code_snapshot',
            $this->string(100)->null()->after('vendor_id')
        );
        $this->createIndex('idx-finance_inbox-vendor-code', '{{%finance_inbox}}', 'vendor_code_snapshot');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-finance_inbox-vendor-code', '{{%finance_inbox}}');
        $this->dropColumn('{{%finance_inbox}}', 'vendor_code_snapshot');
    }
}
