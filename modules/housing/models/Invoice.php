<?php
declare(strict_types=1);
namespace app\modules\housing\models;
use yii\db\ActiveQuery;
final class Invoice extends HousingActiveRecord
{
    public const DRAFT='draft', CONFIRMED='confirmed', PARTIAL='partial', PAID='paid', OVERDUE='overdue', CANCELLED='cancelled';
    public static function tableName(): string { return '{{%housing_invoice}}'; }
    public function rules(): array { return [[['invoice_no','billing_period_id','occupancy_id','payer_emp_id','due_date'],'required'],[['billing_period_id','occupancy_id','payer_emp_id','confirmed_by','cancelled_by','created_by','updated_by'],'integer'],[['subtotal','discount','total_amount','paid_amount','balance_amount'],'number'],[['issued_at','due_date','confirmed_at','cancelled_at'],'safe'],[['note','cancel_reason'],'string'],[['status'],'in','range'=>[self::DRAFT,self::CONFIRMED,self::PARTIAL,self::PAID,self::OVERDUE,self::CANCELLED]]]; }
    public function getItems(): ActiveQuery { return $this->hasMany(InvoiceItem::class,['invoice_id'=>'id'])->orderBy(['sort_order'=>SORT_ASC,'id'=>SORT_ASC]); }
    public function getOccupancy(): ActiveQuery { return $this->hasOne(Occupancy::class,['id'=>'occupancy_id']); }
    public function getPeriod(): ActiveQuery { return $this->hasOne(BillingPeriod::class,['id'=>'billing_period_id']); }
}
