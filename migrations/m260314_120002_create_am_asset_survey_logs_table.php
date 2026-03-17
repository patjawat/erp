<?php

use yii\db\Migration;

/**
 * Creates table `am_asset_survey_logs` (audit of location/department changes).
 */
class m260314_120002_create_am_asset_survey_logs_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%am_asset_survey_logs}}', [
            'id' => $this->primaryKey(),
            'survey_item_id' => $this->integer()->notNull()->comment('FK am_asset_survey_items.id'),
            'old_location' => $this->string(255)->null(),
            'new_location' => $this->string(255)->null(),
            'old_department' => $this->integer()->null(),
            'new_department' => $this->integer()->null(),
            'changed_at' => $this->dateTime()->null(),
            'changed_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx_survey_logs_item', '{{%am_asset_survey_logs}}', 'survey_item_id');

        $this->addForeignKey(
            'fk_survey_logs_item',
            '{{%am_asset_survey_logs}}',
            'survey_item_id',
            '{{%am_asset_survey_items}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_survey_logs_item', '{{%am_asset_survey_logs}}');
        $this->dropTable('{{%am_asset_survey_logs}}');
    }
}
