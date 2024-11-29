<?php

namespace app\modules\hr\models;

use Yii;
use app\models\Categorise;

/**
 * This is the model class for table "leave_step".
 *
 * @property int $id
 * @property int|null $leave_id
 * @property int|null $emp_id ผู้คตรวจสอลและอนุมัติ
 * @property string|null $status ความเห็น Y ผ่าน N ไม่ผ่าน
 * @property int|null $level ลำดับการอนุมติ
 * @property string|null $comment ความคิดเห็น
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class LeaveStep extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_step';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['leave_id', 'emp_id', 'level', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['comment'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'leave_id' => 'Leave ID',
            'emp_id' => 'ผู้คตรวจสอลและอนุมัติ',
            'status' => 'ความเห็น Y ผ่าน N ไม่ผ่าน',
            'level' => 'ลำดับการอนุมติ',
            'comment' => 'ความคิดเห็น',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }


    public function getLeaveStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'leave_status']);
    }

    public function getAvatar()
    {
        try {
            $employee = Employees::find()->where(['id' => $this->emp_id])->one();

            return [
                'avatar' => $employee->getAvatar(false,''),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
                // 'product_type_name' => $this->data_json['product_type_name']
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => ''
            ];
        }
    }
}
