<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260803_000004_seed_pm_strategy_plan_2568_2572 extends Migration
{
    public function safeUp(): void
    {
        if ((new \yii\db\Query())->from('{{%pm_strategy_plan}}')->where(['code'=>'HOS-2568-2572','version'=>1])->exists()) return;
        $now=date('Y-m-d H:i:s');
        $this->insert('{{%pm_strategy_plan}}',[
            'code'=>'HOS-2568-2572','name'=>'แผนยุทธศาสตร์โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย ปี 2568-2572','version'=>1,
            'start_year'=>2568,'end_year'=>2572,'vision'=>"โรงพยาบาลแห่งความสุข ต้นแบบการบริการด้วยหัวใจความเป็นมนุษย์\nมุ่งสู่บริการสุขภาพที่เป็นเลิศด้วยนวัตกรรมและการมีส่วนร่วมของภาคีเครือข่าย",
            'status'=>'draft','source_note'=>'Google Sheet: 02-HOS-68-แผนยุทธศาสตร์ 5 ปี (พ.ศ.2568-2572)-เดชา','ref'=>Yii::$app->security->generateRandomString(32),'created_at'=>$now,'updated_at'=>$now,
        ]);
        $planId=(int)$this->db->getLastInsertID();
        $items=[
            ['M1','พัฒนาบุคลากรให้มีสมรรถนะสูงและมีความสุขในการทำงาน','M1.S1','ยกระดับศักยภาพบุคลากรสู่องค์กรแห่งความสุขและการเรียนรู้'],
            ['M2','พัฒนาระบบบริการสุขภาพด้วยหัวใจความเป็นมนุษย์','M2.S1','ยกระดับการบริการสุขภาพที่เน้นผู้ป่วยเป็นศูนย์กลาง'],
            ['M3','พัฒนาระบบบริการสุขภาพที่เป็นเลิศและเป็นมิตรกับผู้รับบริการ','M3.S1','พัฒนาศักยภาพการให้บริการทางการแพทย์ที่ครอบคลุม'],
            ['M4','ส่งเสริมการวิจัยและพัฒนานวัตกรรมด้านสุขภาพที่ตอบสนองความต้องการของชุมชน','M4.S1','สร้างสรรค์งานวิจัยและนวัตกรรมที่มีคุณค่า'],
            ['M5','เสริมสร้างการมีส่วนร่วมของภาคีเครือข่ายในการดูแลสุขภาพชุมชน','M5.S1','เสริมพลังชุมชนและเครือข่ายในการจัดการสุขภาพ'],
            ['M6','พัฒนาระบบบริหารจัดการที่มีประสิทธิภาพและยั่งยืน','M6.S1','การบริหารจัดการทรัพยากรและสิ่งแวดล้อมอย่างมีประสิทธิภาพ'],
        ];
        foreach($items as $sort=>$item){
            $this->insert('{{%pm_strategy_mission}}',['plan_id'=>$planId,'code'=>$item[0],'name'=>$item[1],'sort_order'=>$sort+1,'is_active'=>1,'ref'=>Yii::$app->security->generateRandomString(32),'created_at'=>$now,'updated_at'=>$now]);
            $missionId=(int)$this->db->getLastInsertID();
            $this->insert('{{%pm_strategy_issue}}',['mission_id'=>$missionId,'code'=>$item[2],'name'=>$item[3],'sort_order'=>1,'is_active'=>1,'ref'=>Yii::$app->security->generateRandomString(32),'created_at'=>$now,'updated_at'=>$now]);
        }
    }
    public function safeDown(): void
    {
        $id=(new \yii\db\Query())->select('id')->from('{{%pm_strategy_plan}}')->where(['code'=>'HOS-2568-2572','version'=>1])->scalar();
        if($id)$this->delete('{{%pm_strategy_plan}}',['id'=>$id]);
    }
}
