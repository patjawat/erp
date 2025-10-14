<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\hr\models\Employees;

/**
 * This is the model class for table "leave_entitlements".
 *
 * @property int $id
 * @property string|null $emp_id พนักงาน
 * @property string|null $position_type_id ประเภทตำแหน่ง
 * @property string|null $leave_type_id ประเภทการลา
 * @property int $month_of_service อายุงาน(เดือน)
 * @property int $year_of_service อายุงาน(ปี)
 * @property int $days วันที่ลาได้
 * @property string|null $data_json
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class LeaveEntitlements extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public $q_department;
    public static function tableName()
    {
        return 'leave_entitlements';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['thai_year', 'emp_id'], 'unique', 'targetAttribute' => ['thai_year', 'emp_id'],
            'message' => 'ปีงบประมาณ {value} มีข้อมูลพนักงานคนนี้อยู่แล้ว'],
            [['emp_id', 'month_of_service', 'year_of_service', 'days', 'thai_year'], 'required'],
            [['emp_id', 'month_of_service', 'year_of_service', 'thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at', 'q', 'q_department'], 'safe'],
            [['position_type_id', 'leave_type_id'], 'string', 'max' => 255],
        ];
    }

    // public function scenarios()
    // {
    //     $scenarios = parent::scenarios();

    //     // กรณีปกติ (ต้องกรอก thai_year)
    //     $scenarios['default'] = [
    //         'emp_id', 'month_of_service', 'year_of_service', 'days', 'thai_year',
    //         [
    //             'thai_year',
    //             'compare',
    //             'compareValue' => function () {
    //                 $yearBudget = LeaveEntitlements::find()
    //                     ->select('thai_year')
    //                     ->orderBy(['thai_year' => SORT_DESC])
    //                     ->scalar();
    //                 return $yearBudget;
    //             },
    //             'operator' => '<=',
    //             'message' => 'ยังไม่ได้กำหนดสิทธิการลาในปี ' . $this->thai_year
    //         ],
    //     ];

    //     // กรณีที่ไม่ต้องกรอก thai_year
    //     $scenarios['notcheck-thai-year'] = [
    //         'emp_id', 'month_of_service', 'year_of_service', 'days','thai_year'];

    //     return $scenarios;
    // }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'พนักงาน',
            'position_type_id' => 'ประเภทตำแหน่ง',
            'leave_type_id' => 'ประเภทการลา',
            'month_of_service' => 'อายุงาน(เดือน)',
            'year_of_service' => 'อายุงาน(ปี)',
            'days' => 'สิทธลาพักผ่อน(วัน)',
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

    public function getPolicies()
    {
        return $this->hasOne(LeavePolicies::class, ['position_type_id' => 'position_type_id']);
    }

    public function listEmployee()
    {
        $employees = Employees::find()->where(['status' => '1'])->all();
        return ArrayHelper::map($employees, 'id', function ($model) {
            return $model->fullname;
        });
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
        $nextYear = [['thai_year' => ($year + 1)]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($nextYear, $isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }

    public function getSummary()
    {
        $sql = "SELECT 
        (SELECT COALESCE(sum(total_days),0) as total_days FROM `leave` l WHERE l.emp_id = le.emp_id AND l.thai_year = le.thai_year AND l.status = 'Approve' AND l.leave_type_id = 'LT4') as leave_use,
        (le.days -(SELECT COALESCE(sum(total_days),0) as total_days FROM `leave` l WHERE l.emp_id = le.emp_id AND l.thai_year = le.thai_year AND l.status = 'Approve' AND l.leave_type_id = 'LT4')) as leave_total
                FROM leave_entitlements le
                WHERE le.emp_id = :emp_id AND le.thai_year = :thai_year";
        $query = Yii::$app->db->createCommand($sql)
            ->bindValue(':thai_year', $this->thai_year)
            ->bindValue(':emp_id', $this->emp_id)
            ->queryOne();
        if ($query['leave_total'] == null) {
            return [
                'leave_use' => 0,
                'leave_total' => 0,
            ];
        } else {
            return [
                'leave_use' =>  $query['leave_use'],
                'leave_total' =>  $query['leave_total'],
            ];
        }
    }

    // ยอดวันลาคงเหลือจากปีที่แล้ว
    public function lastBalanceDays()
    {
        $sql = "SELECT count(total_days) as balance FROM `leave` l
        WHERE l.emp_id = :emp_id AND l.thai_year = :thai_year";
        $query = Yii::$app->db->createCommand($sql)
            ->bindValue(':thai_year', AppHelper::YearBudget() - 1)
            ->bindValue(':emp_id', $this->emp_id)
            ->queryOne();
        if ($query['balance'] == null) {
            return 0;
        } else {
            return $query['balance'];
        }
    }

    // คำนวนยอกวันลาพักผ่ิน
    public function leaveSummaryDays()
    {
        $sql = "SELECT sum(total_days) as leave_use FROM `leave` l
        WHERE l.emp_id = :emp_id AND l.thai_year = :thai_year AND l.status = 'Approve' AND l.leave_type_id = 'LT4'";
        $query = Yii::$app->db->createCommand($sql)
            ->bindValue(':thai_year', $this->thai_year)
            ->bindValue(':emp_id', $this->emp_id)
            ->queryOne();
        $leaveUse = $query['leave_use'] == null ? 0 : $query['leave_use'];
        $leaveBalance = ($this->days - $leaveUse);
        $leaveNextDays = ($leaveBalance + 10);
        if ($leaveNextDays >= $this->calLeaveMaxDays()['leave_max_days'])
            $forwardDays = $this->calLeaveMaxDays()['leave_max_days'];
        else {
            $forwardDays = $leaveNextDays;
        }

        return [
            'leave_use' => $leaveUse ?? 0,
            'leave_balance' => $leaveBalance ?? 0,
            'leave_forward_days' => $forwardDays ?? 0,
            'leave_max_days' => $this->calLeaveMaxDays()['leave_max_days']
        ];
    }

    public function calLeaveMaxDays()
    {
        $sql = "SELECT max_days,accumulation FROM `leave_policies` lp
        WHERE lp.position_type_id = :position_type_id AND lp.year_of_service <= :year_of_service
        ORDER BY lp.year_of_service DESC
        LIMIT 1";
        $query = Yii::$app->db->createCommand($sql)
            ->bindValue(':position_type_id', $this->position_type_id)
            ->bindValue(':year_of_service', $this->year_of_service)
            ->queryOne();
        if (!$query || $query['max_days'] == null) {
            return [
                'leave_max_days' => 0,
                'accumulation' => 0
            ];
        } else {
            return [
                'leave_max_days' => $query['max_days'],
                'accumulation' => $query['accumulation']
            ];
        }
    }
}
