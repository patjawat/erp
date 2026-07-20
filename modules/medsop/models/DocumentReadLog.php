<?php

namespace app\modules\medsop\models;

use yii\db\ActiveRecord;

class DocumentReadLog extends ActiveRecord
{
    public const EVENT_ASSIGNED = 'ASSIGNED';
    public const EVENT_OPENED = 'OPENED';
    public const EVENT_ACKNOWLEDGED = 'ACKNOWLEDGED';
    public const EVENT_REMINDER_SENT = 'REMINDER_SENT';
    public const EVENT_ACKNOWLEDGEMENT_REVOKED = 'ACKNOWLEDGEMENT_REVOKED';

    public static function tableName()
    {
        return '{{%medsop_document_read_log}}';
    }

    public function rules()
    {
        return [
            [['assignment_id', 'document_id', 'revision_no', 'employee_id', 'event_type', 'event_at'], 'required'],
            [['assignment_id', 'document_id', 'revision_no', 'employee_id', 'user_id'], 'integer'],
            [['event_at'], 'safe'],
            [['event_type'], 'in', 'range' => self::eventOptions()],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 500],
            [['metadata_json'], 'string'],
        ];
    }

    public static function eventOptions(): array
    {
        return [
            self::EVENT_ASSIGNED,
            self::EVENT_OPENED,
            self::EVENT_ACKNOWLEDGED,
            self::EVENT_REMINDER_SENT,
            self::EVENT_ACKNOWLEDGEMENT_REVOKED,
        ];
    }

    public function getAssignment()
    {
        return $this->hasOne(DocumentAssignment::class, ['id' => 'assignment_id']);
    }
}
