<?php
use yii\db\Migration;
class m260826_190000_create_iac_risk_followup extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%iac_risk_followup}}',['id'=>$this->primaryKey(),'ref'=>$this->string(64)->notNull(),'hospital_id'=>$this->integer()->notNull(),'fiscal_year_id'=>$this->integer()->notNull(),'reporting_period_id'=>$this->integer()->notNull(),'org_unit_id'=>$this->integer()->notNull(),'risk_register_id'=>$this->integer()->null(),'sequence'=>$this->integer()->notNull(),'mission_objective'=>$this->text()->null(),'existing_control'=>$this->text()->null(),'residual_risk'=>$this->text()->null(),'improvement_plan'=>$this->text()->notNull(),'responsible_person'=>$this->text()->null(),'status_code'=>$this->string(30)->notNull()->defaultValue('not_started'),'followup_method'=>$this->text()->null(),'result_summary'=>$this->text()->null(),'comment'=>$this->text()->null(),'created_at'=>$this->dateTime()->null(),'updated_at'=>$this->dateTime()->null(),'created_by'=>$this->integer()->null(),'updated_by'=>$this->integer()->null()]);
        $this->createIndex('ux-iac-followup-ref','{{%iac_risk_followup}}','ref',true);$this->createIndex('ux-iac-followup-scope-risk','{{%iac_risk_followup}}',['hospital_id','fiscal_year_id','reporting_period_id','org_unit_id','risk_register_id'],true);$this->createIndex('idx-iac-followup-scope','{{%iac_risk_followup}}',['hospital_id','fiscal_year_id','reporting_period_id','org_unit_id']);
        $this->addForeignKey('fk-iac-followup-hospital','{{%iac_risk_followup}}','hospital_id','{{%iac_hospital}}','id','CASCADE','CASCADE');$this->addForeignKey('fk-iac-followup-fiscal','{{%iac_risk_followup}}','fiscal_year_id','{{%iac_fiscal_year}}','id','CASCADE','CASCADE');$this->addForeignKey('fk-iac-followup-period','{{%iac_risk_followup}}','reporting_period_id','{{%iac_reporting_period}}','id','CASCADE','CASCADE');$this->addForeignKey('fk-iac-followup-risk','{{%iac_risk_followup}}','risk_register_id','{{%iac_risk_register}}','id','SET NULL','CASCADE');
    }
    public function safeDown(){ $this->dropTable('{{%iac_risk_followup}}'); }
}
