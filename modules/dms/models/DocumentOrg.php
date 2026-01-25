<?php

namespace app\modules\dms\models;

use Yii;

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
 * @property int|null $qty_min
 * @property int|null $qty_max
 */
class DocumentOrg extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public $q;
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
            [['qty_max'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['title'], 'string'],
            [['qty', 'active', 'qty_min', 'qty_max'], 'integer'],
            [['data_json', 'ma_items','q'], 'safe'],
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
            'qty_min' => 'Qty Min',
            'qty_max' => 'Qty Max',
        ];
    }

}
