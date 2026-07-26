<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use app\modules\hr\models\Employees;
use app\modules\housing\validators\HousingImageDimensionsValidator;
use Yii;
use yii\db\ActiveQuery;

final class MaintenanceRequest extends HousingActiveRecord
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_UNABLE = 'unable';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_URGENT = 'urgent';
    public const PRIORITY_EMERGENCY = 'emergency';

    public const REPORTER_CARETAKER = 'caretaker';
    public const REPORTER_RESIDENT = 'resident';

    public const SCOPE_STRUCTURE = 'structure';
    public const SCOPE_COMMON = 'common';
    public const SCOPE_HOUSE = 'house';
    public const SCOPE_UNIT = 'unit';
    public const SCOPE_ROOM = 'room';

    public const ACK_NOT_REQUIRED = 'not_required';
    public const ACK_PENDING = 'pending';
    public const ACK_ACKNOWLEDGED = 'acknowledged';

    public $before_photos;
    public $after_photos;

    public static function tableName(): string
    {
        return '{{%housing_maintenance}}';
    }

    public function rules(): array
    {
        return [
            [['building_id', 'reported_at', 'reporter_type', 'problem_scope', 'reporter_name', 'title', 'description'], 'required'],
            [['building_id', 'occupancy_id', 'reporter_emp_id', 'assigned_employee_id', 'created_by', 'updated_by'], 'integer'],
            [['reported_at', 'repaired_at', 'closed_at'], 'safe'],
            [['description', 'resolution'], 'string'],
            [['expense_amount'], 'number', 'min' => 0],
            [['ticket_no'], 'string', 'max' => 50],
            [['location_note', 'reporter_name', 'title'], 'string', 'max' => 255],
            [['priority'], 'in', 'range' => array_keys(self::priorityOptions())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['priority'], 'default', 'value' => self::PRIORITY_NORMAL],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['reporter_type'], 'default', 'value' => self::REPORTER_CARETAKER],
            [['problem_scope'], 'default', 'value' => self::SCOPE_STRUCTURE],
            [['acknowledgement_status'], 'default', 'value' => self::ACK_NOT_REQUIRED],
            [['expense_amount'], 'default', 'value' => 0],
            [['reporter_type'], 'in', 'range' => array_keys(self::reporterTypeOptions())],
            [['problem_scope'], 'in', 'range' => array_keys(self::scopeOptions())],
            [['acknowledgement_status'], 'in', 'range' => array_keys(self::acknowledgementOptions())],
            [['occupancy_id'], 'required', 'when' => static fn(self $model): bool => $model->reporter_type === self::REPORTER_RESIDENT],
            [['occupancy_id'], 'validateOccupancy'],
            [['before_photos', 'after_photos'], 'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'maxSize' => 10 * 1024 * 1024,
                'maxFiles' => 10,
                'skipOnEmpty' => true,
            ],
            [['before_photos', 'after_photos'], HousingImageDimensionsValidator::class],
            [['assigned_employee_id'], 'exist',
                'targetClass' => Employees::class,
                'targetAttribute' => ['assigned_employee_id' => 'id'],
                'skipOnEmpty' => true,
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'ticket_no' => 'เลขที่แจ้งซ่อม',
            'building_id' => 'บ้านพัก/แฟลต',
            'location_note' => 'จุดที่พบปัญหา',
            'reported_at' => 'วันที่และเวลาแจ้ง',
            'reporter_name' => 'ผู้แจ้ง',
            'reporter_type' => 'ประเภทผู้แจ้ง',
            'problem_scope' => 'ขอบเขตปัญหา',
            'occupancy_id' => 'ผู้พักอาศัย/ที่พัก',
            'acknowledgement_status' => 'การรับทราบของผู้พักอาศัย',
            'title' => 'หัวข้อปัญหา',
            'description' => 'รายละเอียดปัญหา',
            'priority' => 'ความเร่งด่วน',
            'assigned_employee_id' => 'ผู้รับผิดชอบ',
            'status' => 'สถานะ',
            'resolution' => 'วิธีแก้ไขหรือผลการซ่อม',
            'expense_amount' => 'ค่าใช้จ่ายรวม',
            'repaired_at' => 'วันที่ดำเนินการ',
            'before_photos' => 'รูปภาพก่อนซ่อม',
            'after_photos' => 'รูปภาพหลังซ่อม',
        ];
    }

    public function beforeSave($insert): bool
    {
        if ($insert && empty($this->ticket_no)) {
            if (empty($this->ref)) {
                $this->ref = substr(Yii::$app->security->generateRandomString(), 10);
            }
            $this->ticket_no = 'RM-' . date('Ymd-His') . '-' . strtoupper(substr($this->ref, -5));
        }
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_UNABLE, self::STATUS_CANCELLED], true)
            && empty($this->closed_at)) {
            $this->closed_at = date('Y-m-d H:i:s');
        }
        if (!in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_UNABLE, self::STATUS_CANCELLED], true)) {
            $this->closed_at = null;
        }
        return parent::beforeSave($insert);
    }

    public function getBuilding(): ActiveQuery
    {
        return $this->hasOne(Building::class, ['id' => 'building_id']);
    }

    public function getAssignedEmployee(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'assigned_employee_id']);
    }

    public function getOccupancy(): ActiveQuery
    {
        return $this->hasOne(Occupancy::class, ['id' => 'occupancy_id']);
    }

    public function validateOccupancy(string $attribute): void
    {
        if (!$this->$attribute) {
            return;
        }
        $occupancy = Occupancy::find()->with('unit')->where([
            'id' => $this->$attribute,
            'status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE],
        ])->one();
        if ($occupancy === null || (int) $occupancy->unit?->building_id !== (int) $this->building_id) {
            $this->addError($attribute, 'ผู้พักอาศัยที่เลือกไม่ได้อยู่ในบ้านพักหรือแฟลตรายการนี้');
        }
    }

    public static function reporterTypeOptions(): array
    {
        return [
            self::REPORTER_CARETAKER => 'ผู้ดูแลบ้านพักแจ้ง',
            self::REPORTER_RESIDENT => 'ผู้พักอาศัยแจ้ง',
        ];
    }

    public static function scopeOptions(): array
    {
        return [
            self::SCOPE_STRUCTURE => 'โครงสร้าง/ภายนอกอาคาร',
            self::SCOPE_COMMON => 'พื้นที่ส่วนกลาง',
            self::SCOPE_HOUSE => 'ภายในบ้านพัก',
            self::SCOPE_UNIT => 'ภายในยูนิต',
            self::SCOPE_ROOM => 'ภายในห้องพัก',
        ];
    }

    public static function acknowledgementOptions(): array
    {
        return [
            self::ACK_NOT_REQUIRED => 'ไม่ต้องรับทราบ',
            self::ACK_PENDING => 'รอผู้พักอาศัยรับทราบ',
            self::ACK_ACKNOWLEDGED => 'ผู้พักอาศัยรับทราบแล้ว',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_NEW => 'แจ้งใหม่',
            self::STATUS_IN_PROGRESS => 'กำลังดำเนินการ',
            self::STATUS_COMPLETED => 'ซ่อมแล้ว',
            self::STATUS_UNABLE => 'ไม่สามารถดำเนินการ',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_NORMAL => 'ปกติ',
            self::PRIORITY_URGENT => 'เร่งด่วน',
            self::PRIORITY_EMERGENCY => 'ฉุกเฉิน',
        ];
    }
}
