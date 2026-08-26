<?php
use yii\db\Migration;

class m260826_160000_create_iac_pk1 extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%iac_pk1}}',[
            'id'=>$this->primaryKey(),'ref'=>$this->string(64)->notNull(),'hospital_id'=>$this->integer()->notNull(),'fiscal_year_id'=>$this->integer()->notNull(),'fiscal_year'=>$this->integer()->notNull(),'status'=>$this->string(30)->notNull()->defaultValue('draft'),
            'recipient'=>$this->string(255)->notNull(),'assessment_text'=>$this->text()->notNull(),'conclusion_text'=>$this->text()->notNull(),'weakness_text'=>$this->text()->null(),
            'signer_emp_id'=>$this->integer()->null(),'signer_name'=>$this->string(255)->null(),'signer_position'=>$this->string(255)->null(),'signature_type'=>$this->string(20)->notNull()->defaultValue('system'),'signature_data'=>$this->text()->null(),
            'created_at'=>$this->dateTime()->null(),'updated_at'=>$this->dateTime()->null(),'created_by'=>$this->integer()->null(),'updated_by'=>$this->integer()->null(),
        ]);
        $this->createIndex('ux-iac-pk1-ref','{{%iac_pk1}}','ref',true);$this->createIndex('ux-iac-pk1-year','{{%iac_pk1}}',['hospital_id','fiscal_year_id'],true);
        $this->addForeignKey('fk-iac-pk1-hospital','{{%iac_pk1}}','hospital_id','{{%iac_hospital}}','id','CASCADE','CASCADE');$this->addForeignKey('fk-iac-pk1-fiscal','{{%iac_pk1}}','fiscal_year_id','{{%iac_fiscal_year}}','id','CASCADE','CASCADE');
    }
    public function safeDown(){ $this->dropTable('{{%iac_pk1}}'); }
}
