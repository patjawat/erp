<?php

use yii\db\Migration;
use yii\db\Query;
use app\modules\appreciation\services\AppreciationSnapshotService;

class m260719_000005_add_hr_analytics_snapshots extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%appreciation}}', 'program_year_id', $this->integer()->null()->after('to_emp_id'));
        $this->addColumn('{{%appreciation}}', 'value_name_snapshot', $this->string(120)->null()->after('badge_type'));
        $this->addColumn('{{%appreciation}}', 'core_value_code_snapshot', $this->string(64)->null()->after('value_name_snapshot'));
        $this->addColumn('{{%appreciation}}', 'core_value_name_snapshot', $this->string(160)->null()->after('core_value_code_snapshot'));
        $this->addColumn('{{%appreciation}}', 'department_id_snapshot', $this->integer()->null()->after('points_given'));
        $this->addColumn('{{%appreciation}}', 'department_name_snapshot', $this->string(255)->null()->after('department_id_snapshot'));
        $this->addColumn('{{%appreciation}}', 'position_name_snapshot', $this->string(255)->null()->after('department_name_snapshot'));
        $this->addColumn('{{%appreciation}}', 'position_group_name_snapshot', $this->string(255)->null()->after('position_name_snapshot'));
        $this->addColumn('{{%appreciation}}', 'age_at_event_snapshot', $this->smallInteger()->null()->after('position_group_name_snapshot'));
        $this->addColumn('{{%appreciation}}', 'age_band_snapshot', $this->string(32)->null()->after('age_at_event_snapshot'));
        $this->createIndex('idx-appreciation-year-value-emp', '{{%appreciation}}', ['program_year_id', 'core_value_code_snapshot', 'to_emp_id']);

        $this->addColumn('{{%appreciation_participation}}', 'department_id_snapshot', $this->integer()->null()->after('emp_id'));
        $this->addColumn('{{%appreciation_participation}}', 'department_name_snapshot', $this->string(255)->null()->after('department_id_snapshot'));
        $this->addColumn('{{%appreciation_participation}}', 'position_name_snapshot', $this->string(255)->null()->after('department_name_snapshot'));
        $this->addColumn('{{%appreciation_participation}}', 'position_group_name_snapshot', $this->string(255)->null()->after('position_name_snapshot'));
        $this->addColumn('{{%appreciation_participation}}', 'age_at_registration_snapshot', $this->smallInteger()->null()->after('position_group_name_snapshot'));
        $this->addColumn('{{%appreciation_participation}}', 'age_band_snapshot', $this->string(32)->null()->after('age_at_registration_snapshot'));
        $this->createIndex('idx-appreciation-participation-hr', '{{%appreciation_participation}}', ['program_year_id', 'department_id_snapshot', 'status']);

        // Best-effort backfill. Age is calculated at the original event date;
        // current HR organisation data is used when historical HR data is unavailable.
        $years = (new Query())->from('{{%appreciation_program_year}}')->all();
        foreach ($years as $year) {
            $this->update('{{%appreciation}}', ['program_year_id' => (int) $year['id']], ['and',
                ['program_year_id' => null],
                ['between', 'created_at', $year['start_at'] . ' 00:00:00', $year['end_at'] . ' 23:59:59'],
            ]);
        }

        $values = (new Query())->from('{{%appreciation_value}}')->all();
        foreach ($values as $value) {
            $this->update('{{%appreciation}}', [
                'value_name_snapshot' => $value['name'],
                'core_value_code_snapshot' => $value['core_value_code'] ?: $value['code'],
                'core_value_name_snapshot' => $value['core_value_name'] ?: $value['name'],
            ], ['badge_type' => $value['code']]);
        }

        $appreciations = (new Query())->select(['id', 'to_emp_id', 'created_at'])->from('{{%appreciation}}');
        foreach ($appreciations->batch(200, $this->db) as $batch) {
            foreach ($batch as $row) {
                $snapshot = AppreciationSnapshotService::employee($row['to_emp_id'], $row['created_at']);
                $this->update('{{%appreciation}}', [
                    'department_id_snapshot' => $snapshot['department_id'],
                    'department_name_snapshot' => $snapshot['department_name'],
                    'position_name_snapshot' => $snapshot['position_name'],
                    'position_group_name_snapshot' => $snapshot['position_group_name'],
                    'age_at_event_snapshot' => $snapshot['age'],
                    'age_band_snapshot' => $snapshot['age_band'],
                ], ['id' => $row['id']]);
            }
        }

        $participations = (new Query())->select(['id', 'emp_id', 'registered_at'])->from('{{%appreciation_participation}}');
        foreach ($participations->batch(200, $this->db) as $batch) {
            foreach ($batch as $row) {
                $snapshot = AppreciationSnapshotService::employee($row['emp_id'], $row['registered_at']);
                $this->update('{{%appreciation_participation}}', [
                    'department_id_snapshot' => $snapshot['department_id'],
                    'department_name_snapshot' => $snapshot['department_name'],
                    'position_name_snapshot' => $snapshot['position_name'],
                    'position_group_name_snapshot' => $snapshot['position_group_name'],
                    'age_at_registration_snapshot' => $snapshot['age'],
                    'age_band_snapshot' => $snapshot['age_band'],
                ], ['id' => $row['id']]);
            }
        }
    }

    public function safeDown()
    {
        $this->dropIndex('idx-appreciation-participation-hr', '{{%appreciation_participation}}');
        foreach (['age_band_snapshot', 'age_at_registration_snapshot', 'position_group_name_snapshot', 'position_name_snapshot', 'department_name_snapshot', 'department_id_snapshot'] as $column) {
            $this->dropColumn('{{%appreciation_participation}}', $column);
        }

        $this->dropIndex('idx-appreciation-year-value-emp', '{{%appreciation}}');
        foreach (['age_band_snapshot', 'age_at_event_snapshot', 'position_group_name_snapshot', 'position_name_snapshot', 'department_name_snapshot', 'department_id_snapshot', 'core_value_name_snapshot', 'core_value_code_snapshot', 'value_name_snapshot', 'program_year_id'] as $column) {
            $this->dropColumn('{{%appreciation}}', $column);
        }
    }
}
