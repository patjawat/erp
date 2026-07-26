<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveQuery;

final class MonthlyAccountItem extends HousingActiveRecord
{
    public static function tableName():string{return '{{%housing_monthly_account_item}}';}
    public function rules():array{return [[['account_id','charge_type_id','description'],'required'],[['account_id','charge_type_id','sort_order','created_by','updated_by'],'integer'],[['amount'],'number','min'=>0],[['description','note'],'string','max'=>255]];}
    public function getChargeType():ActiveQuery{return $this->hasOne(ChargeType::class,['id'=>'charge_type_id']);}
}
