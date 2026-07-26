<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveQuery;
final class Meter extends HousingActiveRecord {
 public static function tableName():string{return '{{%housing_meter}}';}
 public function rules():array{return [[['meter_type','name'],'required'],[['building_id','unit_id','room_id','created_by','updated_by'],'integer'],[['installed_at','retired_at'],'safe'],[['description'],'string'],[['meter_no'],'string','max'=>100],[['name'],'string','max'=>150],[['meter_type'],'in','range'=>['water','electric']],[['status'],'in','range'=>['active','inactive']]];}
 public static function typeOptions():array{return ['water'=>'น้ำ','electric'=>'ไฟฟ้า'];}
 public function getBuilding():ActiveQuery{return $this->hasOne(Building::class,['id'=>'building_id']);}
 public function getUnit():ActiveQuery{return $this->hasOne(Unit::class,['id'=>'unit_id']);}
 public function getRoom():ActiveQuery{return $this->hasOne(Room::class,['id'=>'room_id']);}
}
