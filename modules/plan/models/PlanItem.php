<?php

namespace app\modules\plan\models;

use Yii;
use yii\db\Expression;

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
            [['data_json', 'plan_type_id'], 'safe'],
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
        ];
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
