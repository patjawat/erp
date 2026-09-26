<?php

use yii\db\Migration;

/**
 * เพิ่ม form_type ให้ finance_cheque — รูปแบบการเขียนเช็คตามคู่มือ (1-6)
 * bearer=ผู้ถือ / named=ระบุชื่อ(ขีดฆ่าหรือผู้ถือ) / crossed=ขีดคร่อม /
 * ac_payee=A/C PAYEE ONLY / ac_payee_bank=คร่อมเฉพาะธนาคาร
 * คุมว่าจะขีดฆ่า "หรือผู้ถือ" + ขีดคร่อม + ข้อความอย่างไร
 */
class m260926_120000_add_form_type_to_finance_cheque extends Migration
{
    public function safeUp()
    {
        if (!$this->columnExists('{{%finance_cheque}}', 'form_type')) {
            $this->addColumn('{{%finance_cheque}}', 'form_type', $this->string(20)->notNull()->defaultValue('ac_payee')->after('is_ac_payee'));
            // backfill จากค่าเดิม: มี A/C PAYEE = ac_payee, ไม่มี = ระบุชื่อ (ขีดฆ่าหรือผู้ถือ)
            $this->update('{{%finance_cheque}}', ['form_type' => 'named'], ['is_ac_payee' => 0]);
            $this->update('{{%finance_cheque}}', ['form_type' => 'ac_payee'], ['is_ac_payee' => 1]);
        }
    }

    public function safeDown()
    {
        if ($this->columnExists('{{%finance_cheque}}', 'form_type')) {
            $this->dropColumn('{{%finance_cheque}}', 'form_type');
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        $raw = $this->db->schema->getRawTableName($table);
        return $this->db->getTableSchema($raw, true) !== null
            && in_array($column, $this->db->getTableSchema($raw)->columnNames, true);
    }
}
