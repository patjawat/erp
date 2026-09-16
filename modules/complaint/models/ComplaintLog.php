<?php

namespace app\modules\complaint\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveQuery;

/**
 * ประวัติ/audit trail ของเรื่องร้องเรียน (Process_Log)
 *
 * @property int         $id
 * @property int         $complaint_id
 * @property string      $action
 * @property string|null $from_status
 * @property string|null $to_status
 * @property string|null $note
 */
class ComplaintLog extends ComplaintActiveRecord
{
    public static function tableName(): string
    {
        return '{{%complaint_log}}';
    }

    public function rules(): array
    {
        return [
            [['complaint_id', 'action'], 'required'],
            [['complaint_id'], 'integer'],
            [['action', 'from_status', 'to_status'], 'string', 'max' => 32],
            [['note'], 'string', 'max' => 500],
        ];
    }

    public function getActor(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'created_by']);
    }
}
