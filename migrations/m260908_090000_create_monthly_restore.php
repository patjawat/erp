<?php
use yii\db\Migration;

class m260908_090000_create_monthly_restore extends Migration
{
    public function safeUp()
    {
        $audit = ['ref'=>$this->string(255)->notNull(), 'created_at'=>$this->dateTime()->notNull(),
            'updated_at'=>$this->dateTime()->notNull(), 'created_by'=>$this->integer(), 'updated_by'=>$this->integer()];
        $this->createTable('{{%stock_monthly_restore}}', array_merge([
            'id'=>$this->primaryKey(), 'report_year'=>$this->integer()->notNull(), 'report_month'=>$this->integer()->notNull(),
            'file_sha256'=>$this->string(64)->notNull(), 'upload_id'=>$this->integer(),
            'status'=>$this->string(20)->notNull()->defaultValue('draft'),
            'source_json'=>$this->getDb()->driverName === 'mysql' ? 'LONGTEXT NOT NULL' : $this->text()->notNull(),
            'decisions_json'=>$this->getDb()->driverName === 'mysql' ? 'LONGTEXT' : $this->text(),
            'preview_hash'=>$this->string(64), 'reason'=>$this->text(),
        ], $audit));
        $this->createIndex('ux-monthly-restore-ref','{{%stock_monthly_restore}}','ref',true);
        $this->createTable('{{%stock_monthly_restore_event}}', array_merge([
            'id'=>$this->primaryKey(), 'restore_id'=>$this->integer()->notNull(), 'action'=>$this->string(20)->notNull(),
            'before_json'=>$this->getDb()->driverName === 'mysql' ? 'LONGTEXT NOT NULL' : $this->text()->notNull(),
            'after_json'=>$this->getDb()->driverName === 'mysql' ? 'LONGTEXT NOT NULL' : $this->text()->notNull(),
            'reason'=>$this->text()->notNull(),
        ], $audit));
        $this->createIndex('idx-monthly-restore-event','{{%stock_monthly_restore_event}}',['restore_id','id']);
        $this->createTable('{{%stock_monthly_period_lock}}', array_merge([
            'id'=>$this->primaryKey(), 'report_year'=>$this->integer()->notNull(), 'report_month'=>$this->integer()->notNull(),
            'warehouse_id'=>$this->integer()->notNull(), 'restore_id'=>$this->integer()->notNull(),
        ], $audit));
        $this->createIndex('ux-monthly-period-lock','{{%stock_monthly_period_lock}}',['report_year','report_month','warehouse_id'],true);
    }
    public function safeDown()
    {
        echo "Refusing to drop certified month history automatically. Archive it before an explicit rollback.\n";
        return false;
    }
}
