<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use yii\db\ActiveQuery;

final class HousingRequest extends HousingActiveRecord
{
    public const TYPE_MOVE_IN = 'move_in';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_MOVE_OUT = 'move_out';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_STAFF_REVIEW = 'staff_review';
    public const STATUS_COMMITTEE_REVIEW = 'committee_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ALLOCATED = 'allocated';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName(): string
    {
        return '{{%housing_request}}';
    }

    public function rules(): array
    {
        return [
            [['request_no', 'request_type', 'emp_id'], 'required'],
            [['emp_id', 'current_occupancy_id', 'created_by', 'updated_by'], 'integer'],
            [['requested_at', 'submitted_at', 'completed_at'], 'safe'],
            [['reason', 'staff_note'], 'string'],
            [['request_no'], 'string', 'max' => 50],
            [['request_no'], 'unique'],
            [['request_type'], 'in', 'range' => array_keys(self::typeOptions())],
            [['preferred_building_type'], 'string', 'max' => 30],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'request_no' => 'เลขคำขอ',
            'request_type' => 'ประเภทคำขอ',
            'emp_id' => 'ผู้ยื่นคำขอ',
            'preferred_building_type' => 'ประเภทที่พักที่ต้องการ',
            'reason' => 'เหตุผล',
            'status' => 'สถานะ',
            'staff_note' => 'หมายเหตุเจ้าหน้าที่',
        ];
    }

    public function getLogs(): ActiveQuery
    {
        return $this->hasMany(RequestStatusLog::class, ['request_id' => 'id'])->orderBy('acted_at');
    }

    public function getDecision(): ActiveQuery
    {
        return $this->hasOne(CommitteeDecision::class, ['request_id' => 'id']);
    }

    public function getOccupancy(): ActiveQuery
    {
        return $this->hasOne(Occupancy::class, ['request_id' => 'id']);
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_MOVE_IN => 'ขอเข้าพัก',
            self::TYPE_TRANSFER => 'ขอย้ายห้อง',
            self::TYPE_MOVE_OUT => 'ขอย้ายออก',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_SUBMITTED => 'ส่งคำขอแล้ว',
            self::STATUS_STAFF_REVIEW => 'เจ้าหน้าที่ตรวจสอบ',
            self::STATUS_COMMITTEE_REVIEW => 'รอพิจารณา',
            self::STATUS_APPROVED => 'อนุมัติ',
            self::STATUS_REJECTED => 'ไม่อนุมัติ',
            self::STATUS_ALLOCATED => 'จัดสรรแล้ว',
            self::STATUS_ACTIVE => 'เข้าอยู่แล้ว',
            self::STATUS_COMPLETED => 'ดำเนินการเสร็จ',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }
}
