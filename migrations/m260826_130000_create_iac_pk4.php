<?php
use yii\db\Migration;
class m260826_130000_create_iac_pk4 extends Migration
{
    public function safeUp()
    {
        $audit=['created_at'=>$this->dateTime()->null(),'updated_at'=>$this->dateTime()->null(),'created_by'=>$this->integer()->null(),'updated_by'=>$this->integer()->null()];
        $this->createTable('{{%iac_pk4}}',array_merge(['id'=>$this->primaryKey(),'ref'=>$this->string(64)->notNull(),'hospital_id'=>$this->integer()->notNull(),'fiscal_year_id'=>$this->integer()->notNull(),'fiscal_year'=>$this->integer()->notNull(),'org_unit_id'=>$this->integer()->notNull(),'status'=>$this->string(30)->notNull()->defaultValue('draft'),'summary'=>$this->text()->null()],$audit));
        $this->createIndex('ux-iac-pk4-ref','{{%iac_pk4}}','ref',true);$this->createIndex('ux-iac-pk4-unit-year','{{%iac_pk4}}',['hospital_id','fiscal_year_id','org_unit_id'],true);
        $this->addForeignKey('fk-iac-pk4-hospital','{{%iac_pk4}}','hospital_id','{{%iac_hospital}}','id','CASCADE','CASCADE');$this->addForeignKey('fk-iac-pk4-fiscal','{{%iac_pk4}}','fiscal_year_id','{{%iac_fiscal_year}}','id','CASCADE','CASCADE');
        $this->createTable('{{%iac_pk4_item}}',array_merge(['id'=>$this->primaryKey(),'pk4_id'=>$this->integer()->notNull(),'component_code'=>$this->string(30)->notNull(),'sequence'=>$this->integer()->notNull(),'component_name'=>$this->string(255)->notNull(),'evaluation_summary'=>$this->text()->null()],$audit));
        $this->createIndex('ux-iac-pk4-component','{{%iac_pk4_item}}',['pk4_id','component_code'],true);$this->addForeignKey('fk-iac-pk4-item-parent','{{%iac_pk4_item}}','pk4_id','{{%iac_pk4}}','id','CASCADE','CASCADE');
    }
    public function safeDown(){ $this->dropTable('{{%iac_pk4_item}}');$this->dropTable('{{%iac_pk4}}'); }
}
