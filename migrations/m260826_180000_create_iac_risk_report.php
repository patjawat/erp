<?php
use yii\db\Migration;

class m260826_180000_create_iac_risk_report extends Migration
{
    public function safeUp()
    {
        $audit=['created_at'=>$this->dateTime()->null(),'updated_at'=>$this->dateTime()->null(),'created_by'=>$this->integer()->null(),'updated_by'=>$this->integer()->null()];
        $this->createTable('{{%iac_risk_report}}',array_merge(['id'=>$this->primaryKey(),'ref'=>$this->string(64)->notNull(),'hospital_id'=>$this->integer()->notNull(),'fiscal_year_id'=>$this->integer()->notNull(),'reporting_period_id'=>$this->integer()->notNull(),'org_unit_id'=>$this->integer()->notNull(),'revision_no'=>$this->integer()->notNull()->defaultValue(1),'status'=>$this->string(30)->notNull()->defaultValue('draft'),'submitted_at'=>$this->dateTime()->null(),'submitted_by'=>$this->integer()->null(),'approved_at'=>$this->dateTime()->null(),'approved_by'=>$this->integer()->null(),'returned_at'=>$this->dateTime()->null(),'returned_by'=>$this->integer()->null(),'return_note'=>$this->text()->null(),'signer_name'=>$this->string(255)->null(),'signer_position'=>$this->string(255)->null(),'signature_data'=>$this->text()->null()],$audit));
        $this->createIndex('ux-iac-risk-report-ref','{{%iac_risk_report}}','ref',true);$this->createIndex('ux-iac-risk-report-scope-rev','{{%iac_risk_report}}',['hospital_id','fiscal_year_id','reporting_period_id','org_unit_id','revision_no'],true);
        $this->addForeignKey('fk-iac-risk-report-hospital','{{%iac_risk_report}}','hospital_id','{{%iac_hospital}}','id','CASCADE','CASCADE');$this->addForeignKey('fk-iac-risk-report-fiscal','{{%iac_risk_report}}','fiscal_year_id','{{%iac_fiscal_year}}','id','CASCADE','CASCADE');$this->addForeignKey('fk-iac-risk-report-period','{{%iac_risk_report}}','reporting_period_id','{{%iac_reporting_period}}','id','CASCADE','CASCADE');
        $this->createTable('{{%iac_risk_report_item}}',array_merge(['id'=>$this->primaryKey(),'risk_report_id'=>$this->integer()->notNull(),'risk_register_id'=>$this->integer()->null(),'sequence'=>$this->integer()->notNull(),'mission_objective'=>$this->text()->null(),'risk_name'=>$this->string(500)->notNull(),'existing_control'=>$this->text()->null(),'control_assessment'=>$this->string(255)->null(),'residual_risk'=>$this->text()->null(),'improvement_plan'=>$this->text()->null(),'responsible_person'=>$this->text()->null()],$audit));
        $this->createIndex('idx-iac-risk-report-item','{{%iac_risk_report_item}}',['risk_report_id','sequence']);$this->addForeignKey('fk-iac-risk-report-item-parent','{{%iac_risk_report_item}}','risk_report_id','{{%iac_risk_report}}','id','CASCADE','CASCADE');$this->addForeignKey('fk-iac-risk-report-item-source','{{%iac_risk_report_item}}','risk_register_id','{{%iac_risk_register}}','id','SET NULL','CASCADE');
    }
    public function safeDown(){ $this->dropTable('{{%iac_risk_report_item}}');$this->dropTable('{{%iac_risk_report}}'); }
}
