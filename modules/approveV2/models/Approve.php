<?php

namespace app\modules\approveV2\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\AssetDetail;
use app\modules\leave\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\leave\models\LeaveType;
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
 * @property array|string|null $data_json
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

    public function afterFind()
    {
        parent::afterFind();
        $this->normalizeDataJson();
    }

    public function beforeValidate()
    {
        $this->normalizeDataJson();
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->normalizeDataJson();
        if (is_array($this->data_json) && !$this->isNativeJsonColumn()) {
            $this->data_json = Json::encode($this->data_json);
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->normalizeDataJson();
    }

    private function normalizeDataJson(): void
    {
        if (is_array($this->data_json)) {
            return;
        }

        if (!is_string($this->data_json) || trim($this->data_json) === '') {
            $this->data_json = [];
            return;
        }

        try {
            $decoded = Json::decode($this->data_json, true);
            $this->data_json = is_array($decoded) ? $decoded : [];
        } catch (\InvalidArgumentException $e) {
            $this->data_json = [];
        }
    }

    private function isNativeJsonColumn(): bool
    {
        $column = static::getTableSchema()->getColumn('data_json');

        return $column !== null
            && ($column->type === 'json' || stripos((string) $column->dbType, 'json') !== false);
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

    public function getStockOrder()
    {
        return $this->hasOne(\app\modules\inventoryV2\models\StockOrder::class, ['id' => 'from_id'])
            ->andOnCondition(['approve.name' => 'requisition_v2']);
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

    /**
     * คืนค่า data_json เป็น array เสมอ รองรับทั้งคอลัมน์ JSON และ LONGTEXT
     */
    public function getDataJsonArray(): array
    {
        if (is_array($this->data_json)) {
            return $this->data_json;
        }

        if (is_string($this->data_json) && trim($this->data_json) !== '') {
            $decoded = json_decode($this->data_json, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * ชื่อการดำเนินการของขั้นอนุมัติ พร้อม fallback สำหรับข้อมูลเก่า
     */
    public function getApproveLabel(): string
    {
        $data = $this->getDataJsonArray();
        $label = trim((string) ($data['label'] ?? ''));
        if ($label !== '') {
            return $label;
        }

        return [
            1 => 'เห็นชอบ',
            2 => 'เห็นชอบ',
            3 => 'ผ่าน',
            4 => 'อนุมัติ',
        ][(int) $this->level] ?? '';
    }

    //แสดงรายบการ Approve
    public function viewApproveMsg()
    {
        try {
            // ตรวจสอบว่า $item มีคุณสมบัติ status และ data_json หรือไม่
            if (!isset($this->status)) {
                return '';
            }

            $label = $this->getApproveLabel();
            $message = '';

            switch ($this->status) {
                case 'None':
                    $message = 'รอ' . $label;
                    break;
                case 'Pending':
                    $message = 'รอ' . $label;
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
            case 'Pass':
                $color = 'success';
                $status = '';
                $icon = '<i class="fa-regular fa-circle-check me-1"></i>';
                break;
            default:
                // ค่าที่ไม่รู้จัก (ข้อมูลเก่าที่เพี้ยน) ต้องไม่แสดงเป็น "ผ่านแล้ว"
                $status = 'รอ';
                $color = 'warning';
                $icon = '<i class="fa-solid fa-hourglass-end me-1"></i>';
                break;
        }
        $label = Html::encode($this->getApproveLabel());

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
            // ต้องกรอง name ด้วย เพราะ from_id ใช้ร่วมกันข้ามระบบ (leave/purchase/vehicle/...)
            $maxLevel  = Approve::find()
                ->where(['from_id' => $this->from_id, 'name' => $this->name])
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
        $data = $this->getDataJsonArray();
        $approveDate = trim((string) ($data['approve_date'] ?? ''));
        if ($approveDate === '') {
            return null;
        }

        try {
            $time = explode(' ', $approveDate, 2)[1] ?? '';
            return trim(\Yii::$app->thaiFormatter->asDate($approveDate, 'medium') . ' ' . $time);
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
