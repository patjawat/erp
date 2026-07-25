<?php
declare(strict_types=1);
namespace app\modules\housing\models;
final class BillingPeriod extends HousingActiveRecord
{
    public static function tableName(): string { return '{{%housing_billing_period}}'; }
    public function rules(): array { return [[['period_code','name','start_date','end_date','due_date'],'required'],[['period_code'],'string','max'=>20],[['name'],'string','max'=>150],[['start_date','end_date','due_date'],'date','format'=>'php:Y-m-d'],[['end_date'],'compare','compareAttribute'=>'start_date','operator'=>'>=','type'=>'date'],[['status'],'in','range'=>['open','closed','cancelled']],[['note'],'string'],[['closed_at'],'safe'],[['closed_by','created_by','updated_by'],'integer']]; }
}
