<?php

namespace app\modules\medsop\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveRecord;

class DocumentAssignment extends ActiveRecord
{
    public const STATUS_UNREAD = 'UNREAD';
    public const STATUS_READ = 'READ';
    public const STATUS_ACKNOWLEDGED = 'ACKNOWLEDGED';

    public static function tableName()
    {
        return '{{%medsop_document_assignment}}';
    }

    public function rules()
    {
        return [
            [['document_id', 'revision_no', 'employee_id', 'source_json', 'assigned_at'], 'required'],
            [['document_id', 'revision_no', 'employee_id', 'assigned_by', 'open_count', 'acknowledged_by'], 'integer'],
            [['required'], 'boolean'],
            [['source_json'], 'string'],
            [['due_date', 'assigned_at', 'first_opened_at', 'last_opened_at', 'acknowledged_at', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'in', 'range' => self::statusOptions()],
            [['acknowledged_ip'], 'string', 'max' => 45],
            [['acknowledged_user_agent'], 'string', 'max' => 500],
            [['document_id', 'revision_no', 'employee_id'], 'unique'],
        ];
    }

    public static function statusOptions(): array
    {
        return [self::STATUS_UNREAD, self::STATUS_READ, self::STATUS_ACKNOWLEDGED];
    }

    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'document_id']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'employee_id']);
    }

    public function getReadLogs()
    {
        return $this->hasMany(DocumentReadLog::class, ['assignment_id' => 'id'])->orderBy(['event_at' => SORT_DESC]);
    }

    public function getSources(): array
    {
        $sources = json_decode((string) $this->source_json, true);
        return is_array($sources) ? $sources : [];
    }

    public function beforeSave($insert)
    {
        $now = date('Y-m-d H:i:s');
        if ($insert) {
            $this->created_at = $this->created_at ?: $now;
        }
        $this->updated_at = $now;
        return parent::beforeSave($insert);
    }
}
