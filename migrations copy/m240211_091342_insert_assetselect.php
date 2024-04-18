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
            $this->insert('categorise',['name'=>'purchase','code' =>'1','title'=>'เฉพาะเจาะจง','active' => 1]);
            $this->insert('categorise',['name'=>'purchase','code' =>'2','title'=>'บริจาค','active' => 1]);
        }

        $sql3 = Yii::$app->db->createCommand("select * from categorise where name = 'budget_detail'")->queryAll();
        if(count($sql3) < 1){
            $this->insert('categorise',['name'=>'budget_detail','code' =>'1','title'=>'งบประมาณ','active' => 1]);
            $this->insert('categorise',['name'=>'budget_detail','code' =>'2','title'=>'เงิน UC','active' => 1]);
            $this->insert('categorise',['name'=>'budget_detail','code' =>'3','title'=>'เงินบำรุง','active' => 1]);
            $this->insert('categorise',['name'=>'budget_detail','code' =>'4','title'=>'เงินบริจาค','active' => 1]);
            $this->insert('categorise',['name'=>'budget_detail','code' =>'5','title'=>'เงินอื่นๆ','active' => 1]);
            $this->insert('categorise',['name'=>'budget_detail','code' =>'6','title'=>'เงินค่าบริการทางการแพทย์ที่เบิกจ่ายในลักษณะงบลงทุน','active' => 1]);
        }

        $sql4 = Yii::$app->db->createCommand("select * from categorise where name = 'assetstatus'")->queryAll();
        if(count($sql4) < 1){
            $this->insert('categorise',['name'=>'assetstatus','code' =>'1','title'=>'ปกติ','active' => 1]);
            $this->insert('categorise',['name'=>'assetstatus','code' =>'2','title'=>'จำหน่ายแล้ว','active' => 1]);
            $this->insert('categorise',['name'=>'assetstatus','code' =>'3','title'=>'รอจำหน่าย','active' => 1]);
            $this->insert('categorise',['name'=>'assetstatus','code' =>'4','title'=>'ถูกยืม','active' => 1]);
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
