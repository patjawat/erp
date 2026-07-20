<?php

use yii\db\Migration;

class m260718_000002_add_engagement_activities extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%appreciation_value}}', 'core_value_code', $this->string(64)->after('name'));
        $this->addColumn('{{%appreciation_value}}', 'core_value_name', $this->string(160)->after('core_value_code'));

        $this->createTable('{{%appreciation_activity}}', [
            'id' => $this->primaryKey(),
            'program_year_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'activity_type' => $this->string(32)->notNull(),
            'participation_mode' => $this->string(32)->notNull(),
            'external_url' => $this->string(1000),
            'image_url' => $this->string(500),
            'points' => $this->integer()->notNull()->defaultValue(0),
            'capacity' => $this->integer(),
            'start_at' => $this->dateTime()->notNull(),
            'end_at' => $this->dateTime()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'requires_review' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
        ]);
        $this->createIndex('idx-appreciation-activity-year-status', '{{%appreciation_activity}}', ['program_year_id', 'status', 'end_at']);

        $this->createTable('{{%appreciation_participation}}', [
            'id' => $this->primaryKey(),
            'activity_id' => $this->integer()->notNull(),
            'program_year_id' => $this->integer()->notNull(),
            'emp_id' => $this->integer()->notNull(),
            'status' => $this->string(24)->notNull()->defaultValue('registered'),
            'points_awarded' => $this->integer()->notNull()->defaultValue(0),
            'evidence_url' => $this->string(1000),
            'note' => $this->string(500),
            'registered_at' => $this->dateTime()->notNull(),
            'completed_at' => $this->dateTime(),
            'reviewed_at' => $this->dateTime(),
            'reviewed_by' => $this->integer(),
        ]);
        $this->createIndex('uq-appreciation-participation', '{{%appreciation_participation}}', ['activity_id', 'emp_id'], true);
        $this->createIndex('idx-appreciation-participation-year-status', '{{%appreciation_participation}}', ['program_year_id', 'status']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%appreciation_participation}}');
        $this->dropTable('{{%appreciation_activity}}');
        $this->dropColumn('{{%appreciation_value}}', 'core_value_name');
        $this->dropColumn('{{%appreciation_value}}', 'core_value_code');
    }
}
