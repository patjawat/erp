<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * HA12-PCT — เฟส 0 : โครงทะเบียนกิจกรรมและเกณฑ์
 *
 * ระบบทบทวน 12 กิจกรรม + สรุปประเมินโดย PCT (พอร์ตจาก Google Apps Script/Sheets รพ.แวงน้อย)
 * อยู่โซนเมนู "งานคุณภาพ" ต่อจาก "มาตรฐานโรงพยาบาล" (qms)
 *
 * เฟส 0 วางเฉพาะทะเบียนหลัก 2 ตาราง:
 *   - ha12_activity  : 12 กิจกรรม (seed) + ชนิดฟอร์ม (general/med/mrec/kpi)
 *   - ha12_criteria  : เกณฑ์ประเมิน 5 ระดับ ต่อกิจกรรม (มี version) — เนื้อหาแก้ได้ที่หน้าตั้งค่า
 *
 * คอนเวนชันเดียวกับ KM/QMS/Complaint:
 *   หน่วยงาน = tree.id (bigint), คน = employees.id (int, ผ่าน created_by/updated_by),
 *   fiscal_year = int (พ.ศ.), ทุกตารางมี ref (สุ่ม) + audit columns
 */
final class m260916_000001_create_ha12_core extends Migration
{
    /** ชนิดฟอร์มของแต่ละกิจกรรม */
    private const FORM_GENERAL = 'general'; // ตารางทบทวนทั่วไป
    private const FORM_MED = 'med';         // ความคลาดเคลื่อนทางยา (กิจกรรม 7)
    private const FORM_MREC = 'mrec';       // ความสมบูรณ์เวชระเบียน (กิจกรรม 9)
    private const FORM_KPI = 'kpi';         // ติดตามตัวชี้วัด (กิจกรรม 12)

    public function safeUp(): void
    {
        $audit = fn (): array => [
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];

        // 1) ทะเบียนกิจกรรม 12 กิจกรรม -----------------------------------------
        $this->createTable('{{%ha12_activity}}', array_merge([
            'id' => $this->primaryKey(),
            'no' => $this->smallInteger()->notNull()->comment('ลำดับกิจกรรม 1-12'),
            'name' => $this->string(255)->notNull()->comment('ชื่อกิจกรรม'),
            'form_type' => $this->string(16)->notNull()->defaultValue(self::FORM_GENERAL)
                ->comment('ชนิดฟอร์ม: general|med|mrec|kpi'),
            'data_hint' => $this->string(500)->null()->comment('ข้อมูลหลักที่ต้องบันทึก (คำอธิบายย่อ)'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-ha12_activity-ref', '{{%ha12_activity}}', 'ref', true);
        $this->createIndex('uq-ha12_activity-no', '{{%ha12_activity}}', 'no', true);
        $this->createIndex('idx-ha12_activity-active', '{{%ha12_activity}}', ['is_active', 'sort']);

        // 2) เกณฑ์ประเมิน 5 ระดับ ต่อกิจกรรม (มี version) ----------------------
        $this->createTable('{{%ha12_criteria}}', array_merge([
            'id' => $this->primaryKey(),
            'activity_id' => $this->integer()->notNull(),
            'level' => $this->tinyInteger(1)->notNull()->comment('ระดับการพัฒนา 1-5'),
            'title' => $this->string(255)->null()->comment('ชื่อระดับ'),
            'description' => $this->text()->null()->comment('นิยามเกณฑ์ของระดับ'),
            'version' => $this->integer()->notNull()->defaultValue(1)->comment('เวอร์ชันชุดเกณฑ์'),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
        ], $audit()));
        $this->createIndex('uq-ha12_criteria-ref', '{{%ha12_criteria}}', 'ref', true);
        $this->createIndex('uq-ha12_criteria-alv', '{{%ha12_criteria}}', ['activity_id', 'level', 'version'], true);
        $this->addForeignKey('fk-ha12_criteria-activity', '{{%ha12_criteria}}', 'activity_id', '{{%ha12_activity}}', 'id', 'CASCADE', 'CASCADE');

        $this->seedActivities();
        $this->seedCriteria();
    }

    /** 12 กิจกรรมตามคู่มือ HA12 (บท 6) */
    private function seedActivities(): void
    {
        $now = date('Y-m-d H:i:s');
        $g = self::FORM_GENERAL;
        // [no, name, form_type, data_hint]
        $items = [
            [1, 'ทบทวนขณะดูแลผู้ป่วย', $g, 'เหตุการณ์ วิธีทบทวน ประเด็น การแก้ไข ผู้ทบทวน'],
            [2, 'ความคิดเห็นและข้อร้องเรียน', $g, 'ประเภท ข้อร้องเรียน วิธีแก้ไข ผลและการป้องกันซ้ำ'],
            [3, 'การส่งต่อ ขอย้าย ปฏิเสธการรักษา', $g, 'เหตุผลหลักและย่อย ความพร้อม ผลลัพธ์ และการปรับปรุง'],
            [4, 'ทบทวนโดยผู้ชำนาญกว่า', $g, 'อุบัติการณ์ ประเด็น มาตรฐานที่ควรเป็น และผลลัพธ์'],
            [5, 'การค้นหาและป้องกันความเสี่ยง', $g, 'เรื่อง จำนวนครั้ง ประเภท แก้ไข ป้องกัน และผล'],
            [6, 'การป้องกันและเฝ้าระวังการติดเชื้อ', $g, 'อุบัติการณ์ ขั้นตอนหรือสาเหตุ และการปรับปรุง'],
            [7, 'ความคลาดเคลื่อนทางยา', self::FORM_MED, '5 หัวข้อหลัก ความเสี่ยงย่อย จำนวน ความรุนแรง และอัตรา'],
            [8, 'ทบทวนเหตุการณ์สำคัญ', $g, 'เหตุการณ์ สาเหตุ การป้องกันแก้ไข และผู้ร่วมทบทวน'],
            [9, 'ความสมบูรณ์ของเวชระเบียน', self::FORM_MREC, 'จำนวนตรวจ หัวข้อครบถ้วน 12 ข้อ ร้อยละ ปัญหา และผล'],
            [10, 'ทบทวนข้อมูลวิชาการ', $g, 'ข้อแนะนำ สภาพปัจจุบัน สิ่งที่ต้องการ และแผนดำเนินการ'],
            [11, 'ทบทวนการใช้ทรัพยากร', $g, 'เรื่อง ความสมเหตุสมผล การแก้ไข และผู้ทบทวน'],
            [12, 'การติดตามตัวชี้วัดสำคัญ', self::FORM_KPI, 'ตัวชี้วัด เป้าหมาย เดือน ความเสี่ยง ระดับ A ถึง I และการแก้ไข'],
        ];

        $rows = [];
        foreach ($items as $it) {
            [$no, $name, $type, $hint] = $it;
            $rows[] = [$no, $name, $type, $hint, $no, 1, bin2hex(random_bytes(16)), $now];
        }
        $this->batchInsert('{{%ha12_activity}}', ['no', 'name', 'form_type', 'data_hint', 'sort', 'is_active', 'ref', 'created_at'], $rows);
    }

    /**
     * เกณฑ์ 5 ระดับเริ่มต้น (ใช้ร่วมทุกกิจกรรม แก้ไขต่อรายกิจกรรมได้ที่หน้าตั้งค่า)
     * อิงมาตรฐานการให้คะแนน HA แบบ 1-5 (นิยามกลาง — เนื้อหาจริงรายกิจกรรมมาจากเอกสารต้นฉบับ)
     */
    private function seedCriteria(): void
    {
        $now = date('Y-m-d H:i:s');
        $levels = [
            [1, 'เริ่มต้น', 'มีการดำเนินการตามข้อกำหนดพื้นฐานบางส่วน ยังไม่เป็นระบบ'],
            [2, 'พัฒนาบางส่วน', 'มีการดำเนินการอย่างเป็นระบบในบางหน่วยงานหรือบางประเด็น'],
            [3, 'เป็นระบบทั่วองค์กร', 'มีการดำเนินการอย่างเป็นระบบครอบคลุมทั้งองค์กร'],
            [4, 'ประเมินและปรับปรุง', 'มีการติดตาม ประเมินผล และปรับปรุงอย่างต่อเนื่อง'],
            [5, 'ยั่งยืน/เป็นแบบอย่าง', 'มีผลลัพธ์ที่ดี ยั่งยืน และเป็นแบบอย่างให้หน่วยงานอื่นได้'],
        ];

        $activityIds = (new \yii\db\Query())
            ->select('id')
            ->from('{{%ha12_activity}}')
            ->column($this->db);

        $rows = [];
        foreach ($activityIds as $aid) {
            foreach ($levels as [$level, $title, $desc]) {
                $rows[] = [(int) $aid, $level, $title, $desc, 1, 1, bin2hex(random_bytes(16)), $now];
            }
        }
        $this->batchInsert('{{%ha12_criteria}}', ['activity_id', 'level', 'title', 'description', 'version', 'is_active', 'ref', 'created_at'], $rows);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%ha12_criteria}}');
        $this->dropTable('{{%ha12_activity}}');
    }
}
