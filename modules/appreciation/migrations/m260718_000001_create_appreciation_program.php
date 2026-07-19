<?php

use yii\db\Migration;

class m260718_000001_create_appreciation_program extends Migration
{
    public function safeUp()
    {
        $schema = $this->db->schema;

        if ($schema->getTableSchema('{{%appreciation}}', true) === null) {
            $this->createTable('{{%appreciation}}', [
                'id' => $this->primaryKey(),
                'from_emp_id' => $this->integer()->notNull(),
                'to_emp_id' => $this->integer()->notNull(),
                'message' => $this->text()->notNull(),
                'badge_type' => $this->string(64),
                'points_given' => $this->integer()->notNull()->defaultValue(0),
                'created_at' => $this->dateTime()->notNull(),
            ]);
            $this->createIndex('idx-appreciation-from', '{{%appreciation}}', 'from_emp_id');
            $this->createIndex('idx-appreciation-to-created', '{{%appreciation}}', ['to_emp_id', 'created_at']);
        }

        if ($schema->getTableSchema('{{%appreciation_like}}', true) === null) {
            $this->createTable('{{%appreciation_like}}', [
                'id' => $this->primaryKey(),
                'appreciation_id' => $this->integer()->notNull(),
                'emp_id' => $this->integer()->notNull(),
                'created_at' => $this->dateTime()->notNull(),
            ]);
            $this->createIndex('uq-appreciation-like', '{{%appreciation_like}}', ['appreciation_id', 'emp_id'], true);
        }

        if ($schema->getTableSchema('{{%appreciation_challenge}}', true) === null) {
            $this->createTable('{{%appreciation_challenge}}', [
                'id' => $this->primaryKey(), 'name' => $this->string()->notNull(), 'description' => $this->text(),
                'start_at' => $this->date()->notNull(), 'end_at' => $this->date()->notNull(),
                'goal_type' => $this->string(32)->notNull(), 'goal_value' => $this->integer()->notNull(),
                'reward_name' => $this->string(), 'reward_description' => $this->text(),
                'status' => $this->string(20)->notNull()->defaultValue('draft'),
                'created_at' => $this->dateTime()->notNull(), 'updated_at' => $this->dateTime(),
            ]);
        }

        if ($schema->getTableSchema('{{%appreciation_challenge_progress}}', true) === null) {
            $this->createTable('{{%appreciation_challenge_progress}}', [
                'id' => $this->primaryKey(), 'challenge_id' => $this->integer()->notNull(),
                'emp_id' => $this->integer()->notNull(), 'current_value' => $this->integer()->notNull()->defaultValue(0),
                'completed_at' => $this->dateTime(), 'updated_at' => $this->dateTime()->notNull(),
            ]);
            $this->createIndex('uq-appreciation-progress', '{{%appreciation_challenge_progress}}', ['challenge_id', 'emp_id'], true);
        }

        $this->createTable('{{%appreciation_program_year}}', [
            'id' => $this->primaryKey(), 'year' => $this->integer()->notNull(), 'name' => $this->string()->notNull(),
            'points_per_thank' => $this->integer()->notNull()->defaultValue(50),
            'start_at' => $this->date()->notNull(), 'end_at' => $this->date()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'created_at' => $this->dateTime()->notNull(), 'updated_at' => $this->dateTime(),
        ]);
        $this->createIndex('uq-appreciation-program-year', '{{%appreciation_program_year}}', 'year', true);

        $this->createTable('{{%appreciation_value}}', [
            'id' => $this->primaryKey(), 'code' => $this->string(64)->notNull(), 'name' => $this->string(120)->notNull(),
            'icon' => $this->string(32), 'description' => $this->string(500),
            'points' => $this->integer(), 'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
        ]);
        $this->createIndex('uq-appreciation-value-code', '{{%appreciation_value}}', 'code', true);

        $this->createTable('{{%appreciation_level}}', [
            'id' => $this->primaryKey(), 'program_year_id' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull(), 'min_points' => $this->integer()->notNull(),
            'color' => $this->string(20), 'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('idx-appreciation-level-year', '{{%appreciation_level}}', ['program_year_id', 'min_points']);

        $this->createTable('{{%appreciation_reward}}', [
            'id' => $this->primaryKey(), 'program_year_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(), 'description' => $this->text(), 'image_url' => $this->string(500),
            'points_cost' => $this->integer()->notNull(), 'stock_qty' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull(), 'updated_at' => $this->dateTime(),
        ]);
        $this->createIndex('idx-appreciation-reward-year', '{{%appreciation_reward}}', ['program_year_id', 'is_active']);

        $this->createTable('{{%appreciation_redemption}}', [
            'id' => $this->primaryKey(), 'reward_id' => $this->integer()->notNull(),
            'program_year_id' => $this->integer()->notNull(), 'emp_id' => $this->integer()->notNull(),
            'points_used' => $this->integer()->notNull(), 'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'note' => $this->string(500), 'requested_at' => $this->dateTime()->notNull(),
            'processed_at' => $this->dateTime(), 'processed_by' => $this->integer(),
        ]);
        $this->createIndex('idx-appreciation-redemption-emp-year', '{{%appreciation_redemption}}', ['emp_id', 'program_year_id']);

        $this->batchInsert('{{%appreciation_value}}', ['code', 'name', 'icon', 'sort_order'], [
            ['team_player', 'ทำงานเป็นทีม', 'bi-people', 10],
            ['problem_solver', 'แก้ปัญหาอย่างสร้างสรรค์', 'bi-lightbulb', 20],
            ['helpful', 'ช่วยเหลือเกื้อกูล', 'bi-hand-thumbs-up', 30],
            ['leader', 'เป็นผู้นำที่ดี', 'bi-star', 40],
            ['other', 'คุณค่าอื่น ๆ', 'bi-heart', 50],
        ]);
    }

    public function safeDown()
    {
        foreach (['appreciation_redemption', 'appreciation_reward', 'appreciation_level', 'appreciation_value', 'appreciation_program_year'] as $table) {
            $this->dropTable('{{%' . $table . '}}');
        }
        return true;
    }
}
