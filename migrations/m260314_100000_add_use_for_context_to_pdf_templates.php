<?php

use yii\db\Migration;

/**
 * Add use_for_context to pdf_templates (e.g. 'development' = ใบขอไปราชการ).
 */
class m260314_100000_add_use_for_context_to_pdf_templates extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%pdf_templates}}', 'use_for_context', $this->string(64)->null()->comment('context e.g. development'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%pdf_templates}}', 'use_for_context');
    }
}
