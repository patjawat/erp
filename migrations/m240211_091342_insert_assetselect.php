<?php

use yii\db\Migration;

/**
 * Class m240211_091342_insert_assetselect
 */
class m240211_091342_insert_assetselect extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql1 = Yii::$app->db->createCommand("select * from categorise where name = 'method_get'")->queryAll();
        if(count($sql1) < 1){
            $this->insert('categorise',['name'=>'method_get','code' =>'1','title'=>'ซื้อ','active' => 1]);
            $this->insert('categorise',['name'=>'method_get','code' =>'2','title'=>'จ้างก่อสร้าง','active' => 1]);
            $this->insert('categorise',['name'=>'method_get','code' =>'3','title'=>'จ้างทำของ/จ้างเหมาบริการ','active' => 1]);
            $this->insert('categorise',['name'=>'method_get','code' =>'4','title'=>'เช่า','active' => 1]);
            $this->insert('categorise',['name'=>'method_get','code' =>'5','title'=>'บริจาค','active' => 1]);
        }   

        $sql2 = Yii::$app->db->createCommand("select * from categorise where name = 'purchase'")->queryAll();
        if(count($sql2) < 1){
            $this->insert('categorise',['name'=>'purchase','code' =>'1','title'=>'ตกลงราคา','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'2','title'=>'สอบราคา','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'3','title'=>'ประกวดราคา','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'4','title'=>'พิเศษ','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'5','title'=>'กรณีพิเศษ','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'6','title'=>'ประมูลด้วยระบบอิเล็กทรอนิกส์ e-auction','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'7','title'=>'e-market','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'8','title'=>'e-bidding','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'9','title'=>'บริจาค','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'10','title'=>'ตกลงราคา ผ่านบัตรพัสดุ','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'11','title'=>'คัดเลือก','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'12','title'=>'เฉพาะเจาะจง','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'13','title'=>'จ้างที่ปรึกษาโดยวิธีประกาศเชิญชวนทั่วไป','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'14','title'=>'จ้างที่ปรึกษาโดยวิธีคัดเลือก','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'15','title'=>'จ้างที่ปรึกษาโดยวิธีเฉพาะเจาะจง','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'16','title'=>'จ้างออกแบบหรือควบคุมงานก่อสร้างโดยวิธีประกาศเชิญชวน','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'17','title'=>'จ้างออกแบบหรือควบคุมงานก่อสร้างโดยวิธีคัดเลือก','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'18','title'=>'จ้างออกแบบหรือควบคุมงานก่อสร้างโดยวิธีเฉพาะเจาะจง','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'19','title'=>'จ้างออกแบบหรือควบคุมงานก่อสร้างโดยวิธีประกวดแบบ','active' => 1]);
        }


        $budgetGroup = Yii::$app->db->createCommand("select * from categorise where name = 'budget_group'")->queryAll();
        if(count($budgetGroup) < 1){
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG1','title'=>'งบบุคลากร','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG2','title'=>'งบดำเนินงาน (ค่าตอนแทน)','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG3','title'=>'งบดำเนินงาน (ค่าใช้สอย)','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG4','title'=>'งบดำเนินงาน (ค่าวัสดุ)','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG5','title'=>'งบดำเนินงาน (ค่าสาธารณูปโภค)','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG6','title'=>'งบลงทุน (ค่าครุภัณฑ์)','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG7','title'=>'งบลงทุน (ค่าที่ดินและสิ่งก่อสร้าง)','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG8','title'=>'งบเงินอุดหนุน','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG8','title'=>'งบรายจ่ายอื่น','active' => 1]);
            $this->insert('categorise',['name'=>'budget_group','code' =>'BG10','title'=>'งบค่าเสื่อม','active' => 1]);
        }

        $sql3 = Yii::$app->db->createCommand("select * from categorise where name = 'budget_type'")->queryAll();
        if(count($sql3) < 1){
            $this->insert('categorise',['name'=>'budget_type','code' =>'BT1','title'=>'งบประมาณ','active' => 1]);
            $this->insert('categorise',['name'=>'budget_type','code' =>'BT2','title'=>'งบค่าเสื่อม','active' => 1]);
            $this->insert('categorise',['name'=>'budget_type','code' =>'BT3','title'=>'เงินบริจาค','active' => 1]);
            $this->insert('categorise',['name'=>'budget_type','code' =>'BT4','title'=>'เงินบำรุง','active' => 1]);
            $this->insert('categorise',['name'=>'budget_type','code' =>'BT5','title'=>'เงิน อปท.','active' => 1]);
            $this->insert('categorise',['name'=>'budget_type','code' =>'BT6','title'=>'เงิน UC','active' => 1]);
            $this->insert('categorise',['name'=>'budget_type','code' =>'BT7','title'=>'เงินอื่นๆ','active' => 1]);
            $this->insert('categorise',['name'=>'budget_type','code' =>'BT8','title'=>'เงินค่าบริการทางการแพทย์ที่เบิกจ่ายในลักษณะงบลงทุน','active' => 1]);
        }

        $sql4 = Yii::$app->db->createCommand("select * from categorise where name = 'asset_status'")->queryAll();
        if(count($sql4) < 1){
            $this->insert('categorise',['name'=>'asset_status','code' =>'1','title'=>'ปกติ','active' => 1]);
            $this->insert('categorise',['name'=>'asset_status','code' =>'2','title'=>'จำหน่ายแล้ว','active' => 1]);
            $this->insert('categorise',['name'=>'asset_status','code' =>'3','title'=>'รอจำหน่าย','active' => 1]);
            $this->insert('categorise',['name'=>'asset_status','code' =>'4','title'=>'ถูกยืม','active' => 1]);
            $this->insert('categorise',['name'=>'asset_status','code' =>'5','title'=>'ส่งซ่อม','active' => 1]);
        }

        $sql5 = Yii::$app->db->createCommand("select * from categorise where name = 'maintain_pm'")->queryAll();
        if(count($sql5) < 1){
            $this->insert('categorise',['name'=>'maintain_pm','code' =>'1','title'=>'โดยหน่วยงานภายใน','active' => 1]);
            $this->insert('categorise',['name'=>'maintain_pm','code' =>'2','title'=>'โดยหน่วยงานภายนอก','active' => 1]);
            $this->insert('categorise',['name'=>'maintain_pm','code' =>'3','title'=>'ไม่ระบุ','active' => 1]);
        }

        $sql6 = Yii::$app->db->createCommand("select * from categorise where name = 'test_cal'")->queryAll();
        if(count($sql6) < 1){
            $this->insert('categorise',['name'=>'test_cal','code' =>'1','title'=>'โดยหน่วยงานภายใน','active' => 1]);
            $this->insert('categorise',['name'=>'test_cal','code' =>'2','title'=>'โดยหน่วยงานภายนอก','active' => 1]);
            $this->insert('categorise',['name'=>'test_cal','code' =>'3','title'=>'ไม่ระบุ','active' => 1]);
        }

        $sql7 = Yii::$app->db->createCommand("select * from categorise where name = 'asset_risk'")->queryAll();
        if(count($sql7) < 1){
            $this->insert('categorise',['name'=>'asset_risk','code' =>'1','title'=>'ต่ำ','active' => 1]);
            $this->insert('categorise',['name'=>'asset_risk','code' =>'2','title'=>'กลาง','active' => 1]);
            $this->insert('categorise',['name'=>'asset_risk','code' =>'3','title'=>'สูง','active' => 1]);
        }

        $sql8 = Yii::$app->db->createCommand("select * from categorise where name = 'asset_group'")->queryAll();
        if(count($sql8) < 1){
            $this->insert('categorise', ['code' => 4, 'name' => 'asset_group', 'title' => 'วัสดุ']);
            $this->insert('categorise', ['code' => 1, 'name' => 'asset_group', 'title' => 'ที่ดิน']);
            $this->insert('categorise', ['code' => 2, 'name' => 'asset_group', 'title' => 'สิ่งปลูกสร้าง']);
            $this->insert('categorise', ['code' => 3, 'name' => 'asset_group', 'title' => 'ครุภัณฑ์']);
            $this->insert('categorise', ['code' => 5, 'name' => 'asset_group', 'title' => 'จ้างเหมา']);
            $this->insert('categorise', ['code' => 6, 'name' => 'asset_group', 'title' => 'อาหารสด']);
        }
        
        //ประเภททรัพย์สิน
        $sqlAssetType = Yii::$app->db->createCommand("select * from categorise where name = 'asset_type'")->queryAll();
        if(count($sqlAssetType) < 1){
            $this->insert('categorise', ['code' => 4, 'name' => 'asset_group', 'title' => 'วัสดุ']);
            
            
        }

        

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240211_091342_insert_assetselect cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240211_091342_insert_assetselect cannot be reverted.\n";

        return false;
    }
    */
}
