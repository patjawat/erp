<?php

use yii\db\Migration;

class m260718_150000_add_related_links_to_document_step extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%medsop_document_step}}', 'related_links', $this->text()->null()->after('caution'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%medsop_document_step}}', 'related_links');
    }
}
