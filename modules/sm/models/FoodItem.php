<?php

namespace app\modules\sm\models;

use Yii;

/**
 * This is the model class for table "categorise".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $category_id
 * @property string|null $code รหัส
 * @property string|null $emp_id พนักงาน
 * @property string $name ชนิดข้อมูล
 * @property string|null $title ชื่อ
 * @property int|null $qty จำนวน
 * @property string|null $description รายละเอียดเพิ่มเติม
 * @property string|null $data_json
 * @property string|null $unit_items
 * @property string|null $ma_items รายการบำรุงรักษา
 * @property int|null $active
 */
class FoodItem extends \yii\db\ActiveRecord
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
            [['name'], 'required'],
            [['qty', 'active'], 'integer'],
            [['data_json', 'unit_items', 'ma_items'], 'safe'],
            [['ref', 'category_id', 'code', 'emp_id', 'name', 'title', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'category_id' => 'Category ID',
            'code' => 'Code',
            'emp_id' => 'Emp ID',
            'name' => 'Name',
           'title' => 'ชื่อรายการ',
            'qty' => 'Qty',
            'description' => 'Description',
            'data_json' => 'Data Json',
            'unit_items' => 'Unit Items',
            'ma_items' => 'Ma Items',
            'active' => 'Active',
        ];
    }
}
