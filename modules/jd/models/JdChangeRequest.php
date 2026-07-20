<?php

namespace app\modules\jd\models;

use yii\db\ActiveRecord;

class JdChangeRequest extends ActiveRecord
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_NEEDS_INFORMATION = 'needs_information';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RESOLVED = 'resolved';

    public static function tableName()
    {
        return '{{%jd_change_request}}';
    }

    public function rules()
    {
        return [
            [['jd_employee_id', 'emp_id', 'user_id', 'reason', 'submitted_at'], 'required'],
            [['jd_employee_id', 'emp_id', 'user_id', 'section_id', 'reviewed_by', 'new_jd_employee_id'], 'integer'],
            [['reason', 'proposed_change', 'resolution_note'], 'string'],
            [['submitted_at', 'reviewed_at'], 'safe'],
            [['section_code'], 'string', 'max' => 40],
            [['section_title'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 30],
            [['reason'], 'string', 'min' => 10],
            [['status'], 'default', 'value' => self::STATUS_SUBMITTED],
        ];
    }

    public static function openStatuses(): array
    {
        return [self::STATUS_SUBMITTED, self::STATUS_UNDER_REVIEW, self::STATUS_NEEDS_INFORMATION];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_SUBMITTED => 'ส่งคำขอแล้ว',
            self::STATUS_UNDER_REVIEW => 'กำลังพิจารณา',
            self::STATUS_NEEDS_INFORMATION => 'รอข้อมูลเพิ่มเติม',
            self::STATUS_ACCEPTED => 'รับคำขอ',
            self::STATUS_REJECTED => 'ไม่รับคำขอ',
            self::STATUS_CANCELLED => 'ยกเลิกคำขอ',
            self::STATUS_RESOLVED => 'ดำเนินการแล้ว',
        ];
    }
}
