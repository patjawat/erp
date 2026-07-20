<?php

use yii\db\Migration;

class m260718_180000_create_medsop_team_setting extends Migration
{
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->createTable('{{%medsop_team_setting}}', [
            'team_group_id' => $this->integer()->notNull(),
            'code' => $this->string(20),
            'document_categories' => $this->text(),
            'leader_employee_id' => $this->integer(),
            'active' => $this->boolean()->notNull()->defaultValue(true),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('pk_medsop_team_setting', '{{%medsop_team_setting}}', 'team_group_id');
        $this->createIndex('ux_medsop_team_setting_code', '{{%medsop_team_setting}}', 'code', true);

        $now = date('Y-m-d H:i:s');
        $this->execute(<<<SQL
INSERT INTO {{%medsop_team_setting}}
    (team_group_id, code, leader_employee_id, active, created_by, updated_by, created_at, updated_at)
SELECT
    s.coordinator_team_group_id,
    LEFT(UPPER(SUBSTRING_INDEX(TRIM(MAX(t.title)), ' ', 1)), 20),
    MAX(s.coordinator_employee_id),
    1,
    MAX(s.created_by),
    MAX(s.updated_by),
    '{$now}',
    '{$now}'
FROM {{%medsop_organization_setting}} s
INNER JOIN {{%team_group}} t ON t.id = s.coordinator_team_group_id
WHERE s.coordinator_team_group_id IS NOT NULL
GROUP BY s.coordinator_team_group_id
SQL);
    }

    public function safeDown()
    {
        $this->dropTable('{{%medsop_team_setting}}');
    }
}
