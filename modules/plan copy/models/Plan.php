<?php

namespace app\modules\plan\models;

use Yii;

/**
 * This is the model class for table "plan".
 *
 * @property int $id
 * @property string $plan_type ประเภทแผน: material, personnel, expense
 * @property string $title ชื่อแผน
 * @property string|null $description รายละเอียด
 * @property string $start_date วันที่เริ่มแผน
 * @property string $end_date วันที่สิ้นสุดแผน
 * @property float|null $budget_total งบประมาณรวม
 * @property float|null $budget_used งบที่ใช้ไปแล้ว
 * @property string|null $status สถานะ: draft, submitted, approved, completed
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 *
 * @property PlanItem[] $planItems
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
            [['description', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['budget_used'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 'draft'],
            [['plan_type', 'title', 'start_date', 'end_date', 'emp_id'], 'required'],
            [['description'], 'string'],
            [['start_date', 'end_date', 'data_json', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['budget_total', 'budget_used'], 'number'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['plan_type'], 'string', 'max' => 50],
            [['title', 'emp_id'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_type' => 'Plan Type',
            'title' => 'Title',
            'description' => 'Description',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'budget_total' => 'Budget Total',
            'budget_used' => 'Budget Used',
            'status' => 'Status',
            'emp_id' => 'Emp ID',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    /**
     * Gets query for [[PlanItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlanItems()
    {
        return $this->hasMany(PlanItem::class, ['plan_id' => 'id']);
    }

}
