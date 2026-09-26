<?php

use yii\db\Migration;

/**
 * เพิ่มประเภทค่าใช้จ่าย "ค่าใช้จ่ายอื่นๆ" ในใบยืมเงิน
 *
 * สำหรับเงินยืมที่ไม่เข้าหมวดไปราชการ ฝึกอบรม หรือโครงการ ใช้กติกาเดียวกับ
 * กลุ่มโครงการ: ส่งใช้ภายใน 30 วันหลังดำเนินการเสร็จ กรอกรายการเอง
 */
class m260926_140000_add_finance_loan_expense_type_other extends Migration
{
    public function safeUp()
    {
        $exists = (new \yii\db\Query())
            ->from('{{%finance_loan_expense_type}}')
            ->where(['code' => 'other'])
            ->exists($this->db);
        if ($exists) {
            echo "    > มีประเภท other อยู่แล้ว ข้าม\n";
            return;
        }
        $this->insert('{{%finance_loan_expense_type}}', [
            'code' => 'other',
            'name' => 'ค่าใช้จ่ายอื่นๆ',
            'due_days' => 30,
            'due_basis' => 'activity_end',
            'estimate_form' => 'general',
            'is_active' => 1,
            'sort_order' => 70,
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%finance_loan_expense_type}}', ['code' => 'other']);
    }
}
