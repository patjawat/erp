<?php
declare(strict_types=1);
use yii\db\Migration;
final class m260726_000004_create_housing_rates_and_meter_readings extends Migration {
 public function safeUp(): void {
  $o='CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
  $this->createTable('{{%housing_rate}}',['id'=>$this->primaryKey(),'ref'=>$this->string(100)->notNull()->unique(),'charge_type_id'=>$this->integer()->notNull(),'building_id'=>$this->integer(),'unit_id'=>$this->integer(),'calculation_type'=>$this->string(20)->notNull()->defaultValue('flat'),'rate'=>$this->decimal(12,2)->notNull(),'minimum_charge'=>$this->decimal(12,2)->notNull()->defaultValue(0),'effective_from'=>$this->date()->notNull(),'effective_to'=>$this->date(),'status'=>$this->string(20)->notNull()->defaultValue('active'),'note'=>$this->text(),'created_at'=>$this->dateTime(),'updated_at'=>$this->dateTime(),'created_by'=>$this->integer(),'updated_by'=>$this->integer()],$o);
  $this->createIndex('ix_housing_rate_lookup','{{%housing_rate}}',['charge_type_id','building_id','unit_id','effective_from']);
  $this->addForeignKey('fk_housing_rate_type','{{%housing_rate}}','charge_type_id','{{%housing_charge_type}}','id','RESTRICT','CASCADE');
  $this->addForeignKey('fk_housing_rate_building','{{%housing_rate}}','building_id','{{%housing_building}}','id','CASCADE','CASCADE');
  $this->addForeignKey('fk_housing_rate_unit','{{%housing_rate}}','unit_id','{{%housing_unit}}','id','CASCADE','CASCADE');
  $this->createTable('{{%housing_meter_reading}}',['id'=>$this->primaryKey(),'ref'=>$this->string(100)->notNull()->unique(),'meter_id'=>$this->integer()->notNull(),'billing_period_id'=>$this->integer(),'reading_date'=>$this->date()->notNull(),'previous_value'=>$this->decimal(12,2)->notNull()->defaultValue(0),'current_value'=>$this->decimal(12,2)->notNull(),'usage_value'=>$this->decimal(12,2)->notNull()->defaultValue(0),'unit_rate'=>$this->decimal(12,2)->notNull()->defaultValue(0),'minimum_charge'=>$this->decimal(12,2)->notNull()->defaultValue(0),'amount'=>$this->decimal(12,2)->notNull()->defaultValue(0),'status'=>$this->string(20)->notNull()->defaultValue('draft'),'confirmed_at'=>$this->dateTime(),'confirmed_by'=>$this->integer(),'note'=>$this->text(),'created_at'=>$this->dateTime(),'updated_at'=>$this->dateTime(),'created_by'=>$this->integer(),'updated_by'=>$this->integer()],$o);
  $this->createIndex('ux_housing_meter_period','{{%housing_meter_reading}}',['meter_id','billing_period_id'],true);
  $this->addForeignKey('fk_housing_reading_meter','{{%housing_meter_reading}}','meter_id','{{%housing_meter}}','id','CASCADE','CASCADE');
  $this->addForeignKey('fk_housing_reading_period','{{%housing_meter_reading}}','billing_period_id','{{%housing_billing_period}}','id','RESTRICT','CASCADE');
 }
 public function safeDown(): void {$this->dropTable('{{%housing_meter_reading}}');$this->dropTable('{{%housing_rate}}');}
}
