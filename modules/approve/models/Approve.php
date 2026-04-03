<?php

namespace app\modules\approve\models;

use Yii;
use yii\helpers\Html;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetDetail;
use app\modules\leave\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\hr\models\LeaveType;
use app\modules\hr\models\Development;
use app\modules\purchase\models\Order;
use app\modules\booking\models\Vehicle;

use function PHPUnit\Framework\isEmpty;
use app\modules\inventory\models\StockEvent;

/**
 * This is the model class for table "approve".
 *
 * @property int $id
 * @property string|null $from_id รหัสการขออนุญาต
 * @property string|null $name ชื่อการอนุญาต
 * @property string|null $title ชื่อ
 * @property string|null $data_json
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
class Approve extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
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
            [['data_json', 'created_at', 'updated_at', 'deleted_at', 'q', 'thai_year', 'date_start', 'date_end', 'q_department', 'leave_type_id', 'approve_emp_id', 'q', 'date_filter', 'q_status', 'q_development_type_id'], 'safe'],
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
            'emp_id' => 'ผู้คตรวจสอลและอนุมัติ',
            'status' => 'สถานะ',
            'leave_type_id' => 'ประเภทการลา',
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


    // relation table
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

    

    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Establishes a relationship with the StockEvent model
     * based on the foreign key 'from_id'.
     *
     * @return \yii\db\ActiveQuery
     */

    /*******  d89483bd-f223-47e9-9c96-a9679ae1625c  *******/
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
        return $this->hasOne(AssetDetail::class, ['id' => 'from_id'])->andOnCondition(['asset_detail.name' => 'move']);
    }



    // แสดงปีงบประมานทั้งหมด
    public function ListThaiYear()
    {
        $model = Leave::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        $nextYear = [['thai_year' => ($year + 1)]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
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

    //แสดงรายบการ Approve
    public function viewApproveMsg()
    {
        try {
            // ตรวจสอบว่า $item มีคุณสมบัติ status และ data_json หรือไม่
            if (!isset($this->status)) {
                return '';
            }

            // กำหนดค่า default สำหรับ label โดยใช้ Null Coalescing Operator (??)
            // เพื่อป้องกันข้อผิดพลาดหาก data_json หรือ 'label' ไม่มี
            if ($this->level == 3) {
                $label = 'ตรวจสอบ';
            } else {
                $label = $this->data_json['label'] ?? '';
            }
            $message = '';

            switch ($this->status) {
                case 'None':
                    $message = 'รอ' . $label;
                    break;
                case 'Pending':
                    // เลือกระหว่าง $item->title หรือ $label ขึ้นอยู่กับ $item->level
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
                    // สถานะอื่น ๆ ที่ไม่ได้กำหนดไว้
                    return '';
            }

            // คืนค่าเป็นข้อความ HTML ที่ถูกจัดรูปแบบ
            if (!empty($message)) {
                return '<small class="text-muted d-block" style="font-size: 0.75rem;">' . $message . '</small>';
            }
        } catch (\Throwable $th) {
            // ควรมีการ Log ข้อผิดพลาดจริงที่นี่ ($th->getMessage())
            return ''; // คืนค่าว่าง หากเกิดข้อผิดพลาดในการเข้าถึง properties
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
                $status = 'รอ';
                $color = 'warning';
                $icon = '<i class="fa-solid fa-hourglass-end me-1"></i>';
                break;
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
        if ($this->level == 3) {
            $label = 'ตรวจสอบ';
        } else {
            $label = $this->data_json['label'] ?? null;
        }

        return '  <span class="badge bg-' . $color . ' bg-opacity-10 text-' . $color . ' border border-' . $color . '-subtle rounded-pill fw-medium px-2 py-1">' . $icon . $status . $label . '</span>';
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


    //  หา level สุดท้าย
    public function maxLevel()
    {
        try {
            $maxLevel  = Approve::find()
                ->where(['from_id' => $this->from_id])
                ->max('level') ?? 0; // คืนค่า 0 ถ้าไม่มีข้อมูล
            if ($maxLevel == $this->level) {
                return true;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function viewApproveDate()
    {
        try {
            $time = explode(' ', $this->data_json['approve_date'])[1];
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

            return [
                'avatar' => $employee->getAvatar(false, $msg),
                'photo' => $employee->ShowAvatar(),
                'department' => $employee->departmentName(),
                'fullname' => $employee->fullname,
                'position_name' => $employee->positionName()
                // 'product_type_name' => $this->data_json['product_type_name']
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'photo' => '',
                'department' => '',
                'fullname' => '',
                'position_name' => '',
                'product_type_name' => ''
            ];
        }
    }

    //  ภาพทีมผูตรวจสอบ
    public function stackChecker()
    {
        // try {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (self::find()->where(['from_id' => $this->from_id])->andWhere(['not in', 'status', ['None', 'Pending']])->all() as $key => $item) {
            try {
                $data .= Html::img('@web/img/placeholder-img.jpg', [
                    'class' => 'avatar-sm rounded-circle shadow lazyload blur-up' . ($item->status == 'Reject' ? ' border-danger' : null),
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $item->employee->showAvatar()
                    ]
                ]);
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
        $data .= '</div>';
        return $data;
    }

}
