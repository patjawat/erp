<?php

use yii\db\Migration;

class m260822_190000_create_service_profile_section_comment extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%service_profile_section_comment}}',[
            'id'=>$this->primaryKey(),'service_profile_id'=>$this->integer()->notNull(),'section_id'=>$this->integer()->notNull(),'reviewer_employee_id'=>$this->integer()->notNull(),'comment'=>$this->text()->notNull(),'status'=>$this->string(20)->notNull()->defaultValue('open'),'created_at'=>$this->dateTime()->notNull(),'resolved_at'=>$this->dateTime()->null(),'resolved_by_user_id'=>$this->integer()->null(),
        ]);
        $this->createIndex('idx-sp-section-comment-profile-status','{{%service_profile_section_comment}}',['service_profile_id','status']);
        $this->addForeignKey('fk-sp-section-comment-profile','{{%service_profile_section_comment}}','service_profile_id','{{%service_profile}}','id','CASCADE','CASCADE');
        $this->addForeignKey('fk-sp-section-comment-section','{{%service_profile_section_comment}}','section_id','{{%service_profile_section}}','id','CASCADE','CASCADE');
    }
    public function safeDown(){ $this->dropTable('{{%service_profile_section_comment}}'); }
}
