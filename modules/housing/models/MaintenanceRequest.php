<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use app\modules\hr\models\Employees;
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

    public $before_photos;
    public $after_photos;

    public static function tableName(): string
    {
        return '{{%housing_maintenance}}';
    }

    public function rules(): array
    {
        return [
            [['building_id', 'reported_at', 'reporter_name', 'title', 'description'], 'required'],
            [['building_id', 'assigned_employee_id', 'created_by', 'updated_by'], 'integer'],
            [['reported_at', 'repaired_at', 'closed_at'], 'safe'],
            [['description', 'resolution'], 'string'],
            [['expense_amount'], 'number', 'min' => 0],
            [['ticket_no'], 'string', 'max' => 50],
            [['location_note', 'reporter_name', 'title'], 'string', 'max' => 255],
            [['priority'], 'in', 'range' => array_keys(self::priorityOptions())],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['priority'], 'default', 'value' => self::PRIORITY_NORMAL],
            [['status'], 'default', 'value' => self::STATUS_NEW],
            [['expense_amount'], 'default', 'value' => 0],
            [['before_photos', 'after_photos'], 'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                'maxSize' => 10 * 1024 * 1024,
                'maxFiles' => 10,
                'skipOnEmpty' => true,
            ],
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
