<?php

namespace app\modules\plan\models;

use Yii;
use yii\db\Expression;
use app\modules\plan\components\PersonnelPlanTaxonomy;

/**
 * This is the model class for table "categorise".
 *
 * @property int $id
 * @property string|null $sort
 * @property string|null $ref
 * @property string|null $group_id กลุ่ม
 * @property string|null $category_id
 * @property string|null $code รหัส
 * @property string|null $emp_id พนักงาน
 * @property string $name ชนิดข้อมูล
 * @property string|null $title ชื่อ
 * @property int|null $qty
 * @property string|null $description รายละเอียดเพิ่มเติม
 * @property string|null $data_json
 * @property string|null $ma_items รายการบำรุงรักษา
 * @property int|null $active
 */
class PlanItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public $plan_type_id;

    /** @var int[] ประเภทบุคลากรที่ผูกกับรายการนี้ (เก็บใน data_json.employee_type_ids) */
    public $employee_type_ids = [];

    /** @var int 1 = จ่ายให้บุคลากรทุกประเภทในหน่วยงาน เช่น ฉ.11 (data_json.all_employee_types) */
    public $all_employee_types = 0;

    /** @var bool แถวนี้เคยมีการตั้งค่าประเภทบุคลากรไว้หรือไม่ (กันการล้างค่าของ record ที่บันทึกจากที่อื่น) */
    private $hadEmployeeMapping = false;

    public static function tableName()
    {
        return 'categorise';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort', 'ref', 'group_id', 'category_id', 'code', 'emp_id', 'title', 'qty', 'description', 'data_json', 'ma_items'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 1],
            [['name'], 'required'],
            [['title'], 'string'],
            [['qty', 'active'], 'integer'],
            [['data_json', 'plan_type_id', 'employee_type_ids', 'all_employee_types'], 'safe'],
            [['sort', 'ref', 'group_id', 'category_id', 'code', 'emp_id', 'name', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sort' => 'Sort',
            'ref' => 'Ref',
            'group_id' => 'Group ID',
            'category_id' => 'Category ID',
            'code' => 'Code',
            'emp_id' => 'Emp ID',
            'name' => 'Name',
            'title' => 'Title',
            'qty' => 'Qty',
            'description' => 'Description',
            'data_json' => 'Data Json',
            'ma_items' => 'Ma Items',
            'active' => 'Active',
            'employee_type_ids' => 'ประเภทบุคลากรที่ผูกกับรายการนี้',
            'all_employee_types' => 'ใช้กับบุคลากรทุกประเภทในหน่วยงาน',
        ];
    }

    /** อ่านการตั้งค่าประเภทบุคลากรจาก data_json ให้ฟอร์มแก้ไขได้ */
    public function afterFind()
    {
        parent::afterFind();

        $json = $this->dataArray();
        $this->employee_type_ids = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) ($json['employee_type_ids'] ?? [])
        ))));
        $this->all_employee_types = !empty($json['all_employee_types']) ? 1 : 0;
        $this->hadEmployeeMapping = array_key_exists('employee_type_ids', $json)
            || array_key_exists('all_employee_types', $json);
    }

    /** เขียนการตั้งค่าประเภทบุคลากรกลับเข้า data_json โดยไม่ทับคีย์อื่นที่มีอยู่เดิม */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $json = $this->dataArray();
        $allTypes = (int) $this->all_employee_types === 1;
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $this->employee_type_ids))));

        if (!$allTypes && !$ids && !$this->hadEmployeeMapping) {
            return true; // ไม่เคยตั้งค่าและไม่ได้ตั้งค่ามา -> ไม่ต้องแตะ data_json
        }

        if ($allTypes || $ids) {
            $json['all_employee_types'] = $allTypes;
            $json['employee_type_ids'] = $allTypes ? [] : $ids;
        } else {
            // ไม่ได้ตั้งค่า -> ไม่ต้องเก็บคีย์ไว้ (ระบบจะถอยไปใช้ค่าเดิมตามรหัสรายการ)
            unset($json['all_employee_types'], $json['employee_type_ids']);
        }

        $this->data_json = $json ?: null;
        PersonnelPlanTaxonomy::clearCache();

        return true;
    }

    /** data_json เป็น array เสมอ (คอลัมน์เป็น json แต่บางเครื่องอ่านกลับมาเป็นสตริง) */
    private function dataArray(): array
    {
        $raw = $this->data_json;
        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode((string) $raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true); // เผื่อค่าที่เคยถูก encode ซ้ำ
        }

        return is_array($decoded) ? $decoded : [];
    }
    public function getPlanCategory()
    {
        return $this->hasOne(PlanCategory::class, ['code' => 'category_id'])->andOnCondition(['name' => 'plan_category']);
    }


    /**
     * Generate code ใหม่ ตาม category_id
     * เช่น PRE -> PRE_01, PRE_02, ...
     */
    public static function generateNextCode($categoryId)
    {
        $last = self::find()
            ->where(['category_id' => $categoryId])
            ->orderBy(['id' => SORT_DESC]) // หรือ field ที่ใช้ sort ล่าสุด
            ->one();

        if ($last && preg_match('/^' . $categoryId . '_(\d+)$/', $last->code, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return $categoryId . '_' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    function nextItemCode()
{
    // ดึง code ล่าสุด
    $lastCode = self::find()
        ->where(['name' => 'plan_item'])
        ->select(new Expression("MAX(CAST(SUBSTRING(code, 2) AS UNSIGNED))"))
        ->scalar();

    // ถ้าไม่มี code ให้เริ่มที่ 1
    $nextNumber = $lastCode ? ((int)$lastCode + 1) : 1;

    // สร้าง code ใหม่
    $nextCode = 'P' . $nextNumber;

    return $nextCode;
}

}
