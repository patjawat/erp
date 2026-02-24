<?php

use yii\db\Migration;

/**
 * เพิ่ม index สำหรับ DMS dashboard ให้ query getCountsByGroup, getChartSummary เร็วขึ้น
 */
class m260224_160000_add_documents_dashboard_index extends Migration
{
    public function safeUp()
    {
        $this->createIndex(
            'idx-documents-thai_year_document_group',
            '{{%documents}}',
            ['thai_year', 'document_group']
        );
    }

    public function safeDown()
    {
        $this->dropIndex('idx-documents-thai_year_document_group', '{{%documents}}');
    }
}
