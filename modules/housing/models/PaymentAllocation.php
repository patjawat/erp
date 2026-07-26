<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
final class PaymentAllocation extends ActiveRecord
{
    public static function tableName(): string { return '{{%housing_payment_allocation}}'; }
    public function rules(): array { return [[['payment_id','invoice_id','amount'],'required'],[['payment_id','invoice_id','created_by'],'integer'],[['amount'],'number','min'=>0.01],[['created_at'],'safe']]; }
    public function getPayment(): ActiveQuery { return $this->hasOne(Payment::class,['id'=>'payment_id']); }
    public function getInvoice(): ActiveQuery { return $this->hasOne(Invoice::class,['id'=>'invoice_id']); }
}
