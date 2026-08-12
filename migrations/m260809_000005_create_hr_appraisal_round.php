<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * รอบประเมินผลการปฏิบัติราชการ — 1 ปีงบประมาณมี 2 รอบ
 *   รอบที่ 1 : 1 ต.ค. – 31 มี.ค.
 *   รอบที่ 2 : 1 เม.ย. – 30 ก.ย.
 *
 * ตั้งชื่อกลางเป็น appraisal_round ไม่ใช่ competency_round เพราะรอบเดียวกันนี้
 * ใช้ร่วมกับผลสัมฤทธิ์ (KPI) และสมรรถนะตามสายงาน (Functional) ในขั้นถัดไปด้วย
 * น้ำหนักองค์ประกอบเก็บไว้ที่รอบ เพื่อให้แต่ละรอบ/แต่ละปีปรับได้โดยไม่กระทบรอบเก่า
 */
final class m260809_000005_create_hr_appraisal_round extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%hr_appraisal_round}}', [
            'id' => $this->primaryKey(),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณ พ.ศ.'),
            'round_no' => $this->tinyInteger()->notNull()->comment('1 = ต.ค.–มี.ค. / 2 = เม.ย.–ก.ย.'),
            'start_date' => $this->date()->null(),
            'end_date' => $this->date()->null(),
            'due_date' => $this->date()->null()->comment('กำหนดส่งผลประเมิน'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft = เตรียมการ / open = เปิดให้ประเมิน / closed = ปิดรอบแล้ว'),
            'weight_kpi' => $this->decimal(5, 2)->notNull()->defaultValue(50)->comment('น้ำหนักผลสัมฤทธิ์ของงาน (%)'),
            'weight_core' => $this->decimal(5, 2)->notNull()->defaultValue(30)->comment('น้ำหนัก Core competency (%)'),
            'weight_functional' => $this->decimal(5, 2)->notNull()->defaultValue(20)->comment('น้ำหนัก Functional competency (%)'),
            'note' => $this->text()->null(),
            'opened_at' => $this->dateTime()->null(),
            'closed_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('uq-hr_appraisal_round-year_no', '{{%hr_appraisal_round}}', ['fiscal_year', 'round_no'], true);
        $this->createIndex('idx-hr_appraisal_round-status', '{{%hr_appraisal_round}}', ['status', 'fiscal_year']);

        // สร้างรอบของปีที่มีชุดสมรรถนะประกาศใช้อยู่แล้ว เพื่อให้ข้อมูลเดิมมีรอบให้ผูก
        $years = $this->db->createCommand(
            'SELECT DISTINCT fiscal_year FROM {{%hr_competency_year}} ORDER BY fiscal_year'
        )->queryColumn();

        $now = date('Y-m-d H:i:s');
        foreach ($years as $fiscalYear) {
            $fiscalYear = (int) $fiscalYear;
            $ce = $fiscalYear - 543;           // ปีงบ 2569 = ค.ศ. 2026
            foreach ($this->roundDates($ce) as $roundNo => [$start, $end, $due]) {
                $this->insert('{{%hr_appraisal_round}}', [
                    'fiscal_year' => $fiscalYear,
                    'round_no' => $roundNo,
                    'start_date' => $start,
                    'end_date' => $end,
                    'due_date' => $due,
                    'status' => 'draft',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /** @return array<int, array{0:string,1:string,2:string}> round_no => [เริ่ม, สิ้นสุด, กำหนดส่ง] */
    private function roundDates(int $ce): array
    {
        return [
            1 => [($ce - 1) . '-10-01', $ce . '-03-31', $ce . '-04-30'],
            2 => [$ce . '-04-01', $ce . '-09-30', $ce . '-10-31'],
        ];
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%hr_appraisal_round}}');
    }
}
