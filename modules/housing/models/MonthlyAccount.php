<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveQuery;

final class MonthlyAccount extends HousingActiveRecord
{
    public const STATUS_PENDING='pending',STATUS_SAVED='saved';
    public const PAYMENT_UNPAID='unpaid',PAYMENT_PARTIAL='partial',PAYMENT_PAID='paid';
    public static function tableName():string{return '{{%housing_monthly_account}}';}
    public function rules():array{return [
        [['billing_period_id','building_id','subject_key','building_name'],'required'],
        [['billing_period_id','building_id','unit_id','room_id','occupancy_id','payer_emp_id','occupants_over_15','created_by','updated_by'],'integer'],
        [['total_amount','paid_amount','balance_amount'],'number','min'=>0],
        [['subject_key','electric_account_no'],'string','max'=>100],
        [['building_name','payer_name','position_name'],'string','max'=>255],
        [['unit_name','room_name'],'string','max'=>150],[['note'],'string'],
        [['status'],'in','range'=>[self::STATUS_PENDING,self::STATUS_SAVED]],
        [['payment_status'],'in','range'=>[self::PAYMENT_UNPAID,self::PAYMENT_PARTIAL,self::PAYMENT_PAID]],
    ];}
    public function getItems():ActiveQuery{return $this->hasMany(MonthlyAccountItem::class,['account_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC,'id'=>SORT_ASC]);}
    public function getPeriod():ActiveQuery{return $this->hasOne(BillingPeriod::class,['id'=>'billing_period_id']);}
    public function getBuilding():ActiveQuery{return $this->hasOne(Building::class,['id'=>'building_id']);}
    public function getUnit():ActiveQuery{return $this->hasOne(Unit::class,['id'=>'unit_id']);}
    public function getInvoice():ActiveQuery{return $this->hasOne(Invoice::class,['monthly_account_id'=>'id']);}
}
