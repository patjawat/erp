<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * เอกสารฉบับร่างของเงินยืม — สร้างจากทะเบียน แก้บนจอ แล้วสั่งพิมพ์
 *
 * โครงเดียวกับ hr_development_document เพราะทั้งคู่สืบทอด purchase\models\Doc
 * ซึ่งเป็นตัวที่ DocRenderer รู้จัก ตารางจึงต้องมีคอลัมน์ครบตามที่ Doc คาดหวัง
 * ต่างกันแค่คีย์ต้นทาง — ที่นั่นชี้ทะเบียนการเดินทาง ที่นี่ชี้ใบยืมเงิน
 *
 * ดัชนี unique (loan_id, template_code) ทำให้ใบยืมหนึ่งใบมีเอกสารแต่ละชนิดได้ฉบับเดียว
 * เปิดซ้ำคือกลับมาแก้ฉบับเดิม ไม่ใช่สร้างใหม่ทับของที่แก้ไว้แล้ว
 */
final class m260903_060000_create_finance_loan_document extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%finance_loan_document}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'loan_id' => $this->integer()->notNull(),
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

        $this->createIndex('uq-finance_loan_document-source', '{{%finance_loan_document}}', ['loan_id', 'template_code'], true);
        $this->createIndex('idx-finance_loan_document-year', '{{%finance_loan_document}}', ['thai_year', 'status']);
        $this->createIndex('idx-finance_loan_document-deleted', '{{%finance_loan_document}}', 'deleted_at');

        $this->addForeignKey('fk-finance_loan_document-loan', '{{%finance_loan_document}}', 'loan_id', '{{%finance_loan}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-finance_loan_document-loan', '{{%finance_loan_document}}');
        $this->dropTable('{{%finance_loan_document}}');
    }
}
