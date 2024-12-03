<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;

/**
 * This is the model class for table "leave_role".
 *
 * @property int $id
 * @property string|null $data_json
 * @property int $emp_id
 * @property int $thai_year
 * @property int $work_year อายุงาน
 * @property string|null $position_type_id ตำแหน่ง
 * @property string|null $leave_type_id ประเภทการขอลา
 * @property int $max_leave สิทธิสะสมวัน
 * @property int $total_leave
 * @property int $used_leave ใช้ไป
 * @property int $point_leave วันลาสะสม
 */
class LeaveRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_role';
    }

    public $q;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json','q'], 'safe'],
            [['emp_id', 'thai_year', 'work_year', 'max_leave', 'total_leave', 'used_leave', 'point_leave'], 'required'],
            [['emp_id', 'thai_year', 'work_year', 'max_leave', 'total_leave', 'used_leave', 'point_leave','carry_over'], 'integer'],
            [['position_type_id', 'leave_type_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_json' => 'Data Json',
            'emp_id' => 'Emp ID',
            'thai_year' => 'Thai Year',
            'work_year' => 'อายุงาน',
            'position_type_id' => 'ตำแหน่ง',
            'leave_type_id' => 'ประเภทการขอลา',
            'max_leave' => 'สิทธิสะสมวัน',
            'total_leave' => 'Total Leave',
            'used_leave' => 'ใช้ไป',
            'point_leave' => 'วันลาสะสม',
            'carry_over' => 'วันลาสะสมที่ส่งต่อ',
        ];
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
