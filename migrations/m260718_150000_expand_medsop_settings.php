<?php

use yii\db\Migration;

class m260718_150000_expand_medsop_settings extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%medsop_organization_setting}}', 'document_categories', $this->text()->null()->after('coordinator_team'));
        $this->addColumn('{{%medsop_organization_setting}}', 'coordinator_team_group_id', $this->integer()->null()->after('document_categories'));
        $this->addColumn('{{%medsop_organization_setting}}', 'coordinator_employee_id', $this->integer()->null()->after('coordinator_team_group_id'));
        $this->insert('{{%medsop_setting}}', [
            'setting_key' => 'document_types',
            'setting_value' => json_encode(['SOP' => 'SOP', 'WI' => 'WI'], JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%medsop_setting}}', ['setting_key' => 'document_types']);
        $this->dropColumn('{{%medsop_organization_setting}}', 'coordinator_employee_id');
        $this->dropColumn('{{%medsop_organization_setting}}', 'coordinator_team_group_id');
        $this->dropColumn('{{%medsop_organization_setting}}', 'document_categories');
    }
}
