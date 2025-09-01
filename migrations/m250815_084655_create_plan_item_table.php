<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_item}}`.
 */
class m250815_084655_create_plan_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_item}}', [
            'id' => $this->primaryKey(),
            'plan_order_id' => $this->integer()->notNull()->comment('รหัสแผน'),
            'title' => $this->string(255)->comment('ชื่อวัสดุ/บุคลากร/ค่าใช้สอย'),
            'asset_code' => $this->string(255)->comment('รหัสครุภัณฑ์'),
            'item_id' => $this->string(255)->comment('รหัสที่ใช้เชื่อมกัน'),
            'item_name' => $this->string(255)->comment('ชื่อการเชื่อมต่อ'),
            'qty' => $this->integer()->defaultValue(1)->comment('จำนวน'),
            'unit_price' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคาต่อหน่วย'),
            'total_price' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคารวม'),
            'data_json' => $this->json()->comment('ยานพาหนะ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);
        //     $this->addForeignKey(
        //     'fk-plan_item-plan_id',
        //     '{{%plan_item}}',
        //     'plan_id',
        //     '{{%plan}}',
        //     'id',
        //     'CASCADE'
        // );

        $this->PlanType();
        $this->PlanCategory();
        $this->PlanItem();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // $this->dropForeignKey('fk-plan_item-plan_id', '{{%plan_item}}');
        $this->dropTable('{{%plan_item}}');
    }
    //ประเภทของแผน
    public function PlanType()
    {
        $name = 'new_plan_type';

        // ถ้ายังไม่มี name นี้เลย -> สร้างใหม่
        $count = (new \yii\db\Query())
            ->from('categorise')
            ->where(['name' => $name])
            ->count();

        if ($count == 0) {
            $items = [
                // ======================= รายจ่ายบุคลากร (HR) =======================
                ['sort' => 1, 'code' => 'PER', 'category_id' => null, 'title' => 'บุคลากร', 'name' => $name, 'data_json' => null],

                // ======================= รายจ่ายดำเนินงาน (OP) =======================
                ['sort' => 2, 'code' => 'OPS', 'category_id' => null, 'title' => 'รายจ่ายดำเนินงาน', 'name' => $name, 'data_json' => null],

                // ======================= รายจ่ายงบลงทุน (IN) =======================
                ['sort' => 3, 'code' => 'INV', 'category_id' => null, 'title' => 'รายจ่ายงบลงทุน', 'name' => $name, 'data_json' => null],

                // ======================= รายจ่ายอื่น (OT) =======================
                ['sort' => 4, 'code' => 'OTH', 'category_id' => null, 'title' => 'รายจ่ายอื่น', 'name' => $name, 'data_json' => null],
            ];

            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => $name, 'code' => $item['code']])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name'        => $item['name'],
                        'sort'        => $item['sort'],
                        'code'        => $item['code'],
                        'category_id' => $item['category_id'],
                        'title'       => $item['title'],
                        'data_json'   => $item['data_json'],
                    ])->execute();
                }
            }
        }
    }

    public function PlanCategory()
    {
        $name = 'new_plan_category';

        // ถ้ายังไม่มี name นี้เลย -> สร้างใหม่
        $count = (new \yii\db\Query())
            ->from('categorise')
            ->where(['name' => $name])
            ->count();

        if ($count == 0) {
            $items = [
                // บุคลากร
                ['code' => 'PER-SAL', 'category_id' => 'PER', 'title' => 'ค่าจ้าง'],
                ['code' => 'PER-OT', 'category_id' => 'PER', 'title' => 'ค่าตอบแทน/เงินเพิ่มพิเศษ (OT,ค่าเวร,ฉ.11 และอื่นๆ)'],
                ['code' => 'PER-EXP', 'category_id' => 'PER', 'title' => 'ค่าใช้จ่ายอื่น'],
                ['code' => 'PER-UTIL', 'category_id' => 'PER', 'title' => 'ค่าใช้สอย'],

                // รายจ่ายดำเนินงาน
                ['code' => 'OPS-MAT', 'category_id' => 'OPS', 'title' => 'ค่าวัสดุ (ค่ายา,เวชภัณฑ์,วัสดุทั่วไป)'],
                ['code' => 'OPS-UTL', 'category_id' => 'OPS', 'title' => 'ค่าสาธารณูปโภค (ค่าไฟฟ้า,ค่าประปา,ค่าโทรศัพท์,ค่าไปรษณีย์)'],

                // รายจ่ายงบลงทุน
                ['code' => 'INV-ASST', 'category_id' => 'INV', 'title' => 'ค่าครุภัณฑ์ ที่ดิน สิ่งปลูกสร้าง'],

                // รายจ่ายอื่น
                ['code' => 'OTH-SUP', 'category_id' => 'OTH', 'title' => 'เงินสนับสนุน รพ.สต.ในเครือข่าย'],
                ['code' => 'OTH-EXP', 'category_id' => 'OTH', 'title' => 'รายจ่ายอื่นๆ'],
            ];
            $sort = 1;
            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => $name, 'code' => $item['code']])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name'        => $name,
                        'sort'        => $sort++,
                        'code'        => $item['code'],
                        'category_id' => $item['category_id'],
                        'title'       => $item['title'],
                    ])->execute();
                }
            }
        }
    }

    public function PlanItem()
    {
        $name = 'new_plan_item';

        // ถ้ายังไม่มี name นี้เลย -> สร้างใหม่
        $count = (new \yii\db\Query())
            ->from('categorise')
            ->where(['name' => $name])
            ->count();

        if ($count == 0) {
            $items = [
                // ================= บุคลากร =================
                // ค่าจ้าง
                ['code' => 'PER-001-001', 'category_id' => 'PER-001', 'title' => 'พกส.'],
                ['code' => 'PER-001-002', 'category_id' => 'PER-001', 'title' => 'ลูกจ้างชั่วคราว'],
                ['code' => 'PER-001-003', 'category_id' => 'PER-001', 'title' => 'ลูกจ้างรายคาบ'],
                ['code' => 'PER-001-004', 'category_id' => 'PER-001', 'title' => 'เงินสมทบประกันสังคมส่วนของนายจ้าง'],
                ['code' => 'PER-001-005', 'category_id' => 'PER-001', 'title' => 'เงินสมทบกองทุนเลี้ยงชีพรายเดือน'],

                // ค่าตอบแทน/เงินเพิ่มพิเศษ
                ['code' => 'PER-002-001', 'category_id' => 'PER-002', 'title' => 'ค่าตอบแทนนอกเวลาราชการ'],
                ['code' => 'PER-002-002', 'category_id' => 'PER-002', 'title' => 'ค่าเวรบ่าย-ดึก (พยาบาล)'],
                ['code' => 'PER-002-003', 'category_id' => 'PER-002', 'title' => 'ค่าตอบแทนไม่ทำเวชปฏิบัติส่วนตัว'],
                ['code' => 'PER-002-004', 'category_id' => 'PER-002', 'title' => 'ค่าตอบแทนการปฏิบัติงาน(ฉบับ11)'],
                ['code' => 'PER-002-005', 'category_id' => 'PER-002', 'title' => 'ค่าตอบแทน พตส.(เงินนอกงบประมาณ)'],
                ['code' => 'PER-002-006', 'category_id' => 'PER-002', 'title' => 'ค่าตอบแทนอื่น(พ.สาขาส่งเสริมพิเศษ) ตกเบิกค่าเสี่ยงภัย ปี 2563'],

                // ค่าใช้จ่ายอื่น
                ['code' => 'PER-003-001', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายผลักส่งเป็นรายได้แผ่นดิน'],
                ['code' => 'PER-003-002', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายตามโครงการ (UC) (PP)'],
                ['code' => 'PER-003-003', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายตามโครงการ (เงินงบประมาณ)'],
                ['code' => 'PER-003-004', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายตามโครงการ (เงินนอกงบประมาณ)'],
                ['code' => 'PER-003-005', 'category_id' => 'PER-003', 'title' => 'ค่ารักษาตามจ่าย UC ในสังกัด สธ.'],
                ['code' => 'PER-003-006', 'category_id' => 'PER-003', 'title' => 'ค่ารักษาตามจ่าย UC นอกสังกัด สธ.'],
                ['code' => 'PER-003-007', 'category_id' => 'PER-003', 'title' => 'ค่ารักษาตามจ่ายคนต่างด้าวและแรงงานต่างด้าว'],
                ['code' => 'PER-003-008', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายตามโครง การ (P&P) แรงงานต่างด้าว'],
                ['code' => 'PER-003-009', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายตามโครง การ (P&P) บุคคลที่มีปัญหาสถานะและสิทธิ'],
                ['code' => 'PER-003-010', 'category_id' => 'PER-003', 'title' => 'ค่ารักษาตามจ่ายบุคคลที่มีปัญหาสถานะและสิทธิ'],
                ['code' => 'PER-003-011', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอุดหนุนเพื่อการดำเนินงานอื่น'],
                ['code' => 'PER-003-012', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายเงินอุดหนุนเพื่อการลงทุนอื่น'],
                ['code' => 'PER-003-013', 'category_id' => 'PER-003', 'title' => 'ค่าสวัสดิการสังคมอื่น'],
                ['code' => 'PER-003-014', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-อาคารเพื่อการพักอาศัย'],
                ['code' => 'PER-003-015', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-อาคารสำนักงาน'],
                ['code' => 'PER-003-016', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-อาคารเพื่อประโยชน์อื่น'],
                ['code' => 'PER-003-017', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-สิ่งปลูกสร้าง'],
                ['code' => 'PER-003-018', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-อาคารและสิ่งปลูกสร้าง - Interface'],
                ['code' => 'PER-003-019', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์สำนักงาน'],
                ['code' => 'PER-003-020', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ยานพาหนะและอุปกรณ์การขนส่ง'],
                ['code' => 'PER-003-021', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์ไฟฟ้าและวิทยุ'],
                ['code' => 'PER-003-022', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์โฆษณาและเผยแพร่'],
                ['code' => 'PER-003-023', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์การเกษตร'],
                ['code' => 'PER-003-024', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์ก่อสร้าง'],
                ['code' => 'PER-003-025', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์วิทยาศาสตร์และการแพทย์'],
                ['code' => 'PER-003-026', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-อุปกรณ์คอมพิวเตอร์'],
                ['code' => 'PER-003-027', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์งานบ้านงานครัว'],
                ['code' => 'PER-003-028', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-อุปกรณ์อื่น ๆ'],
                ['code' => 'PER-003-029', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย - ครุภัณฑ์ Interface'],
                ['code' => 'PER-003-030', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย - สินทรัพย์ไม่มีตัวตน Interface'],
                ['code' => 'PER-003-031', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-อาคารและสิ่งปลูกสร้างไม่ระบุรายละเอียด'],
                ['code' => 'PER-003-032', 'category_id' => 'PER-003', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์ไม่ระบุรายละเอียด'],
                ['code' => 'PER-003-033', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายเงินช่วยเหลือผู้ประสบภัย'],
                ['code' => 'PER-003-034', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายระหว่างกัน-ภายในกรมเดียวกัน (Manual)'],
                ['code' => 'PER-003-035', 'category_id' => 'PER-003', 'title' => 'โอนสินทรัพย์ให้หน่วยงานของรัฐ'],
                ['code' => 'PER-003-036', 'category_id' => 'PER-003', 'title' => 'โอนสินทรัพย์ให้หน่วยงานของรัฐบัญชีบริจาคสินทรัพย์ให้หน่วยงานภายนอก'],
                ['code' => 'PER-003-037', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายโครงการผลิตแพทย์'],
                ['code' => 'PER-003-038', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายโครงการผลิตบุคลากรทางการแพทย์'],
                ['code' => 'PER-003-039', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายที่ดิน'],
                ['code' => 'PER-003-040', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายลักษณะอื่น'],
                ['code' => 'PER-003-041', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-สินค้าโอนไป สสจ./รพศ./รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-042', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-วัสดุโอนไป สสจ./ รพศ./รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-043', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-ครุภัณฑ์ ที่ดิน และสิ่งก่อสร้าง โอนไป  สสจ./รพศ./รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-044', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบลงทุนโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-045', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบดำเนินงานโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-046', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบ อุดหนุนโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-047', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบรายจ่ายอื่นโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-048', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบกลางโอนไป สสจ./รพศ. /รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-049', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-เงินนอกงบประมาณโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.'],
                ['code' => 'PER-003-050', 'category_id' => 'PER-003', 'title' => 'ค่าใช้จ่ายอื่น-ด้านการฝึกอบรมในประเทศ(เงินนอกงปม.)'],

                // ค่าใช้สอย
                ['code' => 'PER-004-001', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมอาคารและสิ่งปลูกสร้าง'],
                ['code' => 'PER-004-002', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมครุภัณฑ์สำนักงาน'],
                ['code' => 'PER-004-003', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมครุภัณฑ์ยานพาหนะและขนส่ง'],
                ['code' => 'PER-004-004', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมครุภัณฑ์ไฟฟ้าและวิทยุ'],
                ['code' => 'PER-004-005', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมครุภัณฑ์โฆษณาและเผยแพร่'],
                ['code' => 'PER-004-006', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมครุภัณฑ์วิทยาศาสตร์และการแพทย์'],
                ['code' => 'PER-004-007', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมครุภัณฑ์คอมพิวเตอร์'],
                ['code' => 'PER-004-008', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมครุภัณฑ์อื่น'],
                ['code' => 'PER-004-009', 'category_id' => 'PER-004', 'title' => 'ค่าจ้างเหมาบำรุงรักษาดูแลลิฟท์'],
                ['code' => 'PER-004-010', 'category_id' => 'PER-004', 'title' => 'ค่าจ้างเหมาบำรุงรักษาสวนหย่อม'],
                ['code' => 'PER-004-011', 'category_id' => 'PER-004', 'title' => 'ค่าจ้างเหมาบำรุงรักษาครุภัณฑ์วิทยาศาสตร์และการแพทย์'],
                ['code' => 'PER-004-012', 'category_id' => 'PER-004', 'title' => 'ค่าจ้างเหมาบำรุงรักษาเครื่องปรับอากาศ'],
                ['code' => 'PER-004-013', 'category_id' => 'PER-004', 'title' => 'ค่าจ้างเหมาซ่อมแซมบ้านพัก'],
                ['code' => 'PER-004-014', 'category_id' => 'PER-004', 'title' => 'ค่าซ่อมแซมระบบโทรศัพท์'],
                ['code' => 'PER-004-015', 'category_id' => 'PER-004', 'title' => 'ค่าใช้จ่ายอื่น ๆ'],

                // ================= รายจ่ายดำเนินงาน =================
                ['code' => 'OPS-001-001', 'category_id' => 'OPS-001', 'title' => 'ค่ายา'],
                ['code' => 'OPS-001-002', 'category_id' => 'OPS-001', 'title' => 'เวชภัณฑ์ที่มิใช่ยา'],
                ['code' => 'OPS-001-003', 'category_id' => 'OPS-001', 'title' => 'วัสดุทั่วไป'],

                ['code' => 'OPS-002-001', 'category_id' => 'OPS-002', 'title' => 'ค่าไฟฟ้า'],
                ['code' => 'OPS-002-002', 'category_id' => 'OPS-002', 'title' => 'ค่าประปา'],
                ['code' => 'OPS-002-003', 'category_id' => 'OPS-002', 'title' => 'ค่าโทรศัพท์'],
                ['code' => 'OPS-002-004', 'category_id' => 'OPS-002', 'title' => 'ค่าไปรษณีย์'],

                // ================= รายจ่ายงบลงทุน =================
                ['code' => 'INV-001-001', 'category_id' => 'INV-001', 'title' => 'ครุภัณฑ์สำนักงาน'],
                ['code' => 'INV-001-002', 'category_id' => 'INV-001', 'title' => 'ครุภัณฑ์การศึกษา'],
                ['code' => 'INV-001-003', 'category_id' => 'INV-001', 'title' => 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์'],
                ['code' => 'INV-001-004', 'category_id' => 'INV-001', 'title' => 'ครุภัณฑ์คอมพิวเตอร์'],
                ['code' => 'INV-001-005', 'category_id' => 'INV-001', 'title' => 'ครุภัณฑ์ยานพาหนะและอุปกรณ์ขนส่ง'],
                ['code' => 'INV-001-006', 'category_id' => 'INV-001', 'title' => 'อาคารและสิ่งปลูกสร้าง'],
                ['code' => 'INV-001-007', 'category_id' => 'INV-001', 'title' => 'ที่ดิน'],

                // ================= รายจ่ายอื่น =================
                ['code' => 'OTH-001-001', 'category_id' => 'OTH-001', 'title' => 'เงินสนับสนุน รพ.สต.'],
                ['code' => 'OTH-002-001', 'category_id' => 'OTH-002', 'title' => 'รายจ่ายอื่นๆ'],
            ];
            $sort = 1;
            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => $name, 'code' => $item['code']])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name'        => $name,
                        'sort'        => $sort++,
                        'code'        => $item['code'],
                        'category_id' => $item['category_id'],
                        'title'       => $item['title'],
                    ])->execute();
                }
            }
        }
    }
}
