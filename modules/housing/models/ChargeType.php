<?php
declare(strict_types=1);
namespace app\modules\housing\models;
final class ChargeType extends HousingActiveRecord
{
    public const CATEGORY_UTILITY = 'utility';
    public const CATEGORY_EQUIPMENT = 'equipment';
    public const CATEGORY_MAINTENANCE = 'maintenance';
    public const CATEGORY_COMMON = 'common';
    public const CATEGORY_OTHER = 'other';

    public const METHOD_MANUAL = 'manual';
    public const METHOD_METER = 'meter';
    public const METHOD_FLAT_UNIT = 'flat_unit';
    public const METHOD_PER_PERSON = 'per_person';
    public const METHOD_EQUIPMENT = 'equipment';
    public const METHOD_MAINTENANCE = 'maintenance';

    public static function tableName(): string { return '{{%housing_charge_type}}'; }
    public function rules(): array { return [
        [['code','name','category','calculation_method'],'required'],
        [['code'],'string','max'=>50], [['code'],'unique'],
        [['name'],'string','max'=>150], [['unit_name'],'string','max'=>50],
        [['default_rate'],'number','min'=>0],
        [['description'],'string'],
        [['category'],'in','range'=>array_keys(self::categoryOptions())],
        [['calculation_method'],'in','range'=>array_keys(self::methodOptions())],
        [['status'],'in','range'=>['active','inactive']],
        [['sort_order','created_by','updated_by'],'integer'],
        [['sort_order'],'default','value'=>0],
        [['status'],'default','value'=>'active'],
    ]; }

    public function attributeLabels(): array { return [
        'code'=>'รหัสรายการ', 'name'=>'ชื่อค่าใช้จ่าย', 'category'=>'หมวดค่าใช้จ่าย',
        'calculation_method'=>'วิธีคำนวณ', 'unit_name'=>'หน่วยนับ',
        'default_rate'=>'อัตราตั้งต้น (ต่อหน่วย/ต่อคน)',
        'description'=>'รายละเอียด', 'status'=>'สถานะ', 'sort_order'=>'ลำดับแสดงผล',
    ]; }

    public static function categoryOptions(): array { return [
        self::CATEGORY_UTILITY=>'สาธารณูปโภค',
        self::CATEGORY_EQUIPMENT=>'ค่าเช่าอุปกรณ์',
        self::CATEGORY_MAINTENANCE=>'ค่าซ่อม',
        self::CATEGORY_COMMON=>'ค่าส่วนกลาง/เก็บขยะ',
        self::CATEGORY_OTHER=>'อื่น ๆ',
    ]; }

    public static function methodOptions(): array { return [
        self::METHOD_MANUAL=>'กรอกยอดโดยตรง',
        self::METHOD_METER=>'เลขมิเตอร์ × อัตรา',
        self::METHOD_FLAT_UNIT=>'อัตราคงที่ต่อห้อง',
        self::METHOD_PER_PERSON=>'อัตราต่อคน',
        self::METHOD_EQUIPMENT=>'จำนวนอุปกรณ์ × อัตรา',
        self::METHOD_MAINTENANCE=>'ดึงจากใบแจ้งซ่อม',
    ]; }
}
