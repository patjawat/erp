<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveQuery;
final class Receipt extends HousingActiveRecord
{
    public static function tableName(): string { return '{{%housing_receipt}}'; }
    public function rules(): array { return [[['receipt_no','payment_id','issued_at','amount','verification_code'],'required'],[['payment_id','issued_by','cancelled_by','created_by','updated_by'],'integer'],[['amount'],'number'],[['issued_at','cancelled_at'],'safe'],[['receipt_no'],'string','max'=>50],[['verification_code'],'string','max'=>100],[['status'],'in','range'=>['issued','cancelled']],[['cancel_reason'],'string']]; }
    public function getPayment(): ActiveQuery { return $this->hasOne(Payment::class,['id'=>'payment_id']); }
}
