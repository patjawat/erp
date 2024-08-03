<?php

namespace app\modules\pm\models;

use Yii;

/**
 * This is the model class for table "plan_detail".
 *
 * @property int $id
 * @property string|null $name ชื่อตารางเก็บข้อมูล
 * @property int|null $plan_id หัวข้อแผน
 * @property float|null $price ราคา
 * @property int|null $qty จำนวน
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class PlanDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_id', 'qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['price'], 'number'],
            [['data_json', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'plan_id' => 'Plan ID',
            'price' => 'Price',
            'qty' => 'Qty',
            'thai_year' => 'Thai Year',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }
}
