<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000005_extend_housing_maintenance_reporter extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%housing_maintenance}}', 'reporter_type', $this->string(20)->notNull()->defaultValue('caretaker')->after('reported_at'));
        $this->addColumn('{{%housing_maintenance}}', 'problem_scope', $this->string(30)->notNull()->defaultValue('structure')->after('reporter_type'));
        $this->addColumn('{{%housing_maintenance}}', 'occupancy_id', $this->integer()->null()->after('problem_scope'));
        $this->addColumn('{{%housing_maintenance}}', 'reporter_emp_id', $this->integer()->null()->after('occupancy_id'));
        $this->addColumn('{{%housing_maintenance}}', 'acknowledgement_status', $this->string(30)->notNull()->defaultValue('not_required')->after('reporter_name'));

        $this->createIndex('ix_housing_maintenance_reporter', '{{%housing_maintenance}}', ['reporter_type', 'problem_scope']);
        $this->createIndex('ix_housing_maintenance_occupancy', '{{%housing_maintenance}}', 'occupancy_id');
        $this->addForeignKey('fk_housing_maintenance_occupancy', '{{%housing_maintenance}}', 'occupancy_id', '{{%housing_occupancy}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_housing_maintenance_reporter_emp', '{{%housing_maintenance}}', 'reporter_emp_id', '{{%employees}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_housing_maintenance_reporter_emp', '{{%housing_maintenance}}');
        $this->dropForeignKey('fk_housing_maintenance_occupancy', '{{%housing_maintenance}}');
        $this->dropIndex('ix_housing_maintenance_occupancy', '{{%housing_maintenance}}');
        $this->dropIndex('ix_housing_maintenance_reporter', '{{%housing_maintenance}}');
        $this->dropColumn('{{%housing_maintenance}}', 'acknowledgement_status');
        $this->dropColumn('{{%housing_maintenance}}', 'reporter_emp_id');
        $this->dropColumn('{{%housing_maintenance}}', 'occupancy_id');
        $this->dropColumn('{{%housing_maintenance}}', 'problem_scope');
        $this->dropColumn('{{%housing_maintenance}}', 'reporter_type');
    }
}
