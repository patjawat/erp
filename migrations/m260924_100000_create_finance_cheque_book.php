<?php

use yii\db\Migration;

/**
 * ทะเบียนเล่มเช็ค — คุมเล่มเช็คที่รับเข้าตามบัญชีจ่าย (ช่วงเลข/คงเหลือ/สถานะ)
 * ผูกกับ finance_cheque ผ่าน book_id เพื่อให้เลขเช็ครันในเล่ม + เตือนใกล้หมด
 */
class m260924_100000_create_finance_cheque_book extends Migration
{
    public function safeUp()
    {
        $opts = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%finance_cheque_book}}', [
            'id' => $this->primaryKey(),
            'cash_account_id' => $this->integer()->notNull()->comment('บัญชีจ่ายของเล่มนี้'),
            'book_no' => $this->string(50)->null()->comment('เลข/ชื่อเล่มเช็ค'),
            'prefix' => $this->string(20)->null()->comment('คำนำหน้าเลขเช็ค (ถ้ามี)'),
            'start_no' => $this->integer()->notNull()->comment('เลขเริ่ม (ส่วนตัวเลข)'),
            'end_no' => $this->integer()->notNull()->comment('เลขสุดท้าย (ส่วนตัวเลข)'),
            'number_width' => $this->integer()->notNull()->defaultValue(0)->comment('จำนวนหลัก (เติมศูนย์หน้า)'),
            'received_date' => $this->date()->null()->comment('วันที่รับเล่ม'),
            'status' => $this->string(20)->notNull()->defaultValue('active')->comment('active/used_up/cancelled'),
            'note' => $this->string(255)->null(),
            'created_at' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $opts);
        $this->createIndex('idx-fcb-account', '{{%finance_cheque_book}}', 'cash_account_id');
        $this->createIndex('idx-fcb-status', '{{%finance_cheque_book}}', 'status');

        // ผูกเช็คเข้ากับเล่ม
        if (!$this->columnExists('{{%finance_cheque}}', 'book_id')) {
            $this->addColumn('{{%finance_cheque}}', 'book_id', $this->integer()->null()->after('cash_account_id'));
            $this->createIndex('idx-fc-book', '{{%finance_cheque}}', 'book_id');
            $this->addForeignKey('fk-fc-book', '{{%finance_cheque}}', 'book_id', '{{%finance_cheque_book}}', 'id', 'SET NULL', 'CASCADE');
        }
    }

    public function safeDown()
    {
        if ($this->columnExists('{{%finance_cheque}}', 'book_id')) {
            $this->dropForeignKey('fk-fc-book', '{{%finance_cheque}}');
            $this->dropColumn('{{%finance_cheque}}', 'book_id');
        }
        $this->dropTable('{{%finance_cheque_book}}');
    }

    private function columnExists(string $table, string $column): bool
    {
        $raw = $this->db->schema->getRawTableName($table);
        return $this->db->getTableSchema($raw, true) !== null
            && in_array($column, $this->db->getTableSchema($raw)->columnNames, true);
    }
}
