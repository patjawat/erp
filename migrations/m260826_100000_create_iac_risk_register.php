<?php

use yii\db\Migration;

/** Phase 4: annual risk register, sourced from approved CSA or entered manually. */
class m260826_100000_create_iac_risk_register extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%iac_risk_register}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'hospital_id' => $this->integer()->notNull(),
            'fiscal_year_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'org_unit_id' => $this->integer()->notNull(),
            'source_type' => $this->string(20)->notNull(),
            'csa_id' => $this->integer()->null(),
            'csa_risk_id' => $this->integer()->null(),
            'risk_name' => $this->string(500)->notNull(),
            'cause' => $this->text()->null(),
            'impact' => $this->text()->null(),
            'likelihood_score' => $this->tinyInteger()->null(),
            'impact_score' => $this->tinyInteger()->null(),
            'adequacy' => $this->string(30)->null(),
            'residual_risk' => $this->text()->null(),
            'status' => $this->string(30)->notNull()->defaultValue('active'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-iac-risk-register-ref', '{{%iac_risk_register}}', 'ref', true);
        $this->createIndex('ux-iac-risk-register-csa-risk', '{{%iac_risk_register}}', 'csa_risk_id', true);
        $this->createIndex('idx-iac-risk-register-scope', '{{%iac_risk_register}}', ['hospital_id', 'fiscal_year_id', 'org_unit_id', 'status']);
        $this->addForeignKey('fk-iac-register-hospital', '{{%iac_risk_register}}', 'hospital_id', '{{%iac_hospital}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-register-fiscal', '{{%iac_risk_register}}', 'fiscal_year_id', '{{%iac_fiscal_year}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-register-csa', '{{%iac_risk_register}}', 'csa_id', '{{%iac_csa}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-register-csa-risk', '{{%iac_risk_register}}', 'csa_risk_id', '{{%iac_csa_risk}}', 'id', 'CASCADE', 'CASCADE');

        $this->execute("INSERT INTO {{%iac_risk_register}} (ref,hospital_id,fiscal_year_id,fiscal_year,org_unit_id,source_type,csa_id,csa_risk_id,risk_name,cause,impact,likelihood_score,impact_score,adequacy,residual_risk,status,created_at,updated_at,created_by,updated_by)
            SELECT REPLACE(UUID(),'-',''),c.hospital_id,c.fiscal_year_id,c.fiscal_year,c.org_unit_id,'csa',c.id,r.id,r.name,r.cause,r.impact,r.likelihood_score,r.impact_score,r.adequacy,r.residual_risk,'active',COALESCE(r.created_at,NOW()),NOW(),r.created_by,r.updated_by
            FROM {{%iac_csa_risk}} r INNER JOIN {{%iac_csa}} c ON c.id=r.csa_id
            WHERE c.status IN ('head_approved','coordinator_revised')");
    }

    public function safeDown()
    {
        $this->dropTable('{{%iac_risk_register}}');
    }
}
