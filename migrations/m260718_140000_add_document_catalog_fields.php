<?php

use yii\db\Migration;

class m260718_140000_add_document_catalog_fields extends Migration
{
    public function safeUp()
    {
        $table = '{{%medsop_document}}';
        $this->addColumn($table, 'category', $this->string(100)->notNull()->defaultValue('')->after('organization_id'));
        $this->addColumn($table, 'keywords', $this->text()->null()->after('category'));
        $this->addColumn($table, 'review_date', $this->date()->null()->after('keywords'));
        $this->addColumn($table, 'announcement_status', $this->string(50)->notNull()->defaultValue('ACTIVE')->after('review_date'));
        $this->addColumn($table, 'cover_image', $this->string(500)->null()->after('announcement_status'));
        $this->addColumn($table, 'related_links', $this->text()->null()->after('cover_image'));
        $this->createIndex('ix_medsop_document_category', $table, 'category');
        $this->createIndex('ix_medsop_document_review_date', $table, 'review_date');
        $this->update($table, ['keywords' => ''], ['keywords' => null]);
        $this->alterColumn($table, 'keywords', $this->text()->notNull());
        $now = date('Y-m-d H:i:s');
        $this->batchInsert('{{%medsop_setting}}', ['setting_key', 'setting_value', 'updated_at'], [
            ['document_categories', json_encode(['SOP', 'WI', 'แนวทางปฏิบัติ', 'แบบฟอร์ม'], JSON_UNESCAPED_UNICODE), $now],
            ['announcement_statuses', json_encode(['ACTIVE' => 'ประกาศใช้', 'SUSPENDED' => 'ระงับใช้', 'CANCELLED' => 'ยกเลิกประกาศใช้'], JSON_UNESCAPED_UNICODE), $now],
        ]);
    }

    public function safeDown()
    {
        $table = '{{%medsop_document}}';
        $this->delete('{{%medsop_setting}}', ['setting_key' => ['document_categories', 'announcement_statuses']]);
        $this->dropIndex('ix_medsop_document_review_date', $table);
        $this->dropIndex('ix_medsop_document_category', $table);
        foreach (['related_links', 'cover_image', 'announcement_status', 'review_date', 'keywords', 'category'] as $column) {
            $this->dropColumn($table, $column);
        }
    }
}
