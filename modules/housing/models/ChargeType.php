<?php
declare(strict_types=1);
namespace app\modules\housing\models;
final class ChargeType extends HousingActiveRecord
{
    public static function tableName(): string { return '{{%housing_charge_type}}'; }
    public function rules(): array { return [[['code','name'],'required'],[['code'],'string','max'=>50],[['name'],'string','max'=>150],[['description'],'string'],[['status'],'in','range'=>['active','inactive']],[['sort_order','created_by','updated_by'],'integer']]; }
}
