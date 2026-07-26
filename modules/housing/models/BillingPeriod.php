<?php
declare(strict_types=1);
namespace app\modules\housing\models;
final class BillingPeriod extends HousingActiveRecord
{
    public static function tableName(): string { return '{{%housing_billing_period}}'; }
    public function rules(): array { return [[['period_code','name','start_date','end_date','due_date'],'required'],[['period_code'],'string','max'=>20],[['period_code'],'unique'],[['name'],'string','max'=>150],[['closed_by_name'],'string','max'=>255],[['start_date','end_date','due_date'],'date','format'=>'php:Y-m-d'],[['end_date'],'compare','compareAttribute'=>'start_date','operator'=>'>=','type'=>'date'],[['status'],'in','range'=>['open','closed','cancelled']],[['status'],'default','value'=>'open'],[['external_electric_total','external_water_total'],'number','min'=>0],[['note'],'string'],[['prepared_at','closed_at'],'safe'],[['prepared_by','closed_by','created_by','updated_by'],'integer']]; }
    public function attributeLabels(): array { return ['period_code'=>'รหัสรอบเดือน','name'=>'ชื่อรอบเดือน','start_date'=>'วันที่เริ่ม','end_date'=>'วันที่สิ้นสุด','due_date'=>'กำหนดชำระ','external_electric_total'=>'ยอดบิลค่าไฟจากการไฟฟ้า','external_water_total'=>'ยอดบิลค่าน้ำ','status'=>'สถานะ','note'=>'หมายเหตุ']; }
}
