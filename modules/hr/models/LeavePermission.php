<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\hr\models\Employees;

/**
 * This is the model class for table "leave_permission".
 *
 * @property int $id
 * @property string|null $emp_id พนักงาน
 * @property int|null $leave_days สิทธิวันลาที่ได้
 * @property int|null $leave_before_days จำนวนวันลาสะสม
 * @property int|null $leave_limit วันลาสะสมสูงสุด
 * @property int|null $leave_sum_days วันลาสะสม
 * @property int $year_of_service อายุงาน
 * @property string|null $position_type_id ตำแหน่ง
 * @property string|null $leave_type_id ประเภทการขอลา
 * @property string|null $data_json
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class LeavePermission extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_permission';
    }
    public $q;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['leave_days', 'leave_before_days', 'leave_limit', 'leave_sum_days', 'year_of_service', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['year_of_service'], 'required'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at','q'], 'safe'],
            [['emp_id', 'position_type_id', 'leave_type_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'พนักงาน',
            'leave_days' => 'สิทธิวันลาที่ได้',
            'leave_before_days' => 'จำนวนวันลาสะสม',
            'leave_limit' => 'วันลาสะสมสูงสุด',
            'leave_sum_days' => 'วันลาสะสม',
            'year_of_service' => 'อายุงาน',
            'position_type_id' => 'ตำแหน่ง',
            'leave_type_id' => 'ประเภทการขอลา',
            'data_json' => 'Data Json',
            'thai_year' => 'ปีงบประมาณ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
    // แสดงปีงบประมานทั้งหมด
    public function ListThaiYear()
    {
        $model = self::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($isYear,$model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }
}
