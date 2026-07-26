<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000001_add_responsible_employee_to_housing_building extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%housing_building}}',
            'responsible_employee_id',
            $this->integer()->after('description')
        );
        $this->createIndex(
            'ix_housing_building_responsible_employee',
            '{{%housing_building}}',
            'responsible_employee_id'
        );
        $this->addForeignKey(
            'fk_housing_building_responsible_employee',
            '{{%housing_building}}',
            'responsible_employee_id',
            '{{%employees}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey(
            'fk_housing_building_responsible_employee',
            '{{%housing_building}}'
        );
        $this->dropIndex(
            'ix_housing_building_responsible_employee',
            '{{%housing_building}}'
        );
        $this->dropColumn('{{%housing_building}}', 'responsible_employee_id');
    }
}
