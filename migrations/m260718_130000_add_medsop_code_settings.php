<?php

use yii\db\Migration;

class m260718_130000_add_medsop_code_settings extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%medsop_organization_setting}}', 'code', $this->string(20)->null()->after('organization_id'));
        $this->addColumn('{{%medsop_organization_setting}}', 'coordinator_team', $this->string(255)->null()->after('code'));
        $this->createIndex('ux_medsop_org_setting_code', '{{%medsop_organization_setting}}', 'code', true);

        $this->createTable('{{%medsop_setting}}', [
            'setting_key' => $this->string(100)->notNull(),
            'setting_value' => $this->text(),
            'updated_by' => $this->integer(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');
        $this->addPrimaryKey('pk_medsop_setting', '{{%medsop_setting}}', 'setting_key');
        $this->batchInsert('{{%medsop_setting}}', ['setting_key', 'setting_value', 'updated_at'], [
            ['sop_prefix', 'SP', date('Y-m-d H:i:s')],
            ['wi_prefix', 'WI', date('Y-m-d H:i:s')],
            ['code_pattern', '{type}-{org}-{year}-{sequence}', date('Y-m-d H:i:s')],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%medsop_setting}}');
        $this->dropIndex('ux_medsop_org_setting_code', '{{%medsop_organization_setting}}');
        $this->dropColumn('{{%medsop_organization_setting}}', 'coordinator_team');
        $this->dropColumn('{{%medsop_organization_setting}}', 'code');
    }
}
