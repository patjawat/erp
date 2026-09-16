<?php

namespace app\modules\leave\models;

use app\modules\hr\models\Employees;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * ประวัติการแก้ไขใบลา
 *
 * @property int $id
 * @property int $leave_id
 * @property array|string|null $changes
 * @property string|null $status_snapshot
 * @property string|null $note
 * @property int|null $edited_by
 * @property string|null $edited_at
 */
class LeaveEditHistory extends ActiveRecord
{
    /** ป้ายชื่อฟิลด์สำหรับแสดงในตารางประวัติ */
    public const FIELD_LABELS = [
        'date_start'      => 'วันที่เริ่มลา',
        'date_end'        => 'ถึงวันที่',
        'date_start_type' => 'ช่วงวันเริ่ม',
        'date_end_type'   => 'ช่วงวันสิ้นสุด',
        'leave_type_id'   => 'ประเภทการลา',
        'total_days'      => 'จำนวนวัน',
        'status'          => 'สถานะ',
        'reason'          => 'เหตุผล',
        'thai_year'       => 'ปีงบประมาณ',
    ];

    public static function tableName()
    {
        return '{{%leave_edit_history}}';
    }

    public function rules()
    {
        return [
            [['leave_id'], 'required'],
            [['leave_id', 'edited_by'], 'integer'],
            [['changes', 'note'], 'safe'],
            [['status_snapshot'], 'string', 'max' => 255],
            [['edited_at'], 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if (is_array($this->changes)) {
            $this->changes = Json::encode($this->changes, JSON_UNESCAPED_UNICODE);
        }
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        if (is_string($this->changes) && trim($this->changes) !== '') {
            try {
                $this->changes = Json::decode($this->changes, true) ?: [];
            } catch (\Throwable $e) {
                $this->changes = [];
            }
        } elseif (!is_array($this->changes)) {
            $this->changes = [];
        }
    }

    public function getLeave()
    {
        return $this->hasOne(Leave::class, ['id' => 'leave_id']);
    }

    public function getEditor()
    {
        return $this->hasOne(Employees::class, ['id' => 'edited_by']);
    }

    /** ชื่อผู้แก้ไข */
    public function editorName(): string
    {
        return $this->editor ? $this->editor->fullname() : ('รหัส ' . (int) $this->edited_by);
    }

    /**
     * บันทึกประวัติการแก้ไขใบลา 1 รายการ
     *
     * @param int    $leaveId   รหัสใบลา
     * @param array  $changes   { field => ['old' => ..., 'new' => ...], ... }
     * @param string $status    สถานะใบลาขณะแก้ไข
     * @param int    $editorId  รหัสผู้แก้ไข (employee id)
     * @param string $note      หมายเหตุ
     */
    public static function record(int $leaveId, array $changes, ?string $status, ?int $editorId, string $note = ''): bool
    {
        if (empty($changes)) {
            return false;
        }
        $log = new self();
        $log->leave_id = $leaveId;
        $log->changes = $changes;
        $log->status_snapshot = $status;
        $log->note = $note !== '' ? $note : null;
        $log->edited_by = $editorId;
        $log->edited_at = new Expression('NOW()');
        return $log->save(false);
    }
}
