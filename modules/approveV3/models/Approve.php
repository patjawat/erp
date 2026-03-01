<?php

namespace app\modules\approveV3\models;

use Yii;
use yii\helpers\Html;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\AssetDetail;
use app\modules\hr\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\hr\models\LeaveType;
use app\modules\hr\models\Development;
use app\modules\purchase\models\Order;
use app\modules\booking\models\Vehicle;
use app\modules\inventory\models\StockEvent;

/**
 * Model V3 for table "approve".
 *
 * @property int $id
 * @property string|null $from_id รหัสการขออนุญาต
 * @property string|null $name ชื่อการอนุญาต
 * @property string|null $title ชื่อ
 * @property string|null $data_json
 * @property int|null $emp_id ผู้ตรวจสอบและอนุมัติ
 * @property string|null $status ความเห็น Y ผ่าน N ไม่ผ่าน
 * @property int|null $level ลำดับการอนุมัติ
 * @property string|null $comment ความคิดเห็น
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Approve extends \yii\db\ActiveRecord
{
    public $q;
    public $thai_year;
    public $date_start;
    public $date_end;
    public $q_department;
    public $leave_type_id;
    public $approve_emp_id;
    public $date_filter;
    public $q_status;
    public $q_development_type_id;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'approve';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'comment'], 'string'],
            [['data_json', 'created_at', 'updated_at', 'deleted_at', 'q', 'thai_year', 'date_start', 'date_end', 'q_department', 'leave_type_id', 'approve_emp_id', 'date_filter', 'q_status', 'q_development_type_id'], 'safe'],
            [['emp_id', 'level', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['from_id', 'name', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_id' => 'รหัสการขออนุญาต',
            'name' => 'ชื่อการอนุญาต',
            'title' => 'ชื่อ',
            'data_json' => 'Data Json',
            'emp_id' => 'ผู้ตรวจสอบและอนุมัติ',
            'status' => 'สถานะ',
            'leave_type_id' => 'ประเภทการลา',
            'level' => 'ลำดับการอนุมัติ',
            'comment' => 'ความคิดเห็น',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    // relations
    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getLeave()
    {
        return $this->hasOne(Leave::class, ['id' => 'from_id']);
    }

    public function getPurchase()
    {
        return $this->hasOne(Order::class, ['id' => 'from_id'])->andOnCondition(['orders.name' => 'order']);
    }

    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'from_id']);
    }

    public function getStock()
    {
        return $this->hasOne(StockEvent::class, ['id' => 'from_id'])->andOnCondition(['stock_events.name' => 'order']);
    }

    public function getDevelopment()
    {
        return $this->hasOne(Development::class, ['id' => 'from_id']);
    }

    public function getAssetMove()
    {
        return $this->hasOne(AssetDetail::class, ['id' => 'from_id'])->andOnCondition(['asset_detail.name' => 'asset-move']);
    }

    public function getCheckinRecord()
    {
        return $this->hasOne(\app\modules\attendance\models\CheckinRecord::class, ['id' => 'from_id']);
    }

    // helpers
    public function ListThaiYear()
    {
        $model = Leave::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();
        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];
        $nextYear = [['thai_year' => ($year + 1)]];
        $model = ArrayHelper::merge($isYear, $nextYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
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

    public function viewApproveMsg()
    {
        try {
            if (!isset($this->status)) {
                return '';
            }
            $label = ($this->level == 3) ? 'ตรวจสอบ' : ($this->data_json['label'] ?? '');
            $message = '';
            switch ($this->status) {
                case 'None':
                    $message = 'รอ' . $label;
                    break;
                case 'Pending':
                    $suffix = ($this->level == 3 && isset($this->title)) ? $this->title : $label;
                    $message = 'รอ' . $suffix;
                    break;
                case 'Pass':
                    $message = $label;
                    break;
                case 'Reject':
                    $message = 'ไม่' . $label;
                    break;
                default:
                    return '';
            }
            if (!empty($message)) {
                return '<small class="text-muted d-block" style="font-size: 0.75rem;">' . $message . '</small>';
            }
        } catch (\Throwable $th) {
            return '';
        }
        return '';
    }

    public function viewStatus()
    {
        return AppHelper::viewStatus($this->status);
    }

    public function viewApproveStatus()
    {
        switch ($this->status) {
            case 'None':
            case 'Pending':
                $status = 'รอ';
                $color = 'warning';
                $icon = '<i class="fa-solid fa-hourglass-end me-1"></i>';
                break;
            case 'Reject':
                $status = 'ไม่';
                $color = 'danger';
                $icon = '<i class="fa-regular fa-circle-xmark me-1"></i>';
                break;
            default:
                $color = 'success';
                $status = '';
                $icon = '<i class="fa-regular fa-circle-check me-1"></i>';
                break;
        }
        $label = ($this->level == 3) ? 'ตรวจสอบ' : ($this->data_json['label'] ?? null);
        return '  <span class="badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1">' . $icon . $status . $label . '</span>';
    }

    public function listLeaveType()
    {
        $me = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($me && $me->gender == 'ชาย') {
            $list = LeaveType::find()->where(['name' => 'leave_type', 'active' => 1])->andWhere(['not in', 'code', ['LT2']])->all();
        } else {
            $list = LeaveType::find()->where(['name' => 'leave_type', 'active' => 1])->andWhere(['not in', 'code', ['LT5', 'LT7']])->all();
        }
        return ArrayHelper::map($list, 'code', 'title');
    }

    public function maxLevel()
    {
        try {
            $maxLevel = static::find()->where(['from_id' => $this->from_id])->max('level') ?? 0;
            return $maxLevel == $this->level;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function viewApproveDate()
    {
        try {
            $time = explode(' ', $this->data_json['approve_date'])[1] ?? '';
            return \Yii::$app->thaiFormatter->asDate($this->data_json['approve_date'], 'medium') . ' ' . $time;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getAvatar($msg = null)
    {
        try {
            if (empty($this->emp_id) && $this->status == 'Pending') {
                $employee = UserHelper::GetEmployee();
            } else {
                $employee = Employees::find()->where(['id' => $this->emp_id])->one();
            }
            if (!$employee) {
                throw new \Exception('Employee not found');
            }
            return [
                'avatar' => $employee->getAvatar(false, $msg),
                'photo' => $employee->ShowAvatar(),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName(),
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'photo' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
            ];
        }
    }

    public function stackChecker()
    {
        $data = '<div class="avatar-stack">';
        foreach (static::find()->where(['from_id' => $this->from_id])->andWhere(['not in', 'status', ['None', 'Pending']])->all() as $item) {
            try {
                $data .= Html::img('@web/img/placeholder-img.jpg', [
                    'class' => 'avatar-sm rounded-circle shadow lazyload blur-up' . ($item->status == 'Reject' ? ' border-danger' : ''),
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $item->employee->showAvatar(),
                    ],
                ]);
            } catch (\Throwable $th) {
                // skip
            }
        }
        $data .= '</div>';
        return $data;
    }
}
