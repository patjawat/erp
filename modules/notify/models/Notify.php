<?php

namespace app\modules\notify\models;

use Yii;
use app\modules\hr\models\Employees;

/**
 * Model สำหรับตาราง notify (การแจ้งเตือน).
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string|null $message
 * @property string|null $ref_type
 * @property string|null $ref_id
 * @property int $recipient_emp_id
 * @property string|null $read_at
 * @property string $created_at
 * @property string|null $data_json
 *
 * @property Employees $recipientEmp
 */
class Notify extends \yii\db\ActiveRecord
{
    const TYPE_LEAVE_APPROVE = 'leave_approve';
    const TYPE_PURCHASE_APPROVE = 'purchase_approve';
    const TYPE_CHECKIN_APPROVE = 'checkin_approve';
    const TYPE_VEHICLE_APPROVE = 'vehicle_approve';
    const TYPE_STOCK_APPROVE = 'stock_approve';
    const TYPE_DEVELOPMENT_APPROVE = 'development_approve';
    const TYPE_ASSET_MOVE_APPROVE = 'asset_move_approve';
    /** ส่งสรุปผลประชุม/อบรม ให้ผู้ที่กำหนดอ่านรับทราบ */
    const TYPE_DEVELOPMENT_SUMMARY = 'development_summary';
    /** ผู้รับทราบกดรับทราบแล้ว แจ้งกลับผู้ส่งสรุป */
    const TYPE_DEVELOPMENT_SUMMARY_ACK = 'development_summary_ack';
    const TYPE_APPRECIATION_THANK = 'appreciation_thank';
    const TYPE_CHALLENGE_WINNER = 'challenge_winner';
    const TYPE_TASK_ASSIGNED = 'task_assigned';

    const REF_TYPE_TEST = 'test';

    /** @var string[] ข้อความแสดงตาม type */
    public static function typeLabels()
    {
        return [
            self::TYPE_LEAVE_APPROVE => 'ขออนุมัติลา',
            self::TYPE_PURCHASE_APPROVE => 'ขออนุมัติจัดซื้อจัดจ้าง',
            self::TYPE_CHECKIN_APPROVE => 'ขออนุมัติลงเวลาเข้างาน',
            self::TYPE_VEHICLE_APPROVE => 'ขออนุมัติใช้รถ',
            self::TYPE_STOCK_APPROVE => 'ขออนุมัติเบิกวัสดุ',
            self::TYPE_DEVELOPMENT_APPROVE => 'ขออนุมัติอบรม/ประชุม/ดูงาน',
            self::TYPE_ASSET_MOVE_APPROVE => 'ขออนุมัติเคลื่อนย้ายครุภัณฑ์',
            self::TYPE_DEVELOPMENT_SUMMARY => 'สรุปผลประชุม/อบรม รอรับทราบ',
            self::TYPE_DEVELOPMENT_SUMMARY_ACK => 'รับทราบสรุปผลประชุม/อบรมแล้ว',
            self::TYPE_APPRECIATION_THANK => 'มีคำขอบคุณส่งถึงคุณ',
            self::TYPE_CHALLENGE_WINNER => 'ทำ Challenge ครบเป้า',
            self::TYPE_TASK_ASSIGNED => 'มีงานมอบหมายถึงคุณ',
        ];
    }

    /** แจ้งเตือนเมื่อมีคนส่งคำขอบคุณให้ (เรียกจาก appreciation module หลัง save) */
    public static function createForAppreciation($appreciationModel)
    {
        if (!$appreciationModel || !$appreciationModel->to_emp_id) {
            return null;
        }
        $fromName = $appreciationModel->fromEmp ? $appreciationModel->fromEmp->fullname() : 'เพื่อนร่วมงาน';
        $title = $fromName . ' ส่งคำขอบคุณให้คุณ';
        $message = mb_substr(strip_tags($appreciationModel->message), 0, 200);
        return self::createFromApprove(
            self::TYPE_APPRECIATION_THANK,
            $title,
            (int) $appreciationModel->to_emp_id,
            'appreciation',
            (string) $appreciationModel->id,
            $message,
            ['from_emp_id' => $appreciationModel->from_emp_id]
        );
    }

    /**
     * แจ้งเตือนเมื่อมีงานมอบหมายถึงผู้ใช้ (เรียกจากโมดูล task)
     *
     * ไม่แจ้งเมื่อผู้ใช้มอบหมายงานให้ตัวเอง เพราะเพิ่งกดสร้างเองไปเมื่อครู่
     */
    public static function createForTaskAssigned($task, ?int $actorEmpId = null)
    {
        if (!$task || !$task->assignee_emp_id) {
            return null;
        }
        if ($actorEmpId !== null && (int) $actorEmpId === (int) $task->assignee_emp_id) {
            return null;
        }

        $due = $task->due_date ? ' · กำหนดส่ง ' . $task->due_date : '';
        return self::createFromApprove(
            self::TYPE_TASK_ASSIGNED,
            $task->title,
            (int) $task->assignee_emp_id,
            'task',
            (string) $task->id,
            trim(($task->detail ? mb_substr((string) $task->detail, 0, 200) : '') . $due) ?: null,
            ['task_id' => (int) $task->id, 'priority' => $task->priority]
        );
    }

    /** แจ้งเตือนเมื่อทำ Challenge ครบเป้า */
    public static function createForChallengeWinner($progressModel)
    {
        if (!$progressModel || !$progressModel->emp_id || !$progressModel->completed_at) {
            return null;
        }
        $challenge = $progressModel->challenge;
        $title = 'ยินดีด้วย! คุณทำ Challenge "' . ($challenge ? $challenge->name : '') . '" ครบเป้าแล้ว';
        return self::createFromApprove(
            self::TYPE_CHALLENGE_WINNER,
            $title,
            (int) $progressModel->emp_id,
            'appreciation_challenge_progress',
            (string) $progressModel->id,
            null,
            ['challenge_id' => $progressModel->challenge_id]
        );
    }

    public static function tableName()
    {
        return 'notify';
    }

    public function rules()
    {
        return [
            [['type', 'title', 'recipient_emp_id'], 'required'],
            [['message', 'data_json'], 'string'],
            [['recipient_emp_id'], 'integer'],
            [['read_at', 'created_at'], 'safe'],
            [['type', 'ref_type'], 'string', 'max' => 64],
            [['title'], 'string', 'max' => 255],
            [['ref_id'], 'string', 'max' => 64],
            [['recipient_emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employees::class, 'targetAttribute' => ['recipient_emp_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'ประเภท',
            'title' => 'หัวข้อ',
            'message' => 'ข้อความ',
            'ref_type' => 'อ้างอิง (ตาราง)',
            'ref_id' => 'รหัสอ้างอิง',
            'recipient_emp_id' => 'ผู้รับแจ้งเตือน',
            'read_at' => 'อ่านเมื่อ',
            'created_at' => 'สร้างเมื่อ',
            'data_json' => 'ข้อมูลเพิ่มเติม',
        ];
    }

    public function getRecipientEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'recipient_emp_id']);
    }

    /** สร้างการแจ้งเตือนจาก approve (ใช้จาก ApproveHelper หรือจุดที่สร้าง approve) */
    public static function createFromApprove($type, $title, $recipientEmpId, $refType = 'approve', $refId = null, $message = null, $dataJson = null)
    {
        try {
            $recipientEmpId = (int) $recipientEmpId;
            if ($recipientEmpId <= 0) {
                Yii::warning('Notify::createFromApprove skipped: invalid recipient_emp_id', __METHOD__);
                return null;
            }
            $m = new self();
            $m->type = $type;
            $m->title = $title;
            $m->recipient_emp_id = $recipientEmpId;
            $m->ref_type = $refType;
            $m->ref_id = $refId !== null ? (string) $refId : null;
            $m->message = $message;
            $m->data_json = $dataJson !== null ? (is_string($dataJson) ? $dataJson : json_encode($dataJson)) : null;
            $m->created_at = date('Y-m-d H:i:s');
            if (!$m->save(false)) {
                Yii::error('Notify::createFromApprove save failed: ' . json_encode($m->getErrors()), __METHOD__);
                return null;
            }
            return $m;
        } catch (\Throwable $e) {
            Yii::error('Notify::createFromApprove exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), __METHOD__);
            if (YII_DEBUG) {
                throw $e;
            }
            return null;
        }
    }

    /** มาร์กว่าอ่านแล้ว */
    public function markAsRead()
    {
        if ($this->read_at === null) {
            $this->read_at = date('Y-m-d H:i:s');
            return $this->save(false);
        }
        return true;
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }

    public function getTypeLabel()
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }
}
