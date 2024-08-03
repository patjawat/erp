<?php

namespace app\modules\pm\models;

use Yii;

/**
 * This is the model class for table "plan".
 *
 * @property int $id
 * @property string|null $name ชื่อตารางเก็บข้อมูล
 * @property int|null $plan_type ประเภทของแผน
 * @property string|null $budget_type ประเภทเงินที่ต้องใช้
 * @property string|null $plan_status สถานะ
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Plan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_type', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at'], 'safe'],
            [['name', 'budget_type', 'plan_status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'plan_type' => 'Plan Type',
            'budget_type' => 'Budget Type',
            'plan_status' => 'Plan Status',
            'thai_year' => 'Thai Year',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
