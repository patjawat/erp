<?php

namespace app\modules\leave\models;

use app\components\AppHelper;
use app\components\ApproveLevelResolver;
use app\components\CategoriseHelper;
use app\components\LineMsg;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;
use app\components\UserHelper;
use app\models\Categorise;
use app\models\Uploads;
use app\modules\approveV2\models\Approve;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\leave\components\LeaveApproveResolver;
use app\modules\leave\components\LeaveTelegramService;
use app\modules\leave\models\LeaveEntitlements;
use app\modules\leave\models\LeaveType;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "leave".
 *
 * @property int $id
 * @property string|null $leave_type_id ประเภทการขอลา
 * @property float|null $leave_time_type ประเภทการลา
 * @property float|null $total_days จำนวนวัน
 * @property string|null $data_json
 * @property string|null $date_start วันที่ลา
 * @property string|null $date_end ถึงวันที่
 * @property string|null $status สถานะ
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Leave extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public $step;
    public $date_filter;

    public $reason;
    public $data_start_th;
    public $data_end_th;
    public $q_department;
    public $export;
    public $sum_lt1;
    public $sum_lt2;
    public $sum_lt3;
    public $sum_lt4;
    public $work_shift_name;
    public $position_type_id;

    public static function tableName()
    {
        return 'leave';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            [['leave_type_id'], 'required'],
            [['leave_time_type', 'total_days'], 'number'],
            [['date_start_type', 'date_end_type', 'date_filter', 'balance', 'on_holidays', 'data_json', 'date_start', 'date_end', 'leave_start_type', 'leave_end_type', 'created_at', 'updated_at', 'deleted_at', 'emp_id', 'q', 'q_department', 'step', 'export', 'work_shift_name', 'position_type_id', 'ref'], 'safe'],
            [['thai_year', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['leave_type_id', 'status'], 'string', 'max' => 255],
            ['date_end', 'default', 'value' => function ($model) {
                return $model->date_start;
            }],
            ['date_end', 'validateDateRange'],
        ];
    }

    /**
     * Validates the date range
     * 
     * @param string $attribute the attribute being validated
     */
    public function validateDateRange($attribute)
    {
        if (strtotime($this->date_end) < strtotime($this->date_start)) {
            $this->addError($attribute, 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น');
        }
    }


    /**
     * ตรวจสอบว่าอีเวนต์อยู่ในวันที่กำหนดหรือไม่
     * 
     * @param string $date วันที่ในรูปแบบ Y-m-d
     * @return boolean
     */
    public function isInDate($date)
    {
        $eventStart = strtotime($this->date_start);
        $eventEnd = strtotime($this->date_end);
        $checkDate = strtotime($date);

        return ($checkDate >= $eventStart && $checkDate <= $eventEnd);
    }

    /**
     * คำนวณจำนวนวันของอีเวนต์
     * 
     * @return int จำนวนวัน
     */
    public function getDurationDays()
    {
        $eventStart = strtotime($this->date_start);
        $eventEnd = strtotime($this->date_end);

        return round(($eventEnd - $eventStart) / (60 * 60 * 24)) + 1;
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'leave_type_id' => 'ประเภทการขอลา',
            'leave_time_type' => 'ประเภทการลา',
            'total_days' => 'จำนวนวัน',
            'data_json' => 'Data Json',
            'date_start' => 'วันที่ลา',
            'date_end' => 'ถึงวันที่',
            'status' => 'สถานะ',
            'balance' => 'วันที่ลาพักผ่อนสะสม',
            'thai_year' => 'ปีงบประมาณ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
            'q' => 'คำค้นหา'
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public static function find()
    {
        return new LeaveQuery(get_called_class());
    }

    public function afterFind()
    {
        try {
            $this->reason = isset($this->data_json['reason']) ? $this->data_json['reason'] : '';
        } catch (\Throwable $th) {
        }
        $this->work_shift_name = isset($this->data_json['work_shift']) ? ($this->data_json['work_shift'] == 'normal' ? 'เวรเช้า' : 'เวร 8') : '';

        // $this->asset_name = isset($this->data_json['name']) ? $this->data_json['name'] : '-';

        parent::afterFind();
    }

    //ส่ง Msg เมื่อไม่ผ่านการอนุมัติ
    public function MsgReject()
    {
        $message = "บันทึกการ" . $this->leaveType->title . "ไม่ได้รับการอนุมัติ";
        $lineId = $this->employee->user->line_id;
        LineMsg::sendMsg($lineId, $message);
    }

    //ส่ง Msg เมื่อผ่านการอนุมัติ
    public function MsgApprove()
    {
        $message = "บันทึกการ" . $this->leaveType->title . "ได้รับการอนุมัติแล้ว";
        $lineId = $this->employee->user->line_id;
        LineMsg::sendMsg($lineId, $message);
    }

    /**
     * สร้างรายการอนุมัติตามระดับการอนุมัติตามโครงสร้างองค์กร (approve_level_setting)
     * ใช้ LeaveApproveResolver อ่านโครงสร้างองค์กรด้วย raw SQL
     * เฉพาะ modules/leave เท่านั้นที่ใช้วิธีนี้ — ระบบลาเดิม /me/leave/create ใช้การอนุมัติแบบเดิมใน modules/hr
     */
    public function createApprove()
    {
        // $rows = LeaveApproveResolver::buildApproveRows((int) $this->emp_id, (string) $this->id);
        $rows = ApproveLevelResolver::resolve('leave',$this->emp_id);

        // fallback: ถ้าไม่มี settings ให้ใช้ director เป็นผู้อนุมัติเสมอ
        if (empty($rows)) {
            $dirInfo = SiteHelper::viewDirector();
            $dirId   = !empty($dirInfo['id']) ? (int) $dirInfo['id'] : null;
            if ($dirId) {
                $rows = [[
                    'from_id'   => (string) $this->id,
                    'name'      => 'leave',
                    'level'     => 1,
                    'title'     => 'ผู้อำนวยการ',
                    'emp_id'    => $dirId,
                    'status'    => 'Pending',
                    'data_json' => ['label' => 'ผู้อำนวยการ'],
                ]];
            } else {
                return; // ไม่มีทั้ง settings และ director — ไม่สร้าง approve
            }
        }

        // ใช้ผู้อนุมัติตามที่ resolve จาก approve_level_setting (ไม่เขียนทับเป็น ผอ. ทุกระดับ)
        // ผอ. ตามตั้งค่าองค์กร (settings/company) — อิงจาก data_json[director_name] ที่เก็บเป็น id
        $isDirector = \app\components\SiteHelper::isDirectorFromSettings($this->emp_id);
        $approveDate = date('Y-m-d H:i:s');
        $firstPendingApprove = null;
        $first = true;

        foreach ($rows as $r) {
             $dataJson = ['label' => $r['label']];
            if ($r['approver_type'] === 'role') {
                // เก็บ role ไว้ใน data_json เพื่อใช้ตรวจสิทธิ์ทีหลัง
                $dataJson['role'] = 'role'; // placeholder
            }


            $a = new Approve();
            $a->from_id = (string) $this->id;
            $a->name    = 'leave';
            $a->level   = $r['level'];
            $a->title   = $r['label'];

            if ($isDirector) {
                $a->emp_id    = (int) $this->emp_id;
                $a->status    = 'Pass';
                $a->data_json = ['label' => $r['label'], 'approve_date' => $approveDate];
            } else {
                $a->emp_id    = $r['emp_id'];
                $a->status    =  $first ? 'Pending' : 'None';
                $a->data_json = $dataJson;
                if ($a->status === 'Pending' && $firstPendingApprove === null) {
                    $firstPendingApprove = $a;
                }
            }
            $a->save(false);
            $first = false;
        }

        if (!$isDirector && $firstPendingApprove && $firstPendingApprove->id) {
            try {
                $toUser = $firstPendingApprove->employee && $firstPendingApprove->employee->user
                    ? $firstPendingApprove->employee->user->line_id
                    : null;
                if ($toUser) {
                    LineMsg::sendLeave($firstPendingApprove->id, $toUser);
                }
                (new LeaveTelegramService())->notifyPendingApprove($firstPendingApprove);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    public function listApprove()
    {
        return Approve::find()->where(['name' => 'leave', 'from_id' => $this->id])->orderBy(['level' => SORT_ASC])->all();
    }


    // section Relationships
    public function getApproves()
    {
        return $this->hasMany(Approve::class, ['from_id' => 'id'])
            ->andOnCondition(['approve.name' => 'leave']);
    }

    public function getLeaveType()
    {
        return $this->hasOne(LeaveType::class, ['code' => 'leave_type_id'])->andOnCondition(['categorise.name' => 'leave_type']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getLeaveStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'leave_status']);
    }

    // สิทธวันลา
    public function Entitlements()
    {
        return LeaveEntitlements::find()->where(['emp_id' => $this->emp_id, 'thai_year' => $this->thai_year])->one();
    }


    public function Upload($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }

    /**
     * รายการเอกสารแนบ (ใบรับรองแพทย์ ฯลฯ) สำหรับแสดงใน view
     * @return \app\models\Uploads[]
     */
    public function getAttachmentList()
    {
        return Uploads::find()->where(['ref' => $this->ref, 'name' => 'leave_file'])->all();
    }

    //รายการเอกสารแนบ
    public function listClipFile()
    {
        $listFfiles = $this->getAttachmentList();
        $file = '<ul>';

        foreach ($listFfiles as $item) {
            $file .= '<li>' . Html::a($item->file_name, ['/leave/leave/show-file', 'id' => $item->id], ['target' => '_blank']) . '</li>';
        }

        $file .= '</ul>';
        return $file;
    }

    public function listHistory()
    {
        $emp = UserHelper::GetEmployee();
        return self::find()
            ->andWhere(['emp_id' => $this->emp_id, 'thai_year' => $this->thai_year, 'status' => 'Approve'])
            ->andWhere(['<', 'date_start', $this->date_start])
            ->orderBy(['date_start' => SORT_DESC])
            ->all();
    }

    /**
     * สถิติการลาในปีงบประมาณนี้ ตามประเภทการลาในใบลาฉบับนี้เท่านั้น: ลามาแล้ว (ก่อนใบนี้), ลาครั้งนี้ (จำนวนวันใบนี้), รวมเป็น
     * @return array[] แถวเดียว (หรือว่าง) มี code, title, last_days (ลามาแล้ว), on_days (ลาครั้งนี้), total_days (รวมเป็น)
     */
    public function getLeaveStatsInFiscalYear(): array
    {
        $dateStart = $this->date_start ? date('Y-m-d', strtotime($this->date_start)) : '';
        $leaveTypeId = (string) ($this->leave_type_id ?? '');
        if ($leaveTypeId === '') {
            return [];
        }
        $sql = "SELECT x1.code, x1.title, x1.thai_year,
                       x1.last_days,
                       x1.on_days,
                       (x1.last_days + x1.on_days) AS total_days
                FROM (
                    SELECT t.code, t.title, l.thai_year,
                           IFNULL(SUM(CASE WHEN l.leave_type_id = t.code AND l.status = 'Approve' AND l.date_start < :date_start THEN l.total_days ELSE 0 END), 0) AS last_days,
                           IFNULL(SUM(CASE WHEN l.leave_type_id = t.code AND l.id = :leave_id THEN l.total_days ELSE 0 END), 0) AS on_days
                    FROM `leave` l
                    LEFT JOIN categorise t ON t.code = l.leave_type_id AND t.name = 'leave_type'
                    WHERE l.emp_id = :emp_id AND l.thai_year = :thai_year AND l.leave_type_id = :leave_type_id
                    GROUP BY t.code, t.title, l.thai_year
                ) AS x1
                GROUP BY x1.code, x1.title, x1.thai_year
                ORDER BY x1.code";
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':date_start', $dateStart)
            ->bindValue(':thai_year', (int) $this->thai_year)
            ->bindValue(':emp_id', (int) $this->emp_id)
            ->bindValue(':leave_id', (int) $this->id)
            ->bindValue(':leave_type_id', $leaveTypeId)
            ->queryAll();
    }

    /**
     * วันลาครั้งสุดท้ายก่อนหน้าการลาครั้งนี้ (ประเภทเดียวกัน ปีงบประมาณเดียวกัน ผู้อนุมัติแล้ว)
     * @return Leave|null
     */
    public function getLastLeaveBeforeThis(): ?Leave
    {
        $dateStart = $this->date_start ? date('Y-m-d', strtotime($this->date_start)) : null;
        if ($dateStart === null) {
            return null;
        }
        return static::find()
            ->andWhere([
                'emp_id' => $this->emp_id,
                'thai_year' => $this->thai_year,
                'leave_type_id' => $this->leave_type_id,
                'status' => 'Approve',
            ])
            ->andWhere(['<', 'date_start', $dateStart])
            ->orderBy(['date_start' => SORT_DESC])
            ->one();
    }

    public function listLeaveType()
    {
        $me = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($me->gender == 'ชาย') {
            $list = LeaveType::find()->where(['name' => 'leave_type', 'active' => 1])->andWhere(['not in', 'code', ['LT2']])->all();
        } else {
            $list = LeaveType::find()->where(['name' => 'leave_type', 'active' => 1])->andWhere(['not in', 'code', ['LT5', 'LT7']])->all();
        }

        return ArrayHelper::map($list, 'code', 'title');
    }

    public function listStatus()
    {
        return ArrayHelper::map(
            Categorise::find()
                ->where(['name' => 'leave_status'])
                ->orderBy(new \yii\db\Expression('CAST(sort AS UNSIGNED) ASC'))
                ->all(),
            'code',
            'title'
        );
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
        $nextYear = [['thai_year' => ($year + 1)]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($nextYear, $isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }


     // แสดงข้อมูลวันลาที่มี่ผ่านมา
    public function LastDays()
    {
        // return $this->leave_type_id;
        // ลามาแล้ว
        $sumAll = self::find()
            ->where([
                'emp_id' => $this->emp_id,
                'thai_year' => $this->thai_year,
                'leave_type_id' => $this->leave_type_id,
                'status' => 'Approve'
            ])
            ->andwhere(['<', 'date_start', $this->date_start])
            ->sum('total_days');

        $data = self::find()
            ->where([
                'emp_id' => $this->emp_id,
                'thai_year' => $this->thai_year,
                'leave_type_id' => $this->leave_type_id,
                'status' => 'Approve'
            ])
            ->andwhere(['<', 'date_start', $this->date_start])
            ->one();

            return [
                'data' => $data ?? null,
                'sum_all' => $sumAll ?? 0
            ];
    }
    
    // สรุปการลารายบุคคล
    public function leaveEmpSummary()
    {
        $sql = "WITH summary AS (
                SELECT 
                    IFNULL(SUM(CASE WHEN l.leave_type_id = 'LT1' THEN l.total_days ELSE 0 END), 0) AS last_lt1,
                    IFNULL(SUM(CASE WHEN l.leave_type_id = 'LT3' THEN l.total_days ELSE 0 END), 0) AS last_lt3,
                    IFNULL(SUM(CASE WHEN l.leave_type_id = 'LT4' THEN l.total_days ELSE 0 END), 0) AS last_lt4
                FROM 
                    `leave` l
                WHERE 
                    l.emp_id = :emp_id 
                    AND thai_year = :thai_year 
                    AND l.status = 'Approve'
                    AND date_start < :date_start
                )
                SELECT * FROM summary";

        return Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':thai_year', $this->thai_year)
            ->bindValue(':emp_id', $this->emp_id)
            ->bindValue(':date_start', $this->date_start)
            ->queryOne();
    }

    public function getAvatar($empid, $msg = '')
    {
        try {
            $employee = Employees::find()->where(['id' => $this->emp_id])->one();
            if ($employee) {
                $msg = '<span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="bi bi-exclamation-circle-fill"></i> ' . ($this->leaveType->title ?? '') . '</span> เขียนเมื่อ' . $this->viewCreated();
                $msg = 'เขียน ' . $this->viewCreated();
                $avatarUrl = method_exists($employee, 'showAvatar') ? $employee->showAvatar() : '';
                return [
                    'avatar' => $employee->getAvatar(false, $msg),
                    'avatar_url' => $avatarUrl ?: null,
                    'department' => $employee->departmentName(),
                    'fullname' => $employee->fullname,
                    'position_name' => $employee->positionName(),
                ];
            } else {
                return [
                    'avatar' => '',
                    'avatar_url' => null,
                    'department' => '',
                    'fullname' => '',
                    'position_name' => '',
                ];
            }
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'avatar_url' => null,
                'department' => '',
                'fullname' => '',
                'position_name' => '',
            ];
        }
    }

    /**
     * คืน URL สำหรับเปิด PDF ใบลาโดยตรง (เมื่อมีเทมเพลต) — ใช้กับลิงก์พิมพ์ให้เปิดแท็บใหม่แล้วแสดง PDF เลย
     */
    public function getPreviewPdfUrl(): ?string
    {
        $baseDir = Yii::getAlias('@app') . '/modules/filemanager/fileupload/leave_templates';
        $ltCode = $this->leaveType->code ?? null;
        $safe = $ltCode ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) $ltCode) : '';
        $hasPerType = $safe !== '' && is_file($baseDir . '/' . $safe . '/template.pdf');
        $hasDefault = is_file($baseDir . '/default/template.pdf');
        if (!$hasPerType && !$hasDefault) {
            return null;
        }
        return Url::to(['/leave/leave/print', 'id' => $this->id]);
    }

    // แสดงสถานะในรูปแบบสี
    public function viewStatus()
    {
        try {
            switch ($this->status) {
                case 'Pending':
                    $color = 'warning';
                    $icon = '<i class="bi bi-hourglass-split"></i>';
                    break;
                case 'Checking1_pass':
                    $color = 'info';
                    $icon = '<i class="fa-solid fa-circle-check"></i>';
                    break;
                case 'Checking2_pass':
                    $color = 'info';
                    $icon = '<i class="fa-solid fa-circle-check"></i>';
                    break;
                case 'Approve':
                    $color = 'success';
                    $icon = '<i class="bi bi-check-circle-fill text-success"></i>';
                    break;
                case 'ReqCancel':
                    $color = 'warning';
                    $icon = '<i class="bi bi-exclamation-triangle text-danger"></i>';
                    break;
                case 'Cancel':
                    $color = 'secondary';
                    $icon = '<i class="bi bi-exclamation-circle-fill text-secondary"></i>';
                    break;
                case 'Checking':
                    $color = 'warning';
                    $icon = '<i class="fa-solid fa-magnifying-glass"></i>';
                    break;
                case 'Reject':
                    $color = 'danger';
                    $icon = '<i class="bi bi-exclamation-circle-fill text-danger"></i>';
                    break;

                default:
                    $color = '';
                    $icon = '';
            }

            return '<span class="badge bg-'.$color.' bg-opacity-10 text-'.$color.' border border-'.$color.'-subtle rounded-pill fw-medium px-2 py-1">' . $icon . ' ' . $this->leaveStatus->title . '</span>';
        } catch (\Throwable $th) {
            return null;
        }
    }

    //รายการ Approve ที่รอ HR ตรวจสอบ
    public function hrChecking()
    {
        $approve  = Approve::findOne(['name' => 'leave', 'from_id' => $this->id, 'emp_id' => null, 'status' => 'Pending']);
        if ($approve) {
            return $approve;
        } else {
            return false;
        }
    }

    // นับเวลาที่ผ่านมาแล้ว
    public function createdDays()
    {
        return AppHelper::timeDifference($this->created_at);
    }

    // แสดงวันที่สร้าง
    public function viewCreated()
    {
        // return Yii::$app->thaiFormatter->asDate($this->created_at, 'long');
        return Yii::$app->thaiDate->toThaiDate($this->created_at, true, false);
    }

    public function viewLeaveType()
    {
        return '<span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="bi bi-exclamation-circle-fill"></i> ' . $this->leaveType->title . '</span> เนื่องจาก ' . $this->reason;
    }

    /**
     * ค่าวันที่อนุมัติ (ดิบ) ของระดับที่กำหนด — ใช้สำหรับจัดรูปแบบใน PDF ตามที่ผู้ใช้เลือก
     * @param int $level ระดับผู้อนุมัติ 1–8
     * @return string|null วันที่ในรูปแบบ Y-m-d H:i:s หรือ null
     */
    public function getApproveDateRaw($level)
    {
        $check = Approve::find()
            ->where(['name' => 'leave', 'from_id' => $this->id, 'level' => $level])
            ->andWhere(['IS NOT', 'emp_id', null])
            ->one();
        return $check && isset($check->data_json['approve_date']) ? $check->data_json['approve_date'] : null;
    }

    // ผู้ตรวจสอบการลา
    public function checkerName($level)
    {
        $check = Approve::find()->where(['name' => 'leave', 'from_id' => $this->id, 'level' => $level])->andWhere(['IS NOT', 'emp_id', null])->one();
        if ($check) {
            return [
                'employee' => $check->employee,
                // Return plain fullname without surrounding parentheses for PDF display compatibility
                'fullname' => $check->employee ? $check->employee->fullname : 'ไม่ระบุชื่อ',
                'signature' => $check->employee ? $check->employee->signature() : 'ไม่ระบุตำแหน่ง',
                'position' => $check->employee ? $check->employee->positionName() : 'ไม่ระบุตำแหน่ง',
                'approve_date' => isset($check->data_json['approve_date']) ? 'วันที่ ' . Yii::$app->thaiFormatter->asDate($check->data_json['approve_date'], 'long') : '',
            ];
        } else {
            return [
                'fullname' => '',
                'signature' => '',
                'position' => '',
                'approve_date' => '....../........./......'
            ];
        }
    }

    /**
     * ภาพทีมผู้อนุมัติ (avatar stack)
     * ใช้ relation approves ที่โหลดไว้แล้วถ้ามี เพื่อหลีกเลี่ยง N+1 ในหน้ารายการ
     */
    public function stackChecker()
    {
        if ($this->isRelationPopulated('approves')) {
            $approves = array_filter($this->approves, function ($a) {
                return !in_array($a->status, ['None', 'Pending'], true);
            });
            usort($approves, function ($a, $b) {
                return (int) $b->level - (int) $a->level;
            });
            $list = array_values($approves);
            return $list === [] ? '' : static::renderStackChecker($list);
        }
        $approves = Approve::find()
            ->where(['from_id' => $this->id, 'name' => 'leave'])
            ->andWhere(['not in', 'status', ['None', 'Pending']])
            ->orderBy(['level' => SORT_DESC])
            ->with('employee')
            ->all();
        return $approves === [] ? '' : static::renderStackChecker($approves);
    }

    /**
     * Render stack checker from pre-loaded Approve models (avoids N+1 in lists).
     * ใช้รูปขนาดเล็ก (avatar-sm) — ถ้าโหลดช้าให้ตรวจที่โมเดล Employee::showAvatar() ว่าส่ง thumbnail หรือไม่
     * @param \app\modules\approveV2\models\Approve[] $approves
     * @return string
     */
    public static function renderStackChecker(array $approves)
    {
        $data = '<div class="avatar-stack">';
        foreach ($approves as $item) {
            try {
                $src = $item->employee ? $item->employee->showAvatar() : '';
                $data .= Html::img('@web/img/loading.gif', [
                    'class' => 'avatar-sm rounded-circle shadow lazyload' . ($item->status == 'Reject' ? ' border-danger' : ''),
                    'width' => '40',
                    'height' => '40',
                    'style' => 'object-fit:cover;',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $src,
                    ]
                ]);
            } catch (\Throwable $th) {
                // skip
            }
        }
        $data .= '</div>';
        return $data;
    }

    // มอบหมายงานให้

    public function leaveWorkSend()
    {
        try {
            $model = Employees::find()->where(['id' => $this->data_json['leave_work_send_id']])->one();
            return $model;
            // $msg = 'ผู้ปฏิบัติหน้าที่แทน';
            // return [
            //     'fullname' => $model->fullname,
            //     'position' => $model->positionName(),
            //     'avatar' => $model->getAvatar(false, $msg),

            // ];
        } catch (\Throwable $th) {
            // return [
            //     'fullname' => '',
            //     'position' => '',
            //     'avatar' => '',
            // ];
        }
    }

    public function statusProcess()
    {
        $total = Approve::find()->where(['name' => 'leave', 'from_id' => $this->id])->count();
        $accept = Approve::find()->where(['name' => 'leave', 'from_id' => $this->id, 'status' => 'Pass'])->count();
        if ($total > 0) {
            $percentageCompleted = ($accept / $total) * 100;
        } else {
            $percentageCompleted = 0;  // หรือค่าเริ่มต้นที่เหมาะสมเมื่อไม่มีข้อมูล
        }

        return number_format($percentageCompleted, 0);
    }

    public function CreateBy($msg = null)
    {
        $id = $this->created_by ? $this->created_by : null;
        return UserHelper::GetEmployee($this->created_by);
    }

    /**
     * ดึงลำดับผู้อนุมัติตามตั้งค่า approve_level_setting (system=leave)
     * ใช้ LeaveApproveResolver เพื่อให้ตรงกับ createApprove()
     * คืนค่าเป็น [ 'approve_1' => [...], 'approve_2' => [...], ... ]
     * เพื่อให้ actionSave เก็บ data_json ได้ถูกต้อง
     */
    public function Approve()
    {
        try {
            $empId = $this->emp_id ?: ($this->CreateBy() ? $this->CreateBy()->id : null);
            if (!$empId) {
                throw new \Exception('no emp_id');
            }

            $list = LeaveApproveResolver::getApproverInfoList((int) $empId);

            if (empty($list)) {
                // ไม่มี settings — fallback เป็น director
                $dirInfo = SiteHelper::viewDirector();
                $dirId   = !empty($dirInfo['id']) ? (int) $dirInfo['id'] : null;
                $dirEmp  = $dirId ? Employees::find()->where(['id' => $dirId])->one() : null;
                $list    = [[
                    'id'       => $dirId,
                    'fullname' => $dirEmp ? $dirEmp->fullname : ($dirInfo['fullname'] ?? ''),
                    'position' => $dirEmp ? $dirEmp->positionName() : '',
                    'avatar'   => $dirEmp ? $dirEmp->getAvatar(false) : '',
                    'title'    => 'ผู้อำนวยการ',
                ]];
            }

            // แปลงเป็น approve_1, approve_2, ... สำหรับ actionSave
            $result = [];
            foreach ($list as $i => $info) {
                $key = 'approve_' . ($i + 1);
                $empObj = null;
                if (!empty($info['id'])) {
                    $empObj = Employees::find()->where(['id' => $info['id']])->one();
                }
                $result[$key] = [
                    'id'       => $info['id'],
                    'avatar'   => $empObj ? $empObj->getAvatar(false) : '',
                    'fullname' => $empObj ? $empObj->fullname : ($info['fullname'] ?? ''),
                    'position' => $empObj ? $empObj->positionName() : ($info['position'] ?? ''),
                    'title'    => $info['title'],
                ];
            }
            return $result;

        } catch (\Throwable $th) {
            return [
                'approve_1' => ['id' => null, 'avatar' => '', 'fullname' => '', 'position' => '', 'title' => 'หัวหน้างาน'],
                'approve_2' => ['id' => null, 'avatar' => '', 'fullname' => '', 'position' => '', 'title' => 'หัวหน้ากลุ่มงาน'],
                'approve_3' => ['id' => null, 'avatar' => '', 'fullname' => '', 'position' => '', 'title' => 'ผู้อำนวยการ'],
            ];
        }
    }

    public function leader()
    {
        $userCreate = $this->CreateBy();
        $model = Organization::find()->where(['id' => $userCreate->department])->one();
        $employee = self::find()->where(['id' => $model->data_json['leader1']])->one();
    }

    public function Avatar($id)
    {
        try {
            $emp = Employees::find()->where(['id' => $id])->one();

            return [
                'avatar' => $emp->getAvatar(false),
                'department' => $emp->departmentName()
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'department' => ''
            ];
        }
    }


    public function levelStatusCount()
    {
        return  Approve::find()->where(['from_id' => $this->id, 'name' => 'leave'])->andWhere(['!=', 'status', 'None'])->count();
    }

    /**
     * มีผู้อนุมัติ/ไม่อนุมัติไปแล้วอย่างน้อยหนึ่งขั้นหรือยัง (ไม่นับแค่รอดำเนินการ Pending)
     * ถ้า false = ยังแก้ไขได้ (ทุกขั้นยังเป็น None หรือ Pending เช่น รอ หน.เห็นชอบ)
     */
    public function hasApprovalDecision()
    {
        return Approve::find()
            ->where(['from_id' => $this->id, 'name' => 'leave'])
            ->andWhere(['NOT IN', 'status', ['None', 'Pending']])
            ->exists();
    }
    public function showLeaveDate()
    {
        return ThaiDateHelper::formatThaiDateRange($this->date_start, $this->date_end, 'long', 'short');
    }
    public function showStatus()
    {
        $color = 'primary';
        $title = $this->viewStatus();

        return '<div class="d-flex justify-content-between">
                            <span class="text-muted mb-2 fs-13">' . $title . '
                                <span class="text-' . $color . '"></span>
                            </span>
                            <span class="text-muted mb-0 fs-13">' . $this->statusProcess() . '%</span>
                        </div>

                                                <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-' . $color . '" role="progressbar" aria-label="Progress" aria-valuenow="' . $this->statusProcess() . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $this->statusProcess() . '%;">
                            </div>
                        </div>';
    }

    public function listStatusSummary()
    {
        $query = Leave::find();
        $query
            ->select(['categorise.code', 'categorise.title', 'COUNT(leave.id) AS total'])
            ->leftJoin('categorise', 'categorise.code = leave.status')
            ->where(['leave.thai_year' => $this->thai_year])
            ->andFilterWhere(['leave.emp_id' => $this->emp_id])
            ->groupBy('leave.status')
            ->asArray();

        $data = $query->all();
        return $data;
    }

    // นับจำนวนประเภทวันลา
    public function sumLeaveType($type = null)
    {
        $sum = self::find()
            ->where(['thai_year' => $this->thai_year, 'emp_id' => $this->emp_id, 'leave_type_id' => $type, 'status' => 'Approve'])
            ->sum('total_days');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    public function sumLeaveStatus($status = null)
    {
        $sum = self::find()
            ->where(['thai_year' => $this->thai_year, 'emp_id' => $this->emp_id, 'status' => $status])
            ->sum('total_days');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    // นับจำนวนประเภท
    public function countLeaveType($type = null)
    {
        $sum = self::find()
            ->where(['status' => 'Approve'])
            ->andFilterWhere(['leave.thai_year' => $this->thai_year])
            ->andFilterWhere(['emp_id' => $this->emp_id])
            ->andFilterWhere(['leave_type_id' => $type])
            ->count('id');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    // นับจำนวนวันลาพักผ่อนคงเหลือ
    public function sumLeavePermission()
    {
        $lP = LeaveEntitlements::find()->where(['thai_year' => $this->thai_year, 'emp_id' => $this->emp_id])->one();
        $leaveDays = 0;
        if ($lP) {
            $total = $lP->days;
            $leaveDays = ($lP->days - $this->sumLeaveType('LT4'));
        } else {
            $total = 0;
        }

        return [
            'total' => $total,
            'sum' => $leaveDays,
            'use_days' => $this->sumLeaveType('LT4')
        ];
    }

    // นับจำนวนสถานะ
    public function countLeaveStatus($status = null)
    {
        // return $this->emp_id;
        $sum = self::find()
            ->andFilterWhere(['leave.thai_year' => $this->thai_year])
            ->andFilterWhere(['emp_id' => $this->emp_id])
            ->andFilterWhere(['status' => $status])
            ->count('id');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    // นับจำนวนวันลาที่ขออนุมัติ
    public function awaitLeave()
    {
        $sum = self::find()
            ->where(['IN', 'status', ['Pending', '']])
            ->andFilterWhere(['leave.thai_year' => $this->thai_year])
            ->andFilterWhere(['emp_id' => $this->emp_id])
            ->count('id');
        if (!$sum) {
            $sum = 0;
        }
        return $sum;
    }

    // คำนวนวันหยุดคงเหลือ
    public function leaveSumDays()
    {
        $emp = UserHelper::GetEmployee();
        $sql = "SELECT 
                        lp.leave_total_days,
                        COALESCE(SUM(l.total_days), 0) AS used_leave
                    FROM 
                        leave_permission lp
                    LEFT JOIN 
                        `leave` l 
                        ON l.emp_id = lp.emp_id 
                        AND l.thai_year = lp.thai_year 
                        AND l.leave_type_id = 'LT4' 
                        AND l.status = 'Approve'
                    WHERE 
                        lp.emp_id = :emp_id
                        AND lp.thai_year = :thai_year
                    GROUP BY 
                        lp.leave_total_days";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':thai_year', $this->thai_year)
            ->bindValue(':emp_id', $emp->id)
            ->queryOne();
        try {
            return [
                'total_days' => $query['leave_total_days'],
                'used_leave' => $query['used_leave']
            ];
        } catch (\Throwable $th) {
            return [
                'total_days' => 0,
                'used_leave' => 0
            ];
        }
    }

//แสรุปการลาในใบลาแต่ละครั้ง
    public function getLeaveSummary()
{
    $sql = "
        SELECT 
            l.date_start,
            l.date_end,
            l.status,
            l.leave_type_id,
            l.emp_id,
            l.thai_year,

            -- วันลาครั้งนี้
            l.total_days AS current_leave_days,

            -- รวมวันลาที่ลามาก่อนหน้า (ก่อนเริ่มลาครั้งนี้)
            (
                SELECT IFNULL(SUM(l2.total_days), 0)
                FROM `leave` l2
                WHERE l2.emp_id = l.emp_id
                AND l2.thai_year = l.thai_year
                AND l2.date_start < l.date_start
                AND l2.status = 'Approve'
            ) AS last_leave_days,

            -- รวมวันลาทั้งหมดจนถึงครั้งนี้
            (
                SELECT IFNULL(SUM(l3.total_days), 0)
                FROM `leave` l3
                WHERE l3.emp_id = l.emp_id
                AND l3.thai_year = l.thai_year
                AND l3.date_start <= l.date_start
                AND l3.status = 'Approve'
            ) AS total_leave_days

        FROM `leave` l
        WHERE l.id = :id
        AND l.emp_id = :emp_id
        AND l.thai_year = :thai_year
        AND l.status = :status
        LIMIT 1
    ";

    return Yii::$app->db->createCommand($sql, [
        ':id' => $this->id,
        ':emp_id' => $this->emp_id,
        ':thai_year' => $this->thai_year,
        ':status' => 'Approve',
    ])->queryOne();
}

    public function listEmployees()
    {
        $employes = Employees::find()->select(['id', new Expression("CONCAT(fname, ' ', lname) AS fullname")])->where(['status' => 1])->asArray()->all();
        return ArrayHelper::map($employes, 'id', function ($model) {
            return $model['fullname'];
        });
    }

//ประเภทของบุคลากร
       public function ListPositionType()
    {
        return CategoriseHelper::PositionType();
    }

}
