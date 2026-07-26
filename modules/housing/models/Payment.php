<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveQuery;
final class Payment extends HousingActiveRecord
{
    public static function tableName(): string { return '{{%housing_payment}}'; }
    public function rules(): array { return [[['payment_no','payer_emp_id','paid_at','amount','payment_method'],'required'],[['payer_emp_id','received_by','cancelled_by','created_by','updated_by'],'integer'],[['amount'],'number','min'=>0.01],[['paid_at','cancelled_at'],'safe'],[['payment_method'],'in','range'=>['cash','transfer']],[['reference_no'],'string','max'=>150],[['note','cancel_reason'],'string'],[['status'],'in','range'=>['confirmed','cancelled']]]; }
    public function getAllocations(): ActiveQuery { return $this->hasMany(PaymentAllocation::class,['payment_id'=>'id']); }
    public function getReceipt(): ActiveQuery { return $this->hasOne(Receipt::class,['payment_id'=>'id']); }
}
