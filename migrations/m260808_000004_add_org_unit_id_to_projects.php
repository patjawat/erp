<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\db\Query;

/**
 * ผูกโครงการกับทะเบียนหน่วยงานกลาง (org_unit) แทนการอ้างผังโครงสร้างอย่างเดียว
 * เพราะโครงการมีของทีมประสานด้วย ซึ่งไม่มีอยู่ในผังบุคลากร (tree)
 *
 * ใช้แนวเดียวกับโมดูล plan: เก็บทั้ง org_unit_id และ department_id แบบ dual-write
 * เพื่อให้รายงาน/สิทธิ์เดิมที่อ้าง tree.id ยังทำงานต่อได้
 */
final class m260808_000004_add_org_unit_id_to_projects extends Migration
{
    public function safeUp(): void
    {
        // org_unit.id เป็น bigint — ต้องตรงชนิดกัน ไม่ผูก FK ตามแนวเดียวกับ plan_order.plan_unit_id
        // เพราะทะเบียนหน่วยงานแยกเวอร์ชันรายปี แถวของปีเก่าถูกจัดการแยกจากข้อมูลที่อ้างถึง
        $this->addColumn('{{%projects}}', 'org_unit_id', $this->bigInteger()->null()->after('department_id'));
        $this->createIndex('idx-projects-org_unit_id', '{{%projects}}', 'org_unit_id');

        // เติมย้อนหลังจากหน่วยงานในผังของปีเดียวกัน
        $rows = (new Query())->select(['id', 'thai_year', 'department_id'])
            ->from('{{%projects}}')
            ->where(['not', ['department_id' => null]])
            ->andWhere(['org_unit_id' => null])
            ->all($this->db);

        $filled = 0;
        foreach ($rows as $row) {
            $unitId = (new Query())->select('id')->from('org_unit')
                ->where(['thai_year' => (int) $row['thai_year'], 'source' => 'structure', 'ref_id' => (int) $row['department_id']])
                ->scalar($this->db);
            if ($unitId) {
                $this->update('{{%projects}}', ['org_unit_id' => $unitId], ['id' => $row['id']]);
                $filled++;
            }
        }
        echo "    > เติม org_unit_id ย้อนหลัง {$filled}/" . count($rows) . " รายการ\n";
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-projects-org_unit_id', '{{%projects}}');
        $this->dropColumn('{{%projects}}', 'org_unit_id');
    }
}
