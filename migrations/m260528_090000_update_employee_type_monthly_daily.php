<?php

use yii\db\Migration;

/**
 * ปรับปรุงประเภทพนักงานให้แยก ลูกจ้างชั่วคราวรายเดือน/รายวัน
 * - ปรับ employee_type id 4 เป็น ลูกจ้างชั่วคราวรายเดือน
 * - เพิ่ม employee_type id 5 เป็น ลูกจ้างชั่วคราวรายวัน
 * - อัปเดต employees ตาม position_type ที่เกี่ยวข้อง
 */
class m260528_090000_update_employee_type_monthly_daily extends Migration
{
    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%employee_type}}', true) === null) {
            return;
        }

        $this->seedEmployeeTypes();
        $this->backfillEmployeePositions();
        $this->backfillEmployees();
    }

    public function safeDown()
    {
        return false;
    }

    private function seedEmployeeTypes(): void
    {
        $rows = [
            [1, 'ข้าราชการ', 1, ['PT1']],
            [2, 'พนักงานราชการ', 2, ['PT2']],
            [3, 'พนักงานกระทรวง (พกส.)', 3, ['PT3']],
            [4, 'ลูกจ้างชั่วคราวรายเดือน', 4, ['PT4']],
            [5, 'ลูกจ้างชั่วคราวรายวัน', 5, ['PT5']],
            [6, 'ลูกจ้างประจำ', 6, ['PT6']],
        ];

        foreach ($rows as [$id, $title, $sort, $legacyCodes]) {
            $payload = [
                'id' => $id,
                'title' => $title,
                'sort' => $sort,
                'active' => 1,
                'data_json' => ['legacy_codes' => $legacyCodes],
            ];

            $this->upsert(
                '{{%employee_type}}',
                $payload,
                [
                    'title' => $title,
                    'sort' => $sort,
                    'active' => 1,
                    'data_json' => ['legacy_codes' => $legacyCodes],
                ]
            );
        }
    }

    private function backfillEmployees(): void
    {
        $schema = $this->db->getTableSchema('{{%employees}}', true);
        if ($schema === null) {
            return;
        }

        if (!isset($schema->columns['position_type'], $schema->columns['employee_type_id'])) {
            return;
        }

        $this->update('{{%employees}}', ['employee_type_id' => 4], ['position_type' => 'PT4']);
        $this->update('{{%employees}}', ['employee_type_id' => 5], ['position_type' => 'PT5']);
        $this->update('{{%employees}}', ['employee_type_id' => 6], ['position_type' => 'PT6']);
    }

    private function backfillEmployeePositions(): void
    {
        $schema = $this->db->getTableSchema('{{%employee_position}}', true);
        if ($schema === null) {
            return;
        }

        if (!isset($schema->columns['legacy_code'], $schema->columns['employee_type_id'])) {
            return;
        }

        $this->update('{{%employee_position}}', ['employee_type_id' => 4], ['legacy_code' => 'PT4']);
        $this->update('{{%employee_position}}', ['employee_type_id' => 5], ['legacy_code' => 'PT5']);
        $this->update('{{%employee_position}}', ['employee_type_id' => 6], ['legacy_code' => 'PT6']);
    }
}
