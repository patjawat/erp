<?php

declare(strict_types=1);

use yii\db\Migration;

/** เอกสารฉบับร่างสำหรับการเบิกค่าใช้จ่ายเดินทางไปราชการ */
final class m260814_000001_create_hr_development_document extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%hr_development_document}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'development_id' => $this->integer()->notNull(),
            'doc_no' => $this->string(50)->null(),
            'thai_year' => $this->integer()->notNull(),
            'doc_date' => $this->date()->null(),
            'template_id' => $this->integer()->null(),
            'template_code' => $this->string(50)->notNull(),
            'title' => $this->string(255)->notNull(),
            'ref_type' => $this->string(20)->notNull()->defaultValue('none'),
            'ref_id' => $this->integer()->null(),
            'body_html' => $this->getDb()->getSchema()->createColumnSchemaBuilder('mediumtext')->null(),
            'orientation' => $this->string(10)->notNull()->defaultValue('portrait'),
            'emblem' => $this->string(10)->notNull()->defaultValue('none'),
            'font_size' => $this->integer()->notNull()->defaultValue(14),
            'margin_json' => $this->json()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'printed_at' => $this->dateTime()->null(),
            'print_count' => $this->integer()->notNull()->defaultValue(0),
            'note' => $this->text()->null(),
            'department_id' => $this->integer()->null(),
            'emp_id' => $this->integer()->null(),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex(
            'uq-hr_development_document-source',
            '{{%hr_development_document}}',
            ['development_id', 'template_code'],
            true
        );
        $this->createIndex('idx-hr_development_document-year', '{{%hr_development_document}}', ['thai_year', 'status']);
        $this->createIndex('idx-hr_development_document-deleted', '{{%hr_development_document}}', 'deleted_at');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%hr_development_document}}');
    }
}
