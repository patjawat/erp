<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveRecord;
final class PaymentAllocation extends ActiveRecord
{
    public static function tableName(): string { return '{{%housing_payment_allocation}}'; }
    public function rules(): array { return [[['payment_id','invoice_id','amount'],'required'],[['payment_id','invoice_id','created_by'],'integer'],[['amount'],'number','min'=>0.01],[['created_at'],'safe']]; }
}
