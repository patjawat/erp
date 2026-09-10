<?php
use yii\db\Migration;

class m260910_000001_work_schedule extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%attendance_schedule}}', [
            'id' => $this->primaryKey(), 'name' => $this->string(150)->notNull(),
            'start_time' => $this->string(5)->notNull(), 'end_time' => $this->string(5)->notNull(),
            'weekdays' => $this->string(20)->notNull(), 'holidays' => $this->text(),
            'grace_minutes' => $this->integer()->notNull()->defaultValue(0),
            'window_minutes' => $this->integer()->notNull()->defaultValue(240),
            'created_at' => $this->dateTime()->notNull(), 'created_by' => $this->integer(),
        ]);
        $this->createTable('{{%attendance_assignment}}', [
            'id' => $this->primaryKey(), 'scope' => $this->string(20)->notNull(),
            'target_id' => $this->integer()->notNull(), 'mode' => $this->string(20)->notNull(),
            'schedule_id' => $this->integer(), 'effective_from' => $this->date()->notNull(),
            'reason' => $this->string(2000)->notNull(), 'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer(),
        ]);
        $this->createIndex('idx_att_assignment_target', '{{%attendance_assignment}}', ['scope', 'target_id', 'effective_from']);
        $this->addForeignKey('fk_att_assignment_schedule', '{{%attendance_assignment}}', 'schedule_id', '{{%attendance_schedule}}', 'id');
    }
    public function safeDown() { echo "Keep schedule history; rollback requires a reviewed data migration.\n"; return false; }
}
