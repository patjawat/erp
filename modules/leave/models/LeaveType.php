<?php

namespace app\modules\leave\models;

use Yii;

/**
 * Model for table "categorise" (leave_type).
 */
class LeaveType extends \yii\db\ActiveRecord
{
    public $icon;

    public static function tableName()
    {
        return 'categorise';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['qty', 'active'], 'integer'],
            [['data_json', 'ma_items','icon'], 'safe'],
            [['ref', 'group_id', 'category_id', 'code', 'emp_id', 'name', 'title', 'description'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'group_id' => 'กลุ่ม',
            'category_id' => 'Category ID',
            'code' => 'รหัส',
            'emp_id' => 'พนักงาน',
            'name' => 'ชนิดข้อมูล',
            'title' => 'ชื่อ',
            'qty' => 'Qty',
            'description' => 'รายละเอียดเพิ่มเติม',
            'data_json' => 'Data Json',
            'ma_items' => 'รายการบำรุงรักษา',
            'active' => 'Active',
        ];
    }

    public function afterFind()
    {
        try {
            $this->icon = isset($this->data_json['icon']) ? $this->data_json['icon'] : null;
        } catch (\Throwable $th) {
        }
        parent::afterFind();
    }
}
