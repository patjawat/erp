<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%plan}}`.
 */
class m250815_084637_create_plan_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%plan_order}}', [
            'id' => $this->primaryKey(),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'department_id' => $this->integer(255)->comment('หน่วยงาน'),
            'plan_group_id' => $this->string(50)->notNull()->comment('ประเภทแผน: parcel, personnel, expenses'),
            'plan_category_id' => $this->string(50)->comment('หมวดหมู'),
            'plan_type_id' => $this->string(50)->comment('หมวดหมู'),
            'plan_budget_type_id' => $this->string(50)->comment('ประเภทงบ'),
            'plan_type_item_id' => $this->string(50)->comment('รายการแผน'),
            'wage_type_id' => $this->string(50)->comment('ประเภทค่าจ้าง'),
            'pay_type_id' => $this->string(50)->comment('แหล่งเงิน'),
            'asset_group_id' => $this->string(255)->comment('แยกประเภทพัสดุ/ครุภัณฑ์'),
            'asset_type_id' => $this->string(255)->comment('แยกประเภทพัสดุ/ครุภัณฑ์'),
            'asset_category_id' => $this->string(255)->comment('หมวดหมู่ของประเภททรัพย์สินย์'),
            'fiscal_year' => $this->boolean()->defaultValue(1)->comment('การดำเนินการ 1 = ภายในปีงบประมาณ, 0 = นอกปีงบประมาณ'),
            'price_ref' => $this->string(255)->comment('อ้างอิงตามราคา'),
            'title' => $this->string(255)->comment('ชื่อแผน'),
            'order_price' => $this->decimal(15, 2)->defaultValue(0)->comment('ราคารวม'),
            'description' => $this->text()->null()->comment('รายละเอียด'),
            'budget_total' => $this->decimal(15, 2)->defaultValue(0)->comment('งบประมาณรวม'),
            'budget_used' => $this->decimal(15, 2)->defaultValue(0)->comment('งบที่ใช้ไปแล้ว'),
            'status' => $this->string(20)->defaultValue('draft')->comment('สถานะ: draft, submitted, approved, completed'),
            'month_1' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน มกราคม'),
            'month_2' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน กุมภาพันธ์'),
            'month_3' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน มีนาคม'),
            'month_4' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน เมษายน'),
            'month_5' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน พฤษภาคม'),
            'month_6' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน มิถุนายน'),
            'month_7' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน กรกฎาคม'),
            'month_8' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน สิงหาคม'),
            'month_9' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน กันยายน'),
            'month_10' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน ตุลาคม'),
            'month_11' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน พฤศจิกายน'),
            'month_12' => $this->decimal(15, 2)->defaultValue(0)->comment('จำนวนเงินเดือน ธันวาคม'),
            'emp_id' => $this->string()->comment('ผู้ขอ'),
            'data_json' => $this->json()->comment('json'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);
        $this->PlanGroup();
        $this->PlanCategory();
        $this->PlanType();
        $this->PlanTypeItem();
        $this->PlanPriceRef();
        $this->PlanWageType();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%plan_order}}');
    }



    public function PlanPriceRef()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'price_ref'])->count();
        if ($count == 0) {
            $items = [
                ['STANDARD', 'ราคากลาง', 'Standard Price'],
                ['CEILING', 'เพดานราคา', 'Ceiling Price'],
                ['MARKET', 'ราคาท้องตลาด', 'Market Price'],
            ];
            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => 'price_ref', 'code' => $item[0]])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name' => 'price_ref',
                        'code' => $item[0],
                        'title' => $item[1],
                        'data_json' => null,
                    ])->execute();
                }
            }
        }
    }

    public function PlanWageType()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'plan_wage_type'])->count();
        if ($count == 0) {
            $items = [
                ['MONTHLY', 'ค่าจ้างลูกจ้างชั่วคราวรายเดือน'],
                ['DAILY', 'ค่าจ้างลูกจ้างชั่วคราวรายวัน'],
                ['SESSION', 'ค่าจ้างลูกจ้างชั่วคราวรายคาบ'],
            ];
            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => 'plan_wage_type', 'code' => $item[0]])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name' => 'plan_wage_type',
                        'code' => $item[0],
                        'title' => $item[1],
                        'data_json' => null,
                    ])->execute();
                }
            }
        }
    }

    public function PlanGroup()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'plan_group'])->count();
        if ($count == 0) {
            $items = [
                ['parcel', 'แผนคำขอพัสดุ'],
                ['personnel', 'แผนคำขอบุคลากร'],
                ['expenses', 'แผนคำขอค่าใช้สอย'],
            ];
            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => 'plan_group', 'code' => $item[0]])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name' => 'plan_group',
                        'title' => $item[1],
                        'code' => $item[0],
                        'data_json' => null,
                    ])->execute();
                }
            }
        }
    }

    //ประเภทงบ
    public function PlanCategory()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'plan_category'])->count();
        if ($count == 0) {
            $items = [
                ['code' => 'PE',   'category_id' => 'expenses', 'title' => 'รายจ่ายบุคลากร',     'title_en' => 'Personnel Expenditure'],
                ['code' => 'OE',   'category_id' => 'expenses', 'title' => 'รายจ่ายจากการดำเนินงาน', 'title_en' => 'Operating Expenditure'],
                ['code' => 'CE',   'category_id' => 'expenses', 'title' => 'รายจ่ายลงทุน',        'title_en' => 'Capital Expenditure'],
                ['code' => 'OE-OTH',  'category_id' => 'expenses', 'title' => 'รายจ่ายอื่น',         'title_en' => 'Other Expenditure'],
                ['code' => 'OR',   'category_id' => 'income',   'title' => 'รายรับจากการดำเนินงาน', 'title_en' => 'Operating Revenue'],
                ['code' => 'OR-OTH',  'category_id' => 'income',   'title' => 'รายรับอื่น',          'title_en' => 'Other Revenue'],
            ];

            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => 'plan_budget_type', 'code' => $item['code']])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name' => 'plan_budget_type',
                        'code' => $item['code'],
                        'category_id' => $item['category_id'],
                        'title' => $item['title'],
                        'data_json' => json_encode(['title_en' => $item['title_en']], JSON_UNESCAPED_UNICODE),
                    ])->execute();
                }
            }
        }
    }

    //ประเภทของแผน
    public function PlanType()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'plan_type'])->count();
        if ($count == 0) {
            $items = [
                // ======================= รายจ่ายบุคลากร (PE) =======================
                ['sort' => 1, 'code' => 'PE1',  'category_id' => 'PE',  'title' => 'ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 2, 'code' => 'PE2',  'category_id' => 'PE',  'title' => 'ค่าล่วงเวลางานบริการ / งานสนับสนุน', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 3, 'code' => 'PE3',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของเจ้าหน้าที่', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 4, 'code' => 'PE4',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 5, 'code' => 'PE5',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 6, 'code' => 'PE6',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 7, 'code' => 'PE7',  'category_id' => 'PE',  'title' => 'เงินเพิ่ม (พ.ต.ส)', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 8, 'code' => 'PE8',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานของเจ้าหน้าที่ (นอกเวลา) ฉ5', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 9,'code' => 'PE9',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 10, 'code' => 'PE10', 'category_id' => 'PE',  'title' => 'ค่าตอบแทนอื่น', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 11, 'code' => 'PE11', 'category_id' => 'PE',  'title' => 'เงินค่าใช้จ่ายบุคลากรอื่น', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 12, 'code' => 'PE12', 'category_id' => 'PE',  'title' => 'ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.10)', 'name' => 'plan_type', 'data_json' => null],

                // ======================= รายจ่ายจากการดำเนินงาน (OE) =======================
                ['sort' => 13, 'code' => 'OE1',  'category_id' => 'OE',  'title' => 'ค่ายา', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 14, 'code' => 'OE2',  'category_id' => 'OE',  'title' => 'ค่าเวชภัณฑ์มิใช่ยา', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 15, 'code' => 'OE3',  'category_id' => 'OE',  'title' => 'ค่าวัสดุ', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 16, 'code' => 'OE4',  'category_id' => 'OE',  'title' => 'ค่าสาธารณูปโภค', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 17, 'code' => 'OE5',  'category_id' => 'OE',  'title' => 'ค่าใช้สอย', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 18, 'code' => 'OE6',  'category_id' => 'OE',  'title' => 'ค่าใช้จ่ายดำเนินงานอื่น', 'name' => 'plan_type', 'data_json' => null],

                // ======================= รายจ่ายลงทุน (CE) =======================
                ['sort' => 19, 'code' => 'CE1',  'category_id' => 'CE',  'title' => 'ค่าครุภัณฑ์', 'name' => 'plan_type', 'data_json' => [
                    'asset_group_id' => 4
                ]],
                ['sort' => 20, 'code' => 'CE2',  'category_id' => 'CE',  'title' => 'ค่าที่ดินและสิ่งก่อสร้าง', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 21, 'code' => 'CE3',  'category_id' => 'CE',  'title' => 'ค่าครุภัณฑ์ต่ำกว่าเกณฑ์', 'name' => 'plan_type', 'data_json' => [
                    'asset_group_id' => 4
                ]],

                // ======================= รายจ่ายอื่น (OE-OTH) =======================
                ['sort' => 22, 'code' => 'OE-OTH1', 'category_id' => 'OE-OTH', 'title' => 'รายจ่ายสนับสนุน รพ.สต. รพช. รพท. รพศ. สสอ. สสจ.', 'name' => 'plan_type', 'data_json' => null],
                ['sort' => 23, 'code' => 'OE-OTH2', 'category_id' => 'OE-OTH', 'title' => 'รายจ่ายอื่นๆ', 'name' => 'plan_type', 'data_json' => null],
            ];



            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => 'plan_type', 'code' => $item['code']])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name' => 'plan_type',
                        'sort' => $item['sort'],
                        'code' => $item['code'],
                        'category_id' => $item['category_id'],
                        'title' => $item['title'],
                        'data_json' => $item['data_json']

                    ])->execute();
                }
            }
        }
    }

    public function PlanTypeItem()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'plan_type_item'])->count();
        if ($count == 0) {
            $items = [
                // ======================= รายการค่าใช้สอย =======================
                    ['sort' => 1, 'code' => 'OE5-1', 'category_id' => 'OE5', 'title' => 'ค่าใช้จ่ายเดินทางไปราชการ', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 2, 'code' => 'OE5-2', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมอาคารและสิ่งปลูกสร้าง', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 3, 'code' => 'OE5-3', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมครุภัณฑ์สำนักงาน', 'name' => 'plan_type_item', 'data_json' => ['asset_type_id' => 'OFF']],
                    ['sort' => 4, 'code' => 'OE5-4', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมครุภัณฑ์ยานพาหนะและขนส่ง', 'name' => 'plan_type_item', 'data_json' => ['asset_type_id' => 'VEH']],
                    ['sort' => 5, 'code' => 'OE5-5', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมครุภัณฑ์ไฟฟ้าและวิทยุ', 'name' => 'plan_type_item', 'data_json' => ['asset_type_id' => 'ELE']],
                    ['sort' => 6, 'code' => 'OE5-6', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมครุภัณฑ์โฆษณาและเผยแพร่', 'name' => 'plan_type_item', 'data_json' => ['asset_type_id' => 'ADV']],
                    ['sort' => 7, 'code' => 'OE5-7', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'name' => 'plan_type_item', 'data_json' => ['asset_type_id' => 'MED']],
                    ['sort' => 8, 'code' => 'OE5-8', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมครุภัณฑ์คอมพิวเตอร์', 'name' => 'plan_type_item', 'data_json' => ['asset_type_id' => 'COM']],
                    ['sort' => 9, 'code' => 'OE5-9', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมครุภัณฑ์อื่น', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 10, 'code' => 'OE5-10', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาบำรุงรักษาดูแลลิฟท์', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 11, 'code' => 'OE5-11', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาบำรุงรักษาสวนหย่อม', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 12, 'code' => 'OE5-12', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาบำรุงรักษาครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 13, 'code' => 'OE5-13', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาบำรุงรักษาเครื่องปรับอากาศ', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 14, 'code' => 'OE5-14', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาซ่อมแซมบ้านพัก', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 15, 'code' => 'OE5-15', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาประกอบอาหารผู้ป่วย', 'name' => 'plan_type_item', 'data_json' => null],
                    ['sort' => 16, 'code' => 'OE5-16', 'category_id' => 'OE5', 'title' => 'ค่าเช่าเครื่องตรวจวิเคราะห์หาสารชีวเคมีในเลือดและสารคัดหลั่งอัตโนมัติ', 'data_json' => null],
                    ['sort' => 17, 'code' => 'OE5-17', 'category_id' => 'OE5', 'title' => 'ค่าเช่าเครื่องตรวจวิเคราะห์นับเม็ดเลือดแบบสมบูรณ์อัตโนมัติ', 'data_json' => null],
                    ['sort' => 18, 'code' => 'OE5-18', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมากำจัดขยะติดเชื้อ', 'data_json' => null],
                    ['sort' => 19, 'code' => 'OE5-19', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาบริการทางการแพทย์', 'name' => 'plan_type'],
                    ['sort' => 20, 'code' => 'OE5-20', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาบริการอื่น(สนับสนุน)', 'data_json' => null],
                    ['sort' => 21, 'code' => 'OE5-21', 'category_id' => 'OE5', 'title' => 'ค่าจ้างตรวจทางห้องปฏิบัติการ (Lab)', 'data_json' => null],
                    ['sort' => 22, 'code' => 'OE5-22', 'category_id' => 'OE5', 'title' => 'ค่าจ้างตรวจเอ็กซเรย์ (X-Ray)', 'data_json' => null],
                    ['sort' => 23, 'code' => 'OE5-23', 'category_id' => 'OE5', 'title' => 'ค่าธรรมเนียมทางกฎหมาย', 'data_json' => null],
                    ['sort' => 24, 'code' => 'OE5-24', 'category_id' => 'OE5', 'title' => 'ค่าธรรมเนียมธนาคาร', 'data_json' => null],
                    ['sort' => 25, 'code' => 'OE5-25', 'category_id' => 'OE5', 'title' => 'ค่าเบี้ยประกันภัย', 'data_json' => null],
                    ['sort' => 26, 'code' => 'OE5-26', 'category_id' => 'OE5', 'title' => 'ค่าใช้จ่ายในการประชุม', 'data_json' => null],
                    ['sort' => 27, 'code' => 'OE5-27', 'category_id' => 'OE5', 'title' => 'ค่าเช่าเบ็ดเตล็ด', 'data_json' => null],
                    ['sort' => 28, 'code' => 'OE5-28', 'category_id' => 'OE5', 'title' => 'ค่าใช้สอยอื่นๆ', 'data_json' => null],
                    ['sort' => 29, 'code' => 'OE5-29', 'category_id' => 'OE5', 'title' => 'ค่าใช้จ่ายตามโครงการ (UC) (PP)', 'data_json' => null],
                    ['sort' => 30, 'code' => 'OE5-30', 'category_id' => 'OE5', 'title' => 'ค่าใช้จ่ายตามโครงการ (เงินนอกงบประมาณ)', 'data_json' => null],
                    ['sort' => 31, 'code' => 'OE5-31', 'category_id' => 'OE5', 'title' => 'ค่ารักษาตามจ่าย UC ในสังกัด สธ.', 'data_json' => null],
                    ['sort' => 32, 'code' => 'OE5-32', 'category_id' => 'OE5', 'title' => 'ค่ารักษาตามจ่าย UC นอกสังกัด สธ.', 'data_json' => null],
                    ['sort' => 33, 'code' => 'OE5-33', 'category_id' => 'OE5', 'title' => 'ค่ารักษาตามจ่ายคนต่างด้าวและแรงงานต่างด้าว', 'data_json' => null],
                    ['sort' => 34, 'code' => 'OE5-34', 'category_id' => 'OE5', 'title' => 'ค่าใช้จ่ายตามโครง การ (P&P) แรงงานต่างด้าว', 'data_json' => null],
                    ['sort' => 35, 'code' => 'OE5-35', 'category_id' => 'OE5', 'title' => 'ค่าใช้จ่ายตามโครง การ (P&P) บุคคลที่มีปัญหาสถานะและสิทธิ', 'data_json' => null],
                    ['sort' => 36, 'code' => 'OE5-36', 'category_id' => 'OE5', 'title' => 'ค่ารักษาตามจ่ายบุคคลที่มีปัญหาสถานะและสิทธิ', 'data_json' => null],
                    ['sort' => 37, 'code' => 'OE5-37', 'category_id' => 'OE5', 'title' => 'ค่าใช้จ่ายเงินช่วยเหลือผู้ประสบภัย', 'data_json' => null],
                    ['sort' => 38, 'code' => 'OE5-38', 'category_id' => 'OE5', 'title' => 'ค่าบริการทดสอบ/สอบเทียบเครื่องมือแพทย์', 'data_json' => null],
                    ['sort' => 39, 'code' => 'OE5-39', 'category_id' => 'OE5', 'title' => 'ค่าตรวจสอบด้านรังสีวินิจฉัยและการตรวจสอบความปลอดภัยของเครื่องเอกซเรย์', 'data_json' => null],
                    ['sort' => 40, 'code' => 'OE5-40', 'category_id' => 'OE5', 'title' => 'ค่าบริการตรวจสอบวิศวกรรมความปลอดภัยในโรงพยาบาล', 'data_json' => null],
                    ['sort' => 41, 'code' => 'OE5-41', 'category_id' => 'OE5', 'title' => 'ค่าตรวจน้ำทิ้งระบบบำบัดน้ำเสีย', 'data_json' => null],
                    ['sort' => 42, 'code' => 'OE5-42', 'category_id' => 'OE5', 'title' => 'ค่าตรวจวิเคราะห์คุณภาพตัวอย่างน้ำ (กรมอนามัย)', 'data_json' => null],
                    ['sort' => 43, 'code' => 'OE5-43', 'category_id' => 'OE5', 'title' => 'ค่าตรวจไข่หนอนพยาธิในน้ำทิ้ง', 'data_json' => null],
                    ['sort' => 44, 'code' => 'OE5-44', 'category_id' => 'OE5', 'title' => 'ค่าจ้างเหมาตรวจหู ตา ปอด', 'data_json' => null],
                    ['sort' => 45, 'code' => 'OE5-45', 'category_id' => 'OE5', 'title' => 'ค่าเก็บขยะมูลฝอย', 'data_json' => null],
                    ['sort' => 46, 'code' => 'OE5-46', 'category_id' => 'OE5', 'title' => 'ค่าบริการตรวจสอบความไวต่อยารักษาวัณโรค', 'data_json' => null],
                    ['sort' => 47, 'code' => 'OE5-47', 'category_id' => 'OE5', 'title' => 'ค่าบริการการตรวจเพาะเลี้ยงเชื้อและทดสอบความไวต่อยารักษาโรค', 'data_json' => null],
                    ['sort' => 48, 'code' => 'OE5-48', 'category_id' => 'OE5', 'title' => 'ค่าจ้างตรวจทางห้องปฏิบัติการ', 'data_json' => null],
                    ['sort' => 49, 'code' => 'OE5-49', 'category_id' => 'OE5', 'title' => 'ค่าน้ำยาตรวจโลหิตและส่วนประกอบของโลหิต', 'data_json' => null],
                    ['sort' => 50, 'code' => 'OE5-50', 'category_id' => 'OE5', 'title' => 'ค่าสมัครเข้าร่วมโครงการทดสอบความชำนาญทางห้องปฏิบัติการ', 'data_json' => null],
                    ['sort' => 51, 'code' => 'OE5-51', 'category_id' => 'OE5', 'title' => 'บำรุงของสมาชิกโครงการพัฒนาระบบสารสนเทศเปรียบเทียบวัดคุณภาพโรงพยาบาล(THIP)', 'data_json' => null],
                    ['sort' => 52, 'code' => 'OE5-52', 'category_id' => 'OE5', 'title' => 'ค่าต่ออายุการรับบริการแผ่นวัดรังสี', 'data_json' => null],
                    ['sort' => 53, 'code' => 'OE5-53', 'category_id' => 'OE5', 'title' => 'ค่าไฟฟ้า', 'data_json' => null],
                    ['sort' => 54, 'code' => 'OE5-54', 'category_id' => 'OE5', 'title' => 'ค่าโทรศัพท์', 'data_json' => null],
                    ['sort' => 55, 'code' => 'OE5-55', 'category_id' => 'OE5', 'title' => 'ค่าบริการสื่อสารและโทรคมนาคม', 'data_json' => null],
                    ['sort' => 56, 'code' => 'OE5-56', 'category_id' => 'OE5', 'title' => 'ค่าไปรษณีย์และขนส่ง', 'data_json' => null],
                    ['sort' => 57, 'code' => 'OE5-57', 'category_id' => 'OE5', 'title' => 'ค่าน้ำประปาและน้ำบาดาล', 'data_json' => null],
                    ['sort' => 58, 'code' => 'OE5-58', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงอาคารและสิ่งปลูกสร้าง', 'data_json' => null],
                    ['sort' => 59, 'code' => 'OE5-59', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงครุภัณฑ์สำนักงาน', 'data_json' => null],
                    ['sort' => 60, 'code' => 'OE5-60', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงครุภัณฑ์ยานพาหนะและขนส่ง', 'data_json' => null],
                    ['sort' => 61, 'code' => 'OE5-61', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงครุภัณฑ์ไฟฟ้าและวิทยุ', 'data_json' => null],
                    ['sort' => 62, 'code' => 'OE5-62', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงครุภัณฑ์โฆษณาและเผยแพร่', 'data_json' => null],
                    ['sort' => 63, 'code' => 'OE5-63', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'data_json' => null],
                    ['sort' => 64, 'code' => 'OE5-64', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงครุภัณฑ์คอมพิวเตอร์', 'data_json' => null],
                    ['sort' => 65, 'code' => 'OE5-65', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงครุภัณฑ์อื่น', 'data_json' => null],
                    ['sort' => 66, 'code' => 'OE5-66', 'category_id' => 'OE5', 'title' => 'ค่าซ่อมแซมครุภัณฑ์งานบ้านงานครัว', 'data_json' => null],
                    ['sort' => 67, 'code' => 'OE5-67', 'category_id' => 'OE5', 'title' => 'ค่าปรับปรุงครุภัณฑ์งานบ้านงานครัว', 'data_json' => null],
                    ['sort' => 68, 'code' => 'OE5-68', 'category_id' => 'OE5', 'title' => 'ค่ารักษาพยาบาลตามจ่าย UC', 'data_json' => null],
                    ['sort' => 69, 'code' => 'OE5-69', 'category_id' => 'OE5', 'title' => 'ค่ารักษาพยาบาลตามจ่าย ต่างด้าว', 'data_json' => null],
                    ['sort' => 70, 'code' => 'OE5-70', 'category_id' => 'OE5', 'title' => 'ค่ารักษาพยาบาลตามจ่าย บุคคลที่มีปัญหาสถานะและสิทธิ', 'data_json' => null],
                    ['sort' => 71, 'code' => 'OE5-71', 'category_id' => 'OE5', 'title' => 'ค่ารักษาพยาบาลตามจ่าย ปกส.', 'data_json' => null],
                    ['sort' => 72, 'code' => 'OE5-72', 'category_id' => 'OE5', 'title' => 'เงินช่วยเหลือบุคลากรสาธารณสุขฯ(ร.เงินบำรุงฯ ข้อ 9 (10)', 'data_json' => null],
                    ['sort' => 73, 'code' => 'OE5-73', 'category_id' => 'OE5', 'title' => 'เงินสนับสนุนการศึกษาสาขาวิชาชีพที่ขาดแคลน(ร.เงินบำรุงฯ ข้อ 9 (11)', 'data_json' => null],
                    ['sort' => 74, 'code' => 'OE5-74', 'category_id' => 'OE5', 'title' => 'คืนเงินประกันสัญญา', 'data_json' => null],
                    ['sort' => 76, 'code' => 'OE5-76', 'category_id' => 'OE5', 'title' => 'นำส่งเงินกองทุนแรงงานต่างด้าว-ค่าบริหารจัดการ', 'data_json' => null],
                    ['sort' => 78, 'code' => 'OE5-78', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุการแพทย์', 'data_json' => null],
                    ['sort' => 79, 'code' => 'OE5-79', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุวิทยาศาสตร์การแพทย์', 'data_json' => null],
                    ['sort' => 80, 'code' => 'OE5-80', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุเภสัช', 'data_json' => null],
                    ['sort' => 81, 'code' => 'OE5-81', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุทันตกรรม', 'data_json' => null],
                    ['sort' => 82, 'code' => 'OE5-82', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุเอ็กซเรย์', 'data_json' => null],
                    ['sort' => 83, 'code' => 'OE5-83', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุบริโภค', 'data_json' => null],
                    ['sort' => 84, 'code' => 'OE5-84', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุเครื่องแต่งกาย', 'data_json' => null],
                    ['sort' => 85, 'code' => 'OE5-85', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุสำนักงาน', 'data_json' => null],
                    ['sort' => 86, 'code' => 'OE5-86', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุงานบ้านงานครัว', 'data_json' => null],
                    ['sort' => 87, 'code' => 'OE5-87', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุยานพาหนะและขนส่ง', 'data_json' => null],
                    ['sort' => 88, 'code' => 'OE5-88', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุเชื้อเพลิงและหล่อลื่น', 'data_json' => null],
                    ['sort' => 89, 'code' => 'OE5-89', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุไฟฟ้าและวิทยุ', 'data_json' => null],
                    ['sort' => 90, 'code' => 'OE5-90', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุโฆษณาและเผยแพร่', 'data_json' => null],
                    ['sort' => 91, 'code' => 'OE5-91', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุคอมพิวเตอร์', 'data_json' => null],
                    ['sort' => 92, 'code' => 'OE5-92', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุก่อสร้าง', 'data_json' => null],
                    ['sort' => 93, 'code' => 'OE5-93', 'category_id' => 'OE5', 'title' => 'ค่าวัสดุอื่น ๆ', 'data_json' => null],
                    ['sort' => 99, 'code' => 'OE5-99', 'category_id' => 'OE5', 'title' => 'ค่าครุภัณฑ์งบค่าเสื่อม', 'data_json' => null],
                    ['sort' => 100, 'code' => 'OE5-100', 'category_id' => 'OE5', 'title' => 'ค่าครุภัณฑ์เงินบริจาค', 'data_json' => null],
                    ['sort' => 101, 'code' => 'OE5-101', 'category_id' => 'OE5', 'title' => 'ค่าครุภัณฑ์เงินบำรุง', 'data_json' => null],
                    ['sort' => 102, 'code' => 'OE5-102', 'category_id' => 'OE5', 'title' => 'ค่าที่ดินและสิ่งก่อสร้างงบค่าเสื่อม', 'data_json' => null],
                    ['sort' => 103, 'code' => 'OE5-103', 'category_id' => 'OE5', 'title' => 'ค่าที่ดินและสิ่งก่อสร้างเงินบริจาค', 'data_json' => null],
                    ['sort' => 104, 'code' => 'OE5-104', 'category_id' => 'OE5', 'title' => 'ค่าที่ดินและสิ่งก่อสร้างเงินบำรุง', 'data_json' => null],
                    ['sort' => 105, 'code' => 'OE5-105', 'category_id' => 'OE5', 'title' => 'ค่าจ้างลูกจ้างชั่วคราวรายเดือน', 'data_json' => null],
                    ['sort' => 106, 'code' => 'OE5-106', 'category_id' => 'OE5', 'title' => 'ค่าจ้างลูกจ้างชั่วคราวรายวัน', 'data_json' => null],
                    ['sort' => 107, 'code' => 'OE5-107', 'category_id' => 'OE5', 'title' => 'ค่าจ้างลูกจ้างชั่วคราวรายคาบ', 'data_json' => null],
                    ['sort' => 108, 'code' => 'OE5-108', 'category_id' => 'OE5', 'title' => 'ค่าจ้างพนักงานกระทรวงสาธารณสุข', 'data_json' => null],
                    ['sort' => 109, 'code' => 'OE5-109', 'category_id' => 'OE5', 'title' => 'ค่าตอบแทนชันสูตรพลิกศพ', 'data_json' => null],
                    ['sort' => 110, 'code' => 'OE5-110', 'category_id' => 'OE5', 'title' => 'ค่าตอบแทนแพทย์สาขาส่งเสริมพิเศษ', 'data_json' => null],
                    ['sort' => 111, 'code' => 'OE5-111', 'category_id' => 'OE5', 'title' => 'ค่าตอบแทนคณะกรรมการ (ก่อสร้างบ้านพัก)', 'data_json' => null],
                    ['sort' => 112, 'code' => 'OE5-112', 'category_id' => 'OE5', 'title' => 'เงินสมทบกองทุนประกันสังคมส่วนของนายจ้าง (เงินนอกงบประมาณ)', 'data_json' => null],
                    ['sort' => 113, 'code' => 'OE5-113', 'category_id' => 'OE5', 'title' => 'เงินสมทบกองทุนสำรองเลี้ยงชีพพนักงานและเจ้าหน้าที่รัฐ', 'data_json' => null],
                    ['sort' => 114, 'code' => 'OE5-114', 'category_id' => 'OE5', 'title' => 'เงินสมทบกองทุนเงินทดแทน-เงินนอกงบประมาณ', 'data_json' => null],
                    ['sort' => 115, 'code' => 'OE5-115', 'category_id' => 'OE5', 'title' => 'ค่าลงทะเบียน', 'data_json' => null],
                    ['sort' => 117, 'code' => 'OE5-117', 'category_id' => 'OE5', 'title' => 'รายจ่ายลูกหนี้เงินยืม', 'data_json' => null],
                    ['sort' => 118, 'code' => 'OE5-118', 'category_id' => 'OE5', 'title' => 'เงินรับฝากอื่น', 'data_json' => null],
                    ['sort' => 120, 'code' => 'OE5-120', 'category_id' => 'OE5', 'title' => 'ค่าใช้จ่ายในการฝึกอบรม', 'name' => 'plan_type_item', 'data_json' => null]
            ];



            foreach ($items as $item) {
                $exists = (new \yii\db\Query())
                    ->from('categorise')
                    ->where(['name' => 'plan_type_item', 'code' => $item['code']])
                    ->exists();

                if (!$exists) {
                    Yii::$app->db->createCommand()->insert('categorise', [
                        'name' => 'plan_type_item',
                        'sort' => $item['sort'],
                        'code' => $item['code'],
                        'category_id' => $item['category_id'],
                        'title' => $item['title'],
                        'data_json' => isset($item['data_json']) ? $item['data_json'] : null,

                    ])->execute();
                }
            }
        }
    }
}
