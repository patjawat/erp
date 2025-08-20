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
            'plan_budget_type_id' => $this->string(50)->comment('ประเภทงบ'),
            'plan_item_id' => $this->string(50)->comment('รายการแผน'),
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
            'data_json' => $this->json()->comment('ยานพาหนะ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);
        $this->PlanGroup();
        $this->PlanType();
        $this->PlanItem();
        $this->PlanPriceRef();
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
    public function PlanType()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'plan_budget_type'])->count();
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

    //รายการแผน
    public function PlanItem()
    {
        $count = (new \yii\db\Query())->from('categorise')->where(['name' => 'plan_item'])->count();
        if ($count == 0) {
            $items = [
                // ======================= รายจ่ายบุคลากร (PE) =======================
                ['code' => 'PE1',  'category_id' => 'PE',  'title' => 'ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง','name' => 'plan_item'],
                ['code' => 'PE2',  'category_id' => 'PE',  'title' => 'ค่าล่วงเวลางานบริการ / งานสนับสนุน','name' => 'plan_item'],
                ['code' => 'PE3',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของเจ้าหน้าที่','name' => 'plan_item'],
                ['code' => 'PE4',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน','name' => 'plan_item'],
                ['code' => 'PE5',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)','name' => 'plan_item'],
                ['code' => 'PE6',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)','name' => 'plan_item'],
                ['code' => 'PE7',  'category_id' => 'PE',  'title' => 'เงินเพิ่ม (พ.ต.ส)','name' => 'plan_item'],
                ['code' => 'PE8',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานของเจ้าหน้าที่ (นอกเวลา) ฉ5','name' => 'plan_item'],
                ['code' => 'PE9',  'category_id' => 'PE',  'title' => 'ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)','name' => 'plan_item'],
                ['code' => 'PE10', 'category_id' => 'PE',  'title' => 'ค่าตอบแทนอื่น','name' => 'plan_item'],
                ['code' => 'PE11', 'category_id' => 'PE',  'title' => 'เงินค่าใช้จ่ายบุคลากรอื่น','name' => 'plan_item'],
                ['code' => 'PE12', 'category_id' => 'PE',  'title' => 'ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.10)','name' => 'plan_item'],

                // ======================= รายจ่ายจากการดำเนินงาน (OE) =======================
                ['code' => 'OE1',  'category_id' => 'OE',  'title' => 'ค่ายา','name' => 'plan_item'],
                ['code' => 'OE2',  'category_id' => 'OE',  'title' => 'ค่าเวชภัณฑ์มิใช่ยา','name' => 'plan_item'],
                ['code' => 'OE3',  'category_id' => 'OE',  'title' => 'ค่าวัสดุ','name' => 'plan_item'],
                ['code' => 'OE4',  'category_id' => 'OE',  'title' => 'ค่าสาธารณูปโภค','name' => 'plan_item'],
                ['code' => 'OE5',  'category_id' => 'OE',  'title' => 'ค่าใช้สอย','name' => 'plan_item'],
                ['code' => 'OE6',  'category_id' => 'OE',  'title' => 'ค่าใช้จ่ายดำเนินงานอื่น','name' => 'plan_item'],

                // ======================= รายจ่ายลงทุน (CE) =======================
                ['code' => 'CE1',  'category_id' => 'CE',  'title' => 'ค่าครุภัณฑ์','name' => 'plan_item'],
                ['code' => 'CE2',  'category_id' => 'CE',  'title' => 'ค่าที่ดินและสิ่งก่อสร้าง','name' => 'plan_item'],
                ['code' => 'CE3',  'category_id' => 'CE',  'title' => 'ค่าครุภัณฑ์ต่ำกว่าเกณฑ์','name' => 'plan_item'],

                // ======================= รายจ่ายอื่น (OE-OTH) =======================
                ['code' => 'OE-OTH1', 'category_id' => 'OE-OTH', 'title' => 'รายจ่ายสนับสนุน รพ.สต. รพช. รพท. รพศ. สสอ. สสจ.','name' => 'plan_item'],
                ['code' => 'OE-OTH2', 'category_id' => 'OE-OTH', 'title' => 'รายจ่ายอื่นๆ','name' => 'plan_item'],
            ];



      foreach ($items as $item) {
            $exists = (new \yii\db\Query())
                ->from('categorise')
                ->where(['name' => 'plan_item', 'code' => $item['code']])
                ->exists();

            if (!$exists) {
                Yii::$app->db->createCommand()->insert('categorise', [
                    'name' => 'plan_item',
                    'code' => $item['code'],
                    'category_id' => $item['category_id'],
                    'title' => $item['title'],

                ])->execute();
            }
        }
        }
    }
}
