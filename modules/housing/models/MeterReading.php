<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveQuery;
final class MeterReading extends HousingActiveRecord {
 public static function tableName():string{return '{{%housing_meter_reading}}';}
 public function rules():array{return [[['meter_id','reading_date','current_value'],'required'],[['meter_id','billing_period_id','confirmed_by','created_by','updated_by'],'integer'],[['previous_value','current_value','usage_value','unit_rate','minimum_charge','amount'],'number','min'=>0],[['reading_date','confirmed_at'],'safe'],[['note'],'string'],[['status'],'in','range'=>['draft','confirmed']],[['current_value'],'compare','compareAttribute'=>'previous_value','operator'=>'>=','type'=>'number']];}
 public function beforeSave($insert):bool{$this->usage_value=max(0,(float)$this->current_value-(float)$this->previous_value);$this->amount=max($this->usage_value*(float)$this->unit_rate,(float)$this->minimum_charge);return parent::beforeSave($insert);}
 public function getMeter():ActiveQuery{return $this->hasOne(Meter::class,['id'=>'meter_id']);}
 public function getBillingPeriod():ActiveQuery{return $this->hasOne(BillingPeriod::class,['id'=>'billing_period_id']);}
}
