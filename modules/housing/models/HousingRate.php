<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveQuery;
final class HousingRate extends HousingActiveRecord {
 public static function tableName():string{return '{{%housing_rate}}';}
 public function rules():array{return [[['charge_type_id','rate','effective_from'],'required'],[['charge_type_id','building_id','unit_id','created_by','updated_by'],'integer'],[['rate','minimum_charge'],'number','min'=>0],[['effective_from','effective_to'],'safe'],[['note'],'string'],[['calculation_type'],'in','range'=>['flat','per_unit']],[['status'],'in','range'=>['active','inactive']]];}
 public function getChargeType():ActiveQuery{return $this->hasOne(ChargeType::class,['id'=>'charge_type_id']);}
 public function getBuilding():ActiveQuery{return $this->hasOne(Building::class,['id'=>'building_id']);}
 public function getUnit():ActiveQuery{return $this->hasOne(Unit::class,['id'=>'unit_id']);}
}
