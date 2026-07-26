<?php
declare(strict_types=1);
use yii\db\Migration;

final class m260726_000007_create_housing_monthly_accounts extends Migration
{
    public function safeUp(): void
    {
        $o='CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        $this->addColumn('{{%housing_building}}','electric_account_no',$this->string(100)->null()->after('address'));
        $this->addColumn('{{%housing_unit}}','electric_account_no',$this->string(100)->null()->after('name'));
        $this->addColumn('{{%housing_billing_period}}','prepared_at',$this->dateTime()->null()->after('note'));
        $this->addColumn('{{%housing_billing_period}}','prepared_by',$this->integer()->null()->after('prepared_at'));
        $this->addColumn('{{%housing_billing_period}}','closed_by_name',$this->string(255)->null()->after('prepared_by'));
        $this->addColumn('{{%housing_billing_period}}','external_electric_total',$this->decimal(12,2)->notNull()->defaultValue(0)->after('closed_by_name'));
        $this->addColumn('{{%housing_billing_period}}','external_water_total',$this->decimal(12,2)->notNull()->defaultValue(0)->after('external_electric_total'));
        $this->createTable('{{%housing_monthly_account}}',[
            'id'=>$this->primaryKey(),'ref'=>$this->string(100)->notNull()->unique(),
            'billing_period_id'=>$this->integer()->notNull(),'building_id'=>$this->integer()->notNull(),
            'unit_id'=>$this->integer()->null(),'room_id'=>$this->integer()->null(),'occupancy_id'=>$this->integer()->null(),
            'payer_emp_id'=>$this->integer()->null(),'subject_key'=>$this->string(100)->notNull(),
            'building_name'=>$this->string(255)->notNull(),'unit_name'=>$this->string(150)->null(),
            'room_name'=>$this->string(150)->null(),'electric_account_no'=>$this->string(100)->null(),
            'payer_name'=>$this->string(255)->null(),'position_name'=>$this->string(255)->null(),
            'occupants_over_15'=>$this->integer()->notNull()->defaultValue(0),
            'total_amount'=>$this->decimal(12,2)->notNull()->defaultValue(0),
            'paid_amount'=>$this->decimal(12,2)->notNull()->defaultValue(0),
            'balance_amount'=>$this->decimal(12,2)->notNull()->defaultValue(0),
            'payment_status'=>$this->string(20)->notNull()->defaultValue('unpaid'),
            'status'=>$this->string(20)->notNull()->defaultValue('pending'),'note'=>$this->text(),
            'created_at'=>$this->dateTime(),'updated_at'=>$this->dateTime(),'created_by'=>$this->integer(),'updated_by'=>$this->integer(),
        ],$o);
        $this->createIndex('ux_housing_monthly_account_subject','{{%housing_monthly_account}}',['billing_period_id','subject_key'],true);
        $this->createIndex('ix_housing_monthly_account_location','{{%housing_monthly_account}}',['building_id','unit_id','room_id']);
        $this->addForeignKey('fk_housing_account_period','{{%housing_monthly_account}}','billing_period_id','{{%housing_billing_period}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_housing_account_building','{{%housing_monthly_account}}','building_id','{{%housing_building}}','id','RESTRICT','CASCADE');
        $this->addForeignKey('fk_housing_account_unit','{{%housing_monthly_account}}','unit_id','{{%housing_unit}}','id','SET NULL','CASCADE');
        $this->addForeignKey('fk_housing_account_room','{{%housing_monthly_account}}','room_id','{{%housing_room}}','id','SET NULL','CASCADE');
        $this->addForeignKey('fk_housing_account_occupancy','{{%housing_monthly_account}}','occupancy_id','{{%housing_occupancy}}','id','SET NULL','CASCADE');
        $this->createTable('{{%housing_monthly_account_item}}',[
            'id'=>$this->primaryKey(),'ref'=>$this->string(100)->notNull()->unique(),'account_id'=>$this->integer()->notNull(),'charge_type_id'=>$this->integer()->notNull(),
            'description'=>$this->string(255)->notNull(),'amount'=>$this->decimal(12,2)->notNull()->defaultValue(0),
            'note'=>$this->string(255)->null(),'sort_order'=>$this->integer()->notNull()->defaultValue(0),
            'created_at'=>$this->dateTime(),'updated_at'=>$this->dateTime(),'created_by'=>$this->integer(),'updated_by'=>$this->integer(),
        ],$o);
        $this->createIndex('ux_housing_account_item','{{%housing_monthly_account_item}}',['account_id','charge_type_id'],true);
        $this->addForeignKey('fk_housing_account_item_account','{{%housing_monthly_account_item}}','account_id','{{%housing_monthly_account}}','id','CASCADE','CASCADE');
        $this->addForeignKey('fk_housing_account_item_type','{{%housing_monthly_account_item}}','charge_type_id','{{%housing_charge_type}}','id','RESTRICT','CASCADE');
    }
    public function safeDown(): void
    {
        $this->dropTable('{{%housing_monthly_account_item}}');$this->dropTable('{{%housing_monthly_account}}');
        $this->dropColumn('{{%housing_billing_period}}','external_water_total');$this->dropColumn('{{%housing_billing_period}}','external_electric_total');
        $this->dropColumn('{{%housing_billing_period}}','closed_by_name');$this->dropColumn('{{%housing_billing_period}}','prepared_by');$this->dropColumn('{{%housing_billing_period}}','prepared_at');
        $this->dropColumn('{{%housing_unit}}','electric_account_no');$this->dropColumn('{{%housing_building}}','electric_account_no');
    }
}
