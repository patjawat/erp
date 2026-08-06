<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260803_000003_create_pm_strategy_import_tables extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable('{{%pm_strategy_import_batch}}', [
            'id'=>$this->primaryKey(), 'plan_id'=>$this->integer()->notNull(), 'ref'=>$this->string(64)->notNull()->unique(),
            'original_name'=>$this->string(255)->notNull(), 'status'=>$this->string(20)->notNull()->defaultValue('staged'),
            'total_rows'=>$this->integer()->notNull()->defaultValue(0), 'valid_rows'=>$this->integer()->notNull()->defaultValue(0),
            'error_rows'=>$this->integer()->notNull()->defaultValue(0), 'summary_json'=>$this->json()->null(),
            'created_at'=>$this->dateTime()->null(), 'updated_at'=>$this->dateTime()->null(), 'created_by'=>$this->integer()->null(), 'updated_by'=>$this->integer()->null(),
        ], $options);
        $this->createIndex('idx-pm_strategy_import_batch-plan', '{{%pm_strategy_import_batch}}', ['plan_id','status']);
        $this->addForeignKey('fk-pm_strategy_import_batch-plan','{{%pm_strategy_import_batch}}','plan_id','{{%pm_strategy_plan}}','id','CASCADE','CASCADE');
        $this->createTable('{{%pm_strategy_import_row}}', [
            'id'=>$this->primaryKey(), 'batch_id'=>$this->integer()->notNull(), 'sheet_name'=>$this->string(100)->notNull(),
            'row_no'=>$this->integer()->notNull(), 'status'=>$this->string(20)->notNull()->defaultValue('valid'),
            'payload_json'=>$this->json()->notNull(), 'errors_json'=>$this->json()->null(),
        ], $options);
        $this->createIndex('idx-pm_strategy_import_row-batch','{{%pm_strategy_import_row}}',['batch_id','status']);
        $this->addForeignKey('fk-pm_strategy_import_row-batch','{{%pm_strategy_import_row}}','batch_id','{{%pm_strategy_import_batch}}','id','CASCADE','CASCADE');
    }
    public function safeDown(): void { $this->dropTable('{{%pm_strategy_import_row}}'); $this->dropTable('{{%pm_strategy_import_batch}}'); }
}
