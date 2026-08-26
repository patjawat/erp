<?php
use yii\db\Migration;

class m260826_170000_add_pk5_fields_to_risk_register extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%iac_risk_register}}','mission_objective',$this->text()->null()->after('csa_risk_id'));
        $this->addColumn('{{%iac_risk_register}}','existing_control',$this->text()->null()->after('residual_risk'));
        $this->addColumn('{{%iac_risk_register}}','improvement_plan',$this->text()->null()->after('existing_control'));
        $this->addColumn('{{%iac_risk_register}}','responsible_person',$this->text()->null()->after('improvement_plan'));
    }
    public function safeDown(){ $this->dropColumn('{{%iac_risk_register}}','responsible_person');$this->dropColumn('{{%iac_risk_register}}','improvement_plan');$this->dropColumn('{{%iac_risk_register}}','existing_control');$this->dropColumn('{{%iac_risk_register}}','mission_objective'); }
}
