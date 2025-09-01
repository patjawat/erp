<?php

namespace app\modules\plan\models;

use Yii;
use app\models\Categorise;

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
class PlanTypeItem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
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
            [['data_json', 'ma_items'], 'safe'],
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

        public function getPlanType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'plan_type_id'])->andOnCondition(['name' => 'plan_type']);
    }
    
}
