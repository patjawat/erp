<?php

use yii\db\Migration;

/** Reusable payroll item types and effective-dated statutory contribution rules. */
class m260830_150000_create_payroll_configuration extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%payroll_item_type}}', [
            'id' => $this->primaryKey(), 'ref' => $this->string(255)->null(),
            'code' => $this->string(50)->notNull(), 'name' => $this->string(255)->notNull(),
            'direction' => $this->string(30)->notNull(),
            'is_recurring' => $this->boolean()->notNull()->defaultValue(true),
            'is_sso_wage' => $this->boolean()->notNull()->defaultValue(false),
            'status' => $this->string(20)->notNull()->defaultValue('active'),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-payroll-item-type-code', '{{%payroll_item_type}}', 'code', true);

        $this->createTable('{{%payroll_contribution_rule}}', [
            'id' => $this->primaryKey(), 'ref' => $this->string(255)->null(),
            'scheme' => $this->string(30)->notNull()->defaultValue('sso_m33'),
            'name' => $this->string(255)->notNull(),
            'effective_from' => $this->date()->notNull(), 'effective_to' => $this->date()->null(),
            'minimum_wage_base' => $this->decimal(15, 2)->notNull(),
            'maximum_wage_base' => $this->decimal(15, 2)->notNull(),
            'employee_rate' => $this->decimal(8, 6)->notNull(),
            'employer_rate' => $this->decimal(8, 6)->notNull(),
            'rounding_mode' => $this->string(30)->notNull()->defaultValue('half_up_whole'),
            'legal_reference' => $this->string(500)->null(),
            'status' => $this->string(20)->notNull()->defaultValue('active'),
            'created_at' => $this->dateTime()->null(), 'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(), 'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-payroll-contribution-effective', '{{%payroll_contribution_rule}}', ['scheme', 'effective_from', 'effective_to']);

        $now = date('Y-m-d H:i:s');
        $this->batchInsert('{{%payroll_item_type}}', ['ref', 'code', 'name', 'direction', 'is_recurring', 'is_sso_wage', 'status', 'created_at', 'updated_at'], [
            [$this->ref(), 'SALARY', 'เงินเดือน', 'earning', 1, 1, 'active', $now, $now],
            [$this->ref(), 'POSITION_ALLOWANCE', 'เงินประจำตำแหน่ง', 'earning', 1, 0, 'active', $now, $now],
            [$this->ref(), 'RETROACTIVE_PAY', 'เงินย้อนหลัง', 'earning', 0, 1, 'active', $now, $now],
            [$this->ref(), 'SSO_EMPLOYEE', 'เงินสมทบประกันสังคมลูกจ้าง', 'deduction', 1, 0, 'active', $now, $now],
            [$this->ref(), 'SSO_EMPLOYER', 'เงินสมทบประกันสังคมนายจ้าง', 'employer_contribution', 1, 0, 'active', $now, $now],
        ]);
        $this->insert('{{%payroll_contribution_rule}}', [
            'ref' => $this->ref(), 'scheme' => 'sso_m33', 'name' => 'ประกันสังคม ม.33 ปี 2569–2571',
            'effective_from' => '2026-01-01', 'effective_to' => '2028-12-31',
            'minimum_wage_base' => 1650, 'maximum_wage_base' => 17500,
            'employee_rate' => 0.05, 'employer_rate' => 0.05,
            'rounding_mode' => 'half_up_whole',
            'legal_reference' => 'กฎกระทรวงกำหนดค่าจ้างขั้นต่ำและขั้นสูงฯ พ.ศ. 2568',
            'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%payroll_contribution_rule}}');
        $this->dropTable('{{%payroll_item_type}}');
    }

    private function ref(): string
    {
        return substr(Yii::$app->getSecurity()->generateRandomString(), 10);
    }
}
