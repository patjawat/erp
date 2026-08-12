<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ยกระดับ pm_strategy_indicator_value เป็นทะเบียน "ตัวชี้วัดรายปี"
 * ตัวชี้วัดแม่คงที่ตลอดอายุแผน ส่วนนิยาม/ค่าเป้าหมาย/สถานะการใช้งานเก็บแยกรายปีงบประมาณ
 */
final class m260808_000001_create_pm_strategy_indicator_year extends Migration
{
    public function safeUp(): void
    {
        $this->dropForeignKey('fk-pm_strategy_indicator_value-indicator', '{{%pm_strategy_indicator_value}}');
        $this->dropIndex('uq-pm_strategy_indicator_value-year', '{{%pm_strategy_indicator_value}}');
        $this->dropIndex('idx-pm_strategy_indicator_value-indicator_id', '{{%pm_strategy_indicator_value}}');
        $this->renameTable('{{%pm_strategy_indicator_value}}', '{{%pm_strategy_indicator_year}}');

        $table = '{{%pm_strategy_indicator_year}}';
        $this->addColumn($table, 'status', $this->string(20)->notNull()->defaultValue('active'));
        $this->addColumn($table, 'name_override', $this->text()->null());
        $this->addColumn($table, 'unit_override', $this->string(100)->null());
        $this->addColumn($table, 'weight', $this->decimal(6, 2)->null());
        $this->addColumn($table, 'sort_order', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn($table, 'copied_from_id', $this->integer()->null());
        $this->addColumn($table, 'cancelled_at', $this->dateTime()->null());
        $this->addColumn($table, 'cancelled_by', $this->integer()->null());
        $this->addColumn($table, 'cancelled_reason', $this->text()->null());

        $this->createIndex('idx-pm_strategy_indicator_year-indicator_id', $table, 'indicator_id');
        $this->createIndex('uq-pm_strategy_indicator_year-year', $table, ['indicator_id', 'fiscal_year'], true);
        $this->createIndex('idx-pm_strategy_indicator_year-year-status', $table, ['fiscal_year', 'status']);
        $this->addForeignKey('fk-pm_strategy_indicator_year-indicator', $table, 'indicator_id', '{{%pm_strategy_indicator}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-pm_strategy_indicator_year-copied_from', $table, 'copied_from_id', $table, 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $table = '{{%pm_strategy_indicator_year}}';
        $this->dropForeignKey('fk-pm_strategy_indicator_year-copied_from', $table);
        $this->dropForeignKey('fk-pm_strategy_indicator_year-indicator', $table);
        $this->dropIndex('idx-pm_strategy_indicator_year-year-status', $table);
        $this->dropIndex('uq-pm_strategy_indicator_year-year', $table);
        $this->dropIndex('idx-pm_strategy_indicator_year-indicator_id', $table);

        foreach (['cancelled_reason', 'cancelled_by', 'cancelled_at', 'copied_from_id', 'sort_order', 'weight', 'unit_override', 'name_override', 'status'] as $column) {
            $this->dropColumn($table, $column);
        }

        $this->renameTable($table, '{{%pm_strategy_indicator_value}}');
        $this->createIndex('idx-pm_strategy_indicator_value-indicator_id', '{{%pm_strategy_indicator_value}}', 'indicator_id');
        $this->createIndex('uq-pm_strategy_indicator_value-year', '{{%pm_strategy_indicator_value}}', ['indicator_id', 'fiscal_year'], true);
        $this->addForeignKey('fk-pm_strategy_indicator_value-indicator', '{{%pm_strategy_indicator_value}}', 'indicator_id', '{{%pm_strategy_indicator}}', 'id', 'CASCADE', 'CASCADE');
    }
}
