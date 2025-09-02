<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan_item}}`.
 */
class m250815_084655_create_plan_order_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_order_item}}', [
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
        $this->dropTable('{{%plan_order_item}}');
    }
    //ประเภทของแผน
    public function PlanType()
    {
        $name = 'plan_type';

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
    $name = 'plan_category';
    
    // ถ้ายังไม่มี name นี้เลย -> สร้างใหม่
    $count = (new \yii\db\Query())
        ->from('categorise')
        ->where(['name' => $name])
        ->count();
    
    if ($count == 0) {
        $items = [
            // ======================= บุคลากร (PER) =======================
            ['sort' => 1, 'code' => 'PER_01', 'category_id' => 'PER', 'title' => 'ค่าจ้าง (พกส.,ลูกจ้างชั่วคราว,รายคาบ)', 'name' => $name],
            ['sort' => 2, 'code' => 'PER_02', 'category_id' => 'PER', 'title' => 'ค่าตอบแทน/เงินเพิ่มพิเศษ (OT,ค่าเวร,ฉ.11 และอื่นๆ)', 'name' => $name],
            ['sort' => 3, 'code' => 'PER_03', 'category_id' => 'PER', 'title' => 'ค่าใช้จ่ายอื่น (ค่าใช้จ่ายไปราชการ,ค่าฝึกอบรม,ค่าใช้จ่ายโครงการ)', 'name' => $name],
            ['sort' => 4, 'code' => 'PER_04', 'category_id' => 'PER', 'title' => 'ค่าใช้สอย', 'name' => $name],
            
            // ======================= รายจ่ายดำเนินงาน (OPS) =======================
            ['sort' => 5, 'code' => 'OPS_01', 'category_id' => 'OPS', 'title' => 'ค่าวัสดุ (ค่ายา,เวชภัณฑ์ที่มิใช่ยา และวัสดุทั่วไป)', 'name' => $name],
            ['sort' => 6, 'code' => 'OPS_02', 'category_id' => 'OPS', 'title' => 'ค่าสาธารณูปโภค (ค่าไฟฟ้า,ค่าประปา,ค่าโทรศัพท์,ค่าไปรษณีย์)', 'name' => $name],
            
            // ======================= รายจ่ายงบลงทุน (INV) =======================
            ['sort' => 7, 'code' => 'INV_01', 'category_id' => 'INV', 'title' => 'ค่าครุภัณฑ์ ที่ดิน สิ่งปลูกสร้าง (เงินบำรุง /เงินค่าเสื่อม /เงินบริจาค)', 'name' => $name],
            
            // ======================= รายจ่ายอื่น (OTH) =======================
            ['sort' => 8, 'code' => 'OTH_01', 'category_id' => 'OTH', 'title' => 'เงินสนับสนุน รพ.สต.ในเครือข่าย', 'name' => $name],
            ['sort' => 9, 'code' => 'OTH_02', 'category_id' => 'OTH', 'title' => 'รายจ่ายอื่นๆ', 'name' => $name],
        ];
        
        foreach ($items as $item) {
            $exists = (new \yii\db\Query())
                ->from('categorise')
                ->where(['name' => $name, 'code' => $item['code']])
                ->exists();
            
            if (!$exists) {
                // หา category_id จาก parent_code
                $parent = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => 'plan_type', 'code' => $item['code']])
                    ->one();
                
                Yii::$app->db->createCommand()->insert('categorise', [
                    'name' => $item['name'],
                    'sort' => $item['sort'],
                    'code' => $item['code'],
                    'category_id' => $item['category_id'] ?  : null,
                    'title' => $item['title'],
                    'data_json' => null,
                ])->execute();
            }
        }
    }
}

public function PlanItem()
{
    $name = 'plan_item';
    
    // ถ้ายังไม่มี name นี้เลย -> สร้างใหม่
    $count = (new \yii\db\Query())
        ->from('categorise')
        ->where(['name' => $name])
        ->count();
    
    if ($count == 0) {
        $items = [
            // ======================= ค่าจ้าง (PER_01) =======================
            ['sort' => 1, 'code' => 'PER_01_01', 'category_id' => 'PER_01', 'title' => 'พกส.', 'name' => $name],
            ['sort' => 2, 'code' => 'PER_01_02', 'category_id' => 'PER_01', 'title' => 'ลูกจ้างชั่วคราว', 'name' => $name],
            ['sort' => 3, 'code' => 'PER_01_03', 'category_id' => 'PER_01', 'title' => 'ลูกจ้างรายคาบ', 'name' => $name],
            ['sort' => 4, 'code' => 'PER_01_04', 'category_id' => 'PER_01', 'title' => 'เงินสมทบประกันสังคมส่วนของนายจ้าง', 'name' => $name],
            ['sort' => 5, 'code' => 'PER_01_05', 'category_id' => 'PER_01', 'title' => 'เงินสมทบกองทุนเลี้ยงชีพรายเดือน', 'name' => $name],
            
            // ======================= ค่าตอบแทน/เงินเพิ่มพิเศษ (PER_02) =======================
            ['sort' => 6, 'code' => 'PER_02_01', 'category_id' => 'PER_02', 'title' => 'ค่าตอบแทนนอกเวลาราชการ', 'name' => $name],
            ['sort' => 7, 'code' => 'PER_02_02', 'category_id' => 'PER_02', 'title' => 'ค่าเวรบ่าย-ดึก (พยาบาล)', 'name' => $name],
            ['sort' => 8, 'code' => 'PER_02_03', 'category_id' => 'PER_02', 'title' => 'ค่าตอบแทนไม่ทำเวชปฏิบัติส่วนตัว', 'name' => $name],
            ['sort' => 9, 'code' => 'PER_02_04', 'category_id' => 'PER_02', 'title' => 'ค่าตอบแทนการปฏิบัติงาน(ฉบับ11)', 'name' => $name],
            ['sort' => 10, 'code' => 'PER_02_05', 'category_id' => 'PER_02', 'title' => 'ค่าตอบแทน พตส.(เงินนอกงบประมาณ)', 'name' => $name],
            ['sort' => 11, 'code' => 'PER_02_06', 'category_id' => 'PER_02', 'title' => 'ค่าตอบแทนอื่น(พ.สาขาส่งเสริมพิเศษ) ตกเบิกค่าเสี่ยงภัย ปี 2563', 'name' => $name],
            
            // ======================= ค่าใช้จ่ายอื่น (PER_03) =======================
            ['sort' => 12, 'code' => 'PER_03_01', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายผลักส่งเป็นรายได้แผ่นดิน', 'name' => $name],
            ['sort' => 13, 'code' => 'PER_03_02', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายตามโครงการ (UC) (PP)', 'name' => $name],
            ['sort' => 14, 'code' => 'PER_03_03', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายตามโครงการ (เงินงบประมาณ)', 'name' => $name],
            ['sort' => 15, 'code' => 'PER_03_04', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายตามโครงการ (เงินนอกงบประมาณ)', 'name' => $name],
            ['sort' => 16, 'code' => 'PER_03_05', 'category_id' => 'PER_03', 'title' => 'ค่ารักษาตามจ่าย UC ในสังกัด สธ.', 'name' => $name],
            ['sort' => 17, 'code' => 'PER_03_06', 'category_id' => 'PER_03', 'title' => 'ค่ารักษาตามจ่าย UC นอกสังกัด สธ.', 'name' => $name],
            ['sort' => 18, 'code' => 'PER_03_07', 'category_id' => 'PER_03', 'title' => 'ค่ารักษาตามจ่ายคนต่างด้าวและแรงงานต่างด้าว', 'name' => $name],
            ['sort' => 19, 'code' => 'PER_03_08', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายตามโครง การ (P&P) แรงงานต่างด้าว', 'name' => $name],
            ['sort' => 20, 'code' => 'PER_03_09', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายตามโครง การ (P&P) บุคคลที่มีปัญหาสถานะและสิทธิ', 'name' => $name],
            ['sort' => 21, 'code' => 'PER_03_10', 'category_id' => 'PER_03', 'title' => 'ค่ารักษาตามจ่ายบุคคลที่มีปัญหาสถานะและสิทธิ', 'name' => $name],
            ['sort' => 22, 'code' => 'PER_03_11', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอุดหนุนเพื่อการดำเนินงานอื่น', 'name' => $name],
            ['sort' => 23, 'code' => 'PER_03_12', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายเงินอุดหนุนเพื่อการลงทุนอื่น', 'name' => $name],
            ['sort' => 24, 'code' => 'PER_03_13', 'category_id' => 'PER_03', 'title' => 'ค่าสวัสดิการสังคมอื่น', 'name' => $name],
            ['sort' => 25, 'code' => 'PER_03_14', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-อาคารเพื่อการพักอาศัย', 'name' => $name],
            ['sort' => 26, 'code' => 'PER_03_15', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-อาคารสำนักงาน', 'name' => $name],
            ['sort' => 27, 'code' => 'PER_03_16', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-อาคารเพื่อประโยชน์อื่น', 'name' => $name],
            ['sort' => 28, 'code' => 'PER_03_17', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-สิ่งปลูกสร้าง', 'name' => $name],
            ['sort' => 29, 'code' => 'PER_03_18', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-อาคารและสิ่งปลูกสร้าง - Interface', 'name' => $name],
            ['sort' => 30, 'code' => 'PER_03_19', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์สำนักงาน', 'name' => $name],
            ['sort' => 31, 'code' => 'PER_03_20', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ยานพาหนะและอุปกรณ์การขนส่ง', 'name' => $name],
            ['sort' => 32, 'code' => 'PER_03_21', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์ไฟฟ้าและวิทยุ', 'name' => $name],
            ['sort' => 33, 'code' => 'PER_03_22', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์โฆษณาและเผยแพร่', 'name' => $name],
            ['sort' => 34, 'code' => 'PER_03_23', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์การเกษตร', 'name' => $name],
            ['sort' => 35, 'code' => 'PER_03_24', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์ก่อสร้าง', 'name' => $name],
            ['sort' => 36, 'code' => 'PER_03_25', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'name' => $name],
            ['sort' => 37, 'code' => 'PER_03_26', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-อุปกรณ์คอมพิวเตอร์', 'name' => $name],
            ['sort' => 38, 'code' => 'PER_03_27', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์งานบ้านงานครัว', 'name' => $name],
            ['sort' => 39, 'code' => 'PER_03_28', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-อุปกรณ์อื่น ๆ', 'name' => $name],
            ['sort' => 40, 'code' => 'PER_03_29', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย - ครุภัณฑ์ Interface', 'name' => $name],
            ['sort' => 41, 'code' => 'PER_03_30', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย - สินทรัพย์ไม่มีตัวตน Interface', 'name' => $name],
            ['sort' => 42, 'code' => 'PER_03_31', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-อาคารและสิ่งปลูกสร้างไม่ระบุรายละเอียด', 'name' => $name],
            ['sort' => 43, 'code' => 'PER_03_32', 'category_id' => 'PER_03', 'title' => 'ค่าจำหน่าย-ครุภัณฑ์ไม่ระบุรายละเอียด', 'name' => $name],
            ['sort' => 44, 'code' => 'PER_03_33', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายเงินช่วยเหลือผู้ประสบภัย', 'name' => $name],
            ['sort' => 45, 'code' => 'PER_03_34', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายระหว่างกัน-ภายในกรมเดียวกัน (Manual)', 'name' => $name],
            ['sort' => 46, 'code' => 'PER_03_35', 'category_id' => 'PER_03', 'title' => 'โอนสินทรัพย์ให้หน่วยงานของรัฐ', 'name' => $name],
            ['sort' => 47, 'code' => 'PER_03_36', 'category_id' => 'PER_03', 'title' => 'โอนสินทรัพย์ให้หน่วยงานของรัฐบัญชีบริจาคสินทรัพย์ให้หน่วยงานภายนอก', 'name' => $name],
            ['sort' => 48, 'code' => 'PER_03_37', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายโครงการผลิตแพทย์', 'name' => $name],
            ['sort' => 49, 'code' => 'PER_03_38', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายโครงการผลิตบุคลากรทางการแพทย์', 'name' => $name],
            ['sort' => 50, 'code' => 'PER_03_39', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายที่ดิน', 'name' => $name],
            ['sort' => 51, 'code' => 'PER_03_40', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายลักษณะอื่น', 'name' => $name],
            ['sort' => 52, 'code' => 'PER_03_41', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-สินค้าโอนไป สสจ./รพศ./รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 53, 'code' => 'PER_03_42', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-วัสดุโอนไป สสจ./ รพศ./รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 54, 'code' => 'PER_03_43', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-ครุภัณฑ์ ที่ดิน และสิ่งก่อสร้าง โอนไป  สสจ./รพศ./รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 55, 'code' => 'PER_03_44', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบลงทุนโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 56, 'code' => 'PER_03_45', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบดำเนินงานโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 57, 'code' => 'PER_03_46', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบ อุดหนุนโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 58, 'code' => 'PER_03_47', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบรายจ่ายอื่นโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 59, 'code' => 'PER_03_48', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-เงินงบประมาณงบกลางโอนไป สสจ./รพศ. /รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 60, 'code' => 'PER_03_49', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายอื่น-เงินนอกงบประมาณโอนไปสสจ./รพศ./รพท./รพช./รพ.สต.', 'name' => $name],
            ['sort' => 61, 'code' => 'PER_03_50', 'category_id' => 'PER_03', 'title' => 'ค่าใช้จ่ายด้านการฝึกอบรมในประเทศ(เงินนอกงปม.)', 'name' => $name],
            
            // ======================= ค่าใช้สอย (PER_04) =======================
            ['sort' => 62, 'code' => 'PER_04_01', 'category_id' => 'PER_04', 'title' => 'ค่าซ่อมแซมอาคารและสิ่งปลูกสร้าง', 'name' => $name],
            ['sort' => 63, 'code' => 'PER_04_02', 'category_id' => 'PER_04', 'title' => 'ค่าซ่อมแซมครุภัณฑ์สำนักงาน', 'name' => $name],
            ['sort' => 64, 'code' => 'PER_04_03', 'category_id' => 'PER_04', 'title' => 'ค่าซ่อมแซมครุภัณฑ์ยานพาหนะและขนส่ง', 'name' => $name],
            ['sort' => 65, 'code' => 'PER_04_04', 'category_id' => 'PER_04', 'title' => 'ค่าซ่อมแซมครุภัณฑ์ไฟฟ้าและวิทยุ', 'name' => $name],
            ['sort' => 66, 'code' => 'PER_04_05', 'category_id' => 'PER_04', 'title' => 'ค่าซ่อมแซมครุภัณฑ์โฆษณาและเผยแพร่', 'name' => $name],
            ['sort' => 67, 'code' => 'PER_04_06', 'category_id' => 'PER_04', 'title' => 'ค่าซ่อมแซมครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'name' => $name],
            ['sort' => 68, 'code' => 'PER_04_07', 'category_id' => 'PER_04', 'title' => 'ค่าซ่อมแซมครุภัณฑ์คอมพิวเตอร์', 'name' => $name],
            ['sort' => 69, 'code' => 'PER_04_08', 'category_id' => 'PER_04', 'title' => 'ค่าซ่อมแซมครุภัณฑ์อื่น', 'name' => $name],
            ['sort' => 70, 'code' => 'PER_04_09', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาบำรุงรักษาดูแลลิฟท์', 'name' => $name],
            ['sort' => 71, 'code' => 'PER_04_10', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาบำรุงรักษาสวนหย่อม', 'name' => $name],
            ['sort' => 72, 'code' => 'PER_04_11', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาบำรุงรักษาครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'name' => $name],
            ['sort' => 73, 'code' => 'PER_04_12', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาบำรุงรักษาเครื่องปรับอากาศ', 'name' => $name],
            ['sort' => 74, 'code' => 'PER_04_13', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาซ่อมแซมบ้านพัก', 'name' => $name],
            ['sort' => 75, 'code' => 'PER_04_14', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาทำความสะอาด', 'name' => $name],
            ['sort' => 76, 'code' => 'PER_04_15', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาประกอบอาหารผู้ป่วย', 'name' => $name],
            ['sort' => 77, 'code' => 'PER_04_16', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมารถ', 'name' => $name],
            ['sort' => 78, 'code' => 'PER_04_17', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาดูแลความปลอดภัย', 'name' => $name],
            ['sort' => 79, 'code' => 'PER_04_18', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาซักรีด', 'name' => $name],
            ['sort' => 80, 'code' => 'PER_04_19', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมากำจัดขยะติดเชื้อ', 'name' => $name],
            ['sort' => 81, 'code' => 'PER_04_20', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาบริการทางการแพทย์', 'name' => $name],
            ['sort' => 82, 'code' => 'PER_04_21', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างเหมาบริการอื่น(สนับสนุน)', 'name' => $name],
            ['sort' => 83, 'code' => 'PER_04_22', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างตรวจทางห้องปฏิบัติการ (Lab)', 'name' => $name],
            ['sort' => 84, 'code' => 'PER_04_23', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างตรวจเอ็กซเรย์ (X-Ray)', 'name' => $name],
            ['sort' => 85, 'code' => 'PER_04_24', 'category_id' => 'PER_04', 'title' => 'ค่าธรรมเนียมทางกฎหมาย', 'name' => $name],
            ['sort' => 86, 'code' => 'PER_04_25', 'category_id' => 'PER_04', 'title' => 'ค่าธรรมเนียมธนาคาร', 'name' => $name],
            ['sort' => 87, 'code' => 'PER_04_26', 'category_id' => 'PER_04', 'title' => 'ค่าจ้างที่ปรึกษา', 'name' => $name],
            ['sort' => 88, 'code' => 'PER_04_27', 'category_id' => 'PER_04', 'title' => 'ค่าเบี้ยประกันภัย', 'name' => $name],
            ['sort' => 89, 'code' => 'PER_04_28', 'category_id' => 'PER_04', 'title' => 'ค่าใช้จ่ายในการประชุม', 'name' => $name],
            ['sort' => 90, 'code' => 'PER_04_29', 'category_id' => 'PER_04', 'title' => 'ค่ารับรองและพิธีการ', 'name' => $name],
            ['sort' => 91, 'code' => 'PER_04_30', 'category_id' => 'PER_04', 'title' => 'ค่าเช่าอสังหาริมทรัพย์', 'name' => $name],
            ['sort' => 92, 'code' => 'PER_04_31', 'category_id' => 'PER_04', 'title' => 'ค่าเช่าเบ็ดเตล็ด', 'name' => $name],
            ['sort' => 93, 'code' => 'PER_04_32', 'category_id' => 'PER_04', 'title' => 'เงินชดเชยค่างานสิ่งก่อสร้าง', 'name' => $name],
            ['sort' => 94, 'code' => 'PER_04_33', 'category_id' => 'PER_04', 'title' => 'ค่าประชาสัมพันธ์', 'name' => $name],
            ['sort' => 95, 'code' => 'PER_04_34', 'category_id' => 'PER_04', 'title' => 'ค่าชดใช้ค่าเสียหาย', 'name' => $name],
            ['sort' => 96, 'code' => 'PER_04_35', 'category_id' => 'PER_04', 'title' => 'ค่าใช้สอยอื่นๆ', 'name' => $name],
            
            // ======================= ค่าวัสดุ (OPS_01) =======================
            ['sort' => 97, 'code' => 'OPS_01_01', 'category_id' => 'OPS_01', 'title' => 'วัสดุอื่นๆ', 'name' => $name],
            ['sort' => 98, 'code' => 'OPS_01_02', 'category_id' => 'OPS_01', 'title' => 'วัสดุวิทยาศาสตร์และการแพทย์', 'name' => $name],
            ['sort' => 99, 'code' => 'OPS_01_03', 'category_id' => 'OPS_01', 'title' => 'วัสดุก่อสร้างและประปา', 'name' => $name],
            ['sort' => 100, 'code' => 'OPS_01_04', 'category_id' => 'OPS_01', 'title' => 'วัสดุคอมพิวเตอร์', 'name' => $name],
            ['sort' => 101, 'code' => 'OPS_01_05', 'category_id' => 'OPS_01', 'title' => 'วัสดุงานบ้านงานครัว', 'name' => $name],
            ['sort' => 102, 'code' => 'OPS_01_06', 'category_id' => 'OPS_01', 'title' => 'วัสดุบริโภค', 'name' => $name],
            ['sort' => 103, 'code' => 'OPS_01_07', 'category_id' => 'OPS_01', 'title' => 'วัสดุยานพาหนะและขนส่ง', 'name' => $name],
            ['sort' => 104, 'code' => 'OPS_01_08', 'category_id' => 'OPS_01', 'title' => 'วัสดุสำนักงาน', 'name' => $name],
            ['sort' => 105, 'code' => 'OPS_01_09', 'category_id' => 'OPS_01', 'title' => 'วัสดุไฟฟ้าและวิทยุ', 'name' => $name],
            ['sort' => 106, 'code' => 'OPS_01_10', 'category_id' => 'OPS_01', 'title' => 'วัสดุน้ำมันเชื้อเพลิงและหล่อลื่น', 'name' => $name],
            ['sort' => 107, 'code' => 'OPS_01_11', 'category_id' => 'OPS_01', 'title' => 'วัสดุโฆษณาและเผยแพร่', 'name' => $name],
            ['sort' => 108, 'code' => 'OPS_01_12', 'category_id' => 'OPS_01', 'title' => 'วัสดุเครื่องแต่งกาย', 'name' => $name],
            ['sort' => 109, 'code' => 'OPS_01_13', 'category_id' => 'OPS_01', 'title' => 'วัสดุการแพทย์ทั่วไป', 'name' => $name],
            ['sort' => 110, 'code' => 'OPS_01_14', 'category_id' => 'OPS_01', 'title' => 'วัสดุทันตกรรม', 'name' => $name],
            ['sort' => 111, 'code' => 'OPS_01_15', 'category_id' => 'OPS_01', 'title' => 'ยา และเวชภัณฑ์ที่มิใช่ยา', 'name' => $name],
            ['sort' => 112, 'code' => 'OPS_01_16', 'category_id' => 'OPS_01', 'title' => 'วัสดุเภสัชกรรม', 'name' => $name],
            
            // ======================= ค่าสาธารณูปโภค (OPS_02) =======================
            ['sort' => 113, 'code' => 'OPS_02_01', 'category_id' => 'OPS_02', 'title' => 'ค่าไฟฟ้า', 'name' => $name],
            ['sort' => 114, 'code' => 'OPS_02_02', 'category_id' => 'OPS_02', 'title' => 'ค่าน้ำประปา', 'name' => $name],
            ['sort' => 115, 'code' => 'OPS_02_03', 'category_id' => 'OPS_02', 'title' => 'ค่าโทรศัพท์/อินเตอร์เนต/VDO Conference', 'name' => $name],
            ['sort' => 116, 'code' => 'OPS_02_04', 'category_id' => 'OPS_02', 'title' => 'ค่าบริการสื่อสารและโทรคมนาคม', 'name' => $name],
            ['sort' => 117, 'code' => 'OPS_02_05', 'category_id' => 'OPS_02', 'title' => 'ค่าไปรษณีย์', 'name' => $name],
            
            // ======================= ค่าครุภัณฑ์ ที่ดิน สิ่งปลูกสร้าง (INV_01) =======================
            ['sort' => 118, 'code' => 'INV_01_01', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์สำนักงาน', 'name' => $name],
            ['sort' => 119, 'code' => 'INV_01_02', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์การศึกษา', 'name' => $name],
            ['sort' => 120, 'code' => 'INV_01_03', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'name' => $name],
            ['sort' => 121, 'code' => 'INV_01_04', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์การเกษตร', 'name' => $name],
            ['sort' => 122, 'code' => 'INV_01_05', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์ก่อสร้าง', 'name' => $name],
            ['sort' => 123, 'code' => 'INV_01_06', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์ไฟฟ้าและวิทยุ', 'name' => $name],
            ['sort' => 124, 'code' => 'INV_01_07', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์โฆษณาและเผยแพร่', 'name' => $name],
            ['sort' => 125, 'code' => 'INV_01_08', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'name' => $name],
            ['sort' => 126, 'code' => 'INV_01_09', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์งานบ้านงานครัว', 'name' => $name],
            ['sort' => 127, 'code' => 'INV_01_10', 'category_id' => 'INV_01', 'title' => 'ครุภัณฑ์คอมพิวเตอร์', 'name' => $name],
            ['sort' => 128, 'code' => 'INV_01_11', 'category_id' => 'INV_01', 'title' => 'ที่ดิน', 'name' => $name],
            ['sort' => 129, 'code' => 'INV_01_12', 'category_id' => 'INV_01', 'title' => 'อาคาร สิ่งก่อสร้าง', 'name' => $name],
            ['sort' => 130, 'code' => 'INV_01_13', 'category_id' => 'INV_01', 'title' => 'อื่นๆ (ครุภัณฑ์ต่ำกว่าเกณฑ์)', 'name' => $name],
            
            // ======================= เงินสนับสนุน รพ.สต.ในเครือข่าย (OTH_01) =======================
            ['sort' => 131, 'code' => 'OTH_01_01', 'category_id' => 'OTH_01', 'title' => 'Fix Cost', 'name' => $name],
            ['sort' => 132, 'code' => 'OTH_01_02', 'category_id' => 'OTH_01', 'title' => 'เงินเดือนลูกจ้างวิชาชีพ', 'name' => $name],
            ['sort' => 133, 'code' => 'OTH_01_03', 'category_id' => 'OTH_01', 'title' => 'เงินค่าตอบแทนกำลังคน (ฉ.11)', 'name' => $name],
            ['sort' => 134, 'code' => 'OTH_01_04', 'category_id' => 'OTH_01', 'title' => 'ค่าบริการทางการแพทย์ที่เบิกจ่ายในลักษณะงบลงทุน', 'name' => $name],
            ['sort' => 135, 'code' => 'OTH_01_05', 'category_id' => 'OTH_01', 'title' => 'งบลงทุน(เงินบำรุง)', 'name' => $name],
            ['sort' => 136, 'code' => 'OTH_01_06', 'category_id' => 'OTH_01', 'title' => 'เงินแพทย์แผนไทย', 'name' => $name],
            ['sort' => 137, 'code' => 'OTH_01_07', 'category_id' => 'OTH_01', 'title' => 'เงิน QOF', 'name' => $name],
            ['sort' => 138, 'code' => 'OTH_01_08', 'category_id' => 'OTH_01', 'title' => 'ค่ายา/วัสดุสนับสนุน รพ.สต. (โควิด)/สนับสนุนอื่นๆ', 'name' => $name],
            
            // ======================= รายจ่ายอื่นๆ (OTH_02) =======================
            ['sort' => 139, 'code' => 'OTH_02_01', 'category_id' => 'OTH_02', 'title' => 'ค่าใช้จ่ายลักษณะอื่น', 'name' => $name],
            ['sort' => 140, 'code' => 'OTH_02_02', 'category_id' => 'OTH_02', 'title' => 'ค่ารักษาตามจ่ายในจังหวัด', 'name' => $name],
            ['sort' => 141, 'code' => 'OTH_02_03', 'category_id' => 'OTH_02', 'title' => 'ค่ารักษาตามจ่ายต่างจังหวัด', 'name' => $name],
        ];
        
        foreach ($items as $item) {
            $exists = (new \yii\db\Query())
                ->from('categorise')
                ->where(['name' => $name, 'code' => $item['code']])
                ->exists();
            
            if (!$exists) {
                // หา category_id จาก parent_code
                $parent = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => 'plan_category', 'code' => $item['category_id']])
                    ->one();
                
                Yii::$app->db->createCommand()->insert('categorise', [
                    'name' => $item['name'],
                    'sort' => $item['sort'],
                    'code' => $item['code'],
                    'category_id' => $item['category_id'] ?  : null,
                    'title' => $item['title'],
                    'data_json' => null,
                ])->execute();
            }
        }
    }
}
}
