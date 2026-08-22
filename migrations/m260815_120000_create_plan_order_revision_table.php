<?php

use yii\db\Migration;

class m260815_120000_create_plan_order_revision_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%plan_order_revision}}', [
            'id' => $this->primaryKey(),
            'plan_order_id' => $this->integer()->notNull(),
            'cycle_no' => $this->integer()->notNull()->defaultValue(0),
            'version_type' => $this->string(30)->notNull(),
            'status' => $this->string(20)->notNull(),
            'order_price' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_1' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_2' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_3' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_4' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_5' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_6' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_7' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_8' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_9' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_10' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_11' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'month_12' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'plan_json' => $this->json()->notNull(),
            'items_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-plan-revision-cycle-version', '{{%plan_order_revision}}', ['plan_order_id', 'cycle_no', 'version_type'], true);
        $this->addForeignKey('fk-plan-revision-order', '{{%plan_order_revision}}', 'plan_order_id', '{{%plan_order}}', 'id', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-plan-revision-order', '{{%plan_order_revision}}');
        $this->dropTable('{{%plan_order_revision}}');
    }
}
