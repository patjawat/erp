<?php

use yii\db\Migration;

/** Phase 1 foundation for IAC&Risk: hospital scope, fiscal years, periods, audit and RBAC. */
class m260825_120000_create_iac_risk_phase1 extends Migration
{
    private array $permissions = [
        'iacRiskView' => 'ดูข้อมูล IAC&Risk ตามขอบเขตหน่วยงาน',
        'iacRiskAuthor' => 'จัดทำข้อมูล IAC&Risk ของหน่วยงาน',
        'iacRiskUnitApprove' => 'รับรองข้อมูล IAC&Risk ในฐานะหัวหน้าหน่วยงาน',
        'iacRiskCoordinate' => 'ตรวจและแก้ไขข้อมูล IAC&Risk ในฐานะทีมประสาน',
        'iacRiskCommittee' => 'พิจารณาความเสี่ยงระดับโรงพยาบาล',
        'iacRiskDirector' => 'รับรองเอกสาร IAC&Risk ระดับโรงพยาบาล',
        'iacRiskAdmin' => 'ดูแลการตั้งค่า IAC&Risk',
    ];

    public function safeUp()
    {
        $this->createTable('{{%iac_hospital}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'code' => $this->string(30)->notNull(),
            'name' => $this->string(255)->notNull(),
            'province' => $this->string(100)->null(),
            'active' => $this->boolean()->notNull()->defaultValue(true),
            'is_current' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-iac-hospital-code', '{{%iac_hospital}}', 'code', true);
        $this->createIndex('idx-iac-hospital-current', '{{%iac_hospital}}', ['active', 'is_current']);

        $this->createTable('{{%iac_fiscal_year}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'hospital_id' => $this->integer()->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'is_current' => $this->boolean()->notNull()->defaultValue(false),
            'opened_at' => $this->dateTime()->null(),
            'opened_by' => $this->integer()->null(),
            'closed_at' => $this->dateTime()->null(),
            'closed_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-iac-fiscal-hospital-year', '{{%iac_fiscal_year}}', ['hospital_id', 'fiscal_year'], true);
        $this->createIndex('idx-iac-fiscal-current', '{{%iac_fiscal_year}}', ['hospital_id', 'is_current', 'status']);
        $this->addForeignKey('fk-iac-fiscal-hospital', '{{%iac_fiscal_year}}', 'hospital_id', '{{%iac_hospital}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%iac_reporting_period}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'fiscal_year_id' => $this->integer()->notNull(),
            'code' => $this->string(30)->notNull(),
            'name' => $this->string(100)->notNull(),
            'sequence' => $this->integer()->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'due_date' => $this->date()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'opened_at' => $this->dateTime()->null(),
            'closed_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-iac-period-code', '{{%iac_reporting_period}}', ['fiscal_year_id', 'code'], true);
        $this->createIndex('idx-iac-period-order', '{{%iac_reporting_period}}', ['fiscal_year_id', 'sequence']);
        $this->addForeignKey('fk-iac-period-fiscal', '{{%iac_reporting_period}}', 'fiscal_year_id', '{{%iac_fiscal_year}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%iac_activity}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'hospital_id' => $this->integer()->notNull(),
            'fiscal_year_id' => $this->integer()->null(),
            'reporting_period_id' => $this->integer()->null(),
            'org_unit_id' => $this->integer()->null(),
            'entity_type' => $this->string(60)->notNull(),
            'entity_id' => $this->integer()->null(),
            'action' => $this->string(60)->notNull(),
            'from_status' => $this->string(30)->null(),
            'to_status' => $this->string(30)->null(),
            'message' => $this->text()->null(),
            'data_json' => $this->json()->null(),
            'ip_address' => $this->string(45)->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-iac-activity-entity', '{{%iac_activity}}', ['entity_type', 'entity_id', 'created_at']);
        $this->createIndex('idx-iac-activity-scope', '{{%iac_activity}}', ['hospital_id', 'fiscal_year_id', 'org_unit_id', 'created_at']);
        $this->addForeignKey('fk-iac-activity-hospital', '{{%iac_activity}}', 'hospital_id', '{{%iac_hospital}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-iac-activity-fiscal', '{{%iac_activity}}', 'fiscal_year_id', '{{%iac_fiscal_year}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-iac-activity-period', '{{%iac_activity}}', 'reporting_period_id', '{{%iac_reporting_period}}', 'id', 'SET NULL', 'CASCADE');

        $auth = Yii::$app->authManager;
        foreach ($this->permissions as $name => $description) {
            if ($auth->getPermission($name) === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
            }
        }
        if (($admin = $auth->getRole('admin')) !== null) {
            foreach (array_keys($this->permissions) as $name) {
                $permission = $auth->getPermission($name);
                if ($permission !== null && !$auth->hasChild($admin, $permission)) {
                    $auth->addChild($admin, $permission);
                }
            }
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (array_reverse(array_keys($this->permissions)) as $name) {
            if ($permission = $auth->getPermission($name)) {
                $auth->remove($permission);
            }
        }
        $auth->invalidateCache();

        $this->dropTable('{{%iac_activity}}');
        $this->dropTable('{{%iac_reporting_period}}');
        $this->dropTable('{{%iac_fiscal_year}}');
        $this->dropTable('{{%iac_hospital}}');
    }
}
