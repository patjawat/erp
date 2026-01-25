<?php

use yii\db\Migration;

class m260125_150758_change_document_org_to_json_in_documents_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%documents}}', 'document_org', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%documents}}', 'document_org', $this->string());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260125_150758_change_document_org_to_json_in_documents_table cannot be reverted.\n";

        return false;
    }
    */
}
