<?php

use yii\db\Migration;

/**
 * ปรับให้ตรงระบบแผนเงินบำรุงของเขต (cfomoph.com/monthlycash) — เมนู 1.3 / 1.4 / 1.5
 *  - plan_investment : แผนการลงทุนด้วยเงินบำรุง (1 ปี = จำนวนปีแรก, 3 ปี = จำนวน 3 ปี) ต่อชุดแผนปีเริ่ม plan_year
 *  - plan_annual_attachment.line_code : ผูกแนบ 1/แนบ 2 เข้ากับบรรทัดตายตัวของแบบฟอร์มเขต (r1-r3 / c1-c19)
 *    แถวเดิมที่ชื่อตรงบรรทัดเขตจะถูกใส่รหัสให้; ที่ไม่ตรงคงไว้ (line_code = null) ให้ผู้ใช้ย้ายเองในหน้าจอ
 */
class m260925_100000_plan_investment_and_attachment_line extends Migration
{
    /** ชื่อบรรทัดของเขต (สำเนาไว้ใน migration เพื่อไม่ผูกกับโค้ด model ที่อาจเปลี่ยนภายหลัง) */
    private const LINES = [
        'reserve' => [
            'r1' => 'เงินกองทุนหลักประกันสุขภาพถ้วนหน้ารอการจัดสรร',
            'r2' => 'เงินกองทุนประกันสังคมรอการจัดสรร',
            'r3' => 'เงินกองทุนแรงงานต่างด้าวรอการจัดสรร',
        ],
        'commitment' => [
            'c1' => 'ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง',
            'c2' => 'ค่าล่วงเวลางานบริการ / งานสนับสนุน',
            'c3' => 'ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของพยาบาล',
            'c4' => 'ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน',
            'c5' => 'ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)',
            'c6' => 'ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)',
            'c7' => 'เงินเพิ่ม (พ.ต.ส)',
            'c8' => 'ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)',
            'c9' => 'ค่าตอบแทนอื่น',
            'c10' => 'เงินค่าใช้จ่ายบุคลากรอื่น',
            'c11' => 'ค่ายา',
            'c12' => 'ค่าวัสดุทางการแพทย์ / วัสดุวิทยาศาสตร์การแพทย์ / วัสดุทันตกรรม',
            'c13' => 'ค่าวัสดุอื่น',
            'c14' => 'ค่าสาธารณูปโภค',
            'c15' => 'ค่าใช้สอย',
            'c16' => 'ค่าครุภัณฑ์ค้างจ่าย',
            'c17' => 'ค่าที่ดินและสิ่งก่อสร้างค้างจ่าย',
            'c18' => 'รายจ่ายอื่นค้างจ่าย',
            'c19' => 'เงินประกัน เงินมัดจำ เงินรับฝากอื่น',
        ],
    ];

    public function safeUp()
    {
        $tbl = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable('{{%plan_investment}}', [
            'id' => $this->primaryKey(),
            'plan_year' => $this->integer()->notNull()->comment('ปีเริ่มของชุดแผน 3 ปี (พ.ศ.) เช่น 2570 = แผน 2570-2572'),
            'budget_type' => $this->string(20)->notNull()->comment('equipment=ครุภัณฑ์, construction=สิ่งก่อสร้าง'),
            'name' => $this->string(255)->notNull()->comment('รายการลงทุน'),
            'unit' => $this->string(50)->null()->comment('หน่วยนับ'),
            'unit_price' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ราคาต่อหน่วย'),
            'qty_y1' => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('จำนวนหน่วย ปีที่ 1 (= แผนลงทุน 1 ปี)'),
            'qty_y2' => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('จำนวนหน่วย ปีที่ 2'),
            'qty_y3' => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('จำนวนหน่วย ปีที่ 3'),
            'source' => $this->string(20)->notNull()->defaultValue('maintenance')->comment('maintenance=เงินบำรุง, depreciation=งบค่าเสื่อม, donation=งบบริจาค'),
            'policy' => $this->smallInteger()->notNull()->defaultValue(8)->comment('นโยบาย EMS 1-8'),
            'note' => $this->string(500)->null()->comment('เหตุผลความจำเป็น/หมายเหตุ'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ], $tbl);
        $this->createIndex('idx-plan_investment-year', '{{%plan_investment}}', ['plan_year', 'budget_type']);

        $this->addColumn('{{%plan_annual_attachment}}', 'line_code', $this->string(10)->null()->comment('รหัสบรรทัดแบบฟอร์มเขต r1-r3 / c1-c19; null = รายการเดิมนอกแบบฟอร์ม')->after('kind'));

        // ใส่รหัสให้แถวเดิมที่ชื่อตรงบรรทัดเขต (เทียบแบบตัดช่องว่าง)
        $norm = fn ($s) => preg_replace('/\s+/u', '', (string) $s);
        $rows = (new \yii\db\Query())->select(['id', 'kind', 'name'])->from('{{%plan_annual_attachment}}')->all($this->db);
        foreach ($rows as $r) {
            foreach (self::LINES[$r['kind']] ?? [] as $code => $title) {
                if ($norm($r['name']) === $norm($title)) {
                    $this->update('{{%plan_annual_attachment}}', ['line_code' => $code], ['id' => $r['id']]);
                    break;
                }
            }
        }
        $this->createIndex('idx-plan_annual_attachment-line', '{{%plan_annual_attachment}}', ['fiscal_year', 'kind', 'line_code']);
    }

    public function safeDown()
    {
        $this->dropIndex('idx-plan_annual_attachment-line', '{{%plan_annual_attachment}}');
        $this->dropColumn('{{%plan_annual_attachment}}', 'line_code');
        $this->dropTable('{{%plan_investment}}');
    }
}
