<?php

namespace app\modules\hr\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\components\LineMsg;
use app\components\ThaiDateHelper;
use app\modules\approve\models\Approve;
use app\modules\hr\models\Development;
use app\modules\hr\models\Employees;
use app\modules\notify\models\Notify;

/**
 * สรุปผลการประชุม/อบรม/ดูงาน ของใบขออนุญาตไปราชการหนึ่งใบ
 *
 * ผู้รับทราบไม่ได้เก็บในตารางนี้ แต่ใช้ตาราง approve กลางของระบบ
 * (name = self::APPROVE_NAME, from_id = development_id) เหมือนโมดูลอื่น
 *
 * @property int $id
 * @property int $development_id ใบขออนุญาตไปราชการที่ผูกอยู่
 * @property string $status draft | submitted | acknowledged
 * @property string|null $content สรุปเนื้อหา/สาระสำคัญที่ได้รับ
 * @property string|null $benefit การนำไปใช้ประโยชน์ต่อหน่วยงาน
 * @property string|null $suggestion ข้อเสนอแนะต่อหน่วยงาน
 * @property string|null $ref token ของ filemanager สำหรับไฟล์แนบ
 * @property array|null $data_json
 * @property string|null $submitted_at
 * @property int|null $submitted_by
 */
class DevelopmentSummary extends \yii\db\ActiveRecord
{
    /** ยังไม่ส่งให้ใครรับทราบ — ทะเบียนแสดงเป็นสีแดง */
    const STATUS_DRAFT = 'draft';
    /** ส่งแล้ว ยังมีผู้รับทราบค้าง — ทะเบียนแสดงเป็นสีเหลือง */
    const STATUS_SUBMITTED = 'submitted';
    /** ผู้รับทราบครบทุกคน — ทะเบียนแสดงเป็นสีเขียว */
    const STATUS_ACKNOWLEDGED = 'acknowledged';

    /** ค่า approve.name ที่ใช้เก็บรายชื่อผู้รับทราบสรุปผล */
    const APPROVE_NAME = 'development_summary';

    /** ตอนกดส่งให้รับทราบ บังคับว่าต้องมีเนื้อหาสรุป (บันทึกร่างไม่บังคับ) */
    const SCENARIO_SUBMIT = 'submit';

    public static function tableName()
    {
        return 'development_summary';
    }

    public function rules()
    {
        return [
            [['development_id'], 'required'],
            [['development_id', 'submitted_by', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['content', 'benefit', 'suggestion'], 'string'],
            [['content'], 'required', 'on' => self::SCENARIO_SUBMIT],
            [['data_json', 'submitted_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['status'], 'string', 'max' => 20],
            [['ref'], 'string', 'max' => 255],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SUBMIT] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'development_id' => 'ใบขออนุญาตไปราชการ',
            'status' => 'สถานะ',
            'content' => 'สรุปเนื้อหา/สาระสำคัญที่ได้รับ',
            'benefit' => 'การนำไปใช้ประโยชน์ต่อหน่วยงาน',
            'suggestion' => 'ข้อเสนอแนะต่อหน่วยงาน',
            'ref' => 'ไฟล์แนบ',
            'submitted_at' => 'วันที่ส่งให้รับทราบ',
            'submitted_by' => 'ผู้ส่ง',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getDevelopment()
    {
        return $this->hasOne(Development::class, ['id' => 'development_id']);
    }

    /** แถวผู้รับทราบทั้งหมดของสรุปนี้ (เรียงตามลำดับที่ผู้บันทึกเลือกไว้) */
    public function getAcknowledgers()
    {
        return Approve::find()
            ->where(['name' => self::APPROVE_NAME, 'from_id' => $this->development_id])
            ->orderBy(['level' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    /** emp_id ของผู้รับทราบทั้งหมด ใช้เติมค่าเดิมกลับเข้าฟอร์ม */
    public function acknowledgerIds(): array
    {
        $ids = [];
        foreach ($this->getAcknowledgers() as $row) {
            $ids[] = (string) $row->emp_id;
        }
        return $ids;
    }

    /** ยังมีผู้รับทราบที่ยังไม่กดหรือไม่ */
    public function hasPendingAcknowledger(): bool
    {
        return Approve::find()
            ->where(['name' => self::APPROVE_NAME, 'from_id' => $this->development_id])
            ->andWhere(['<>', 'status', 'Pass'])
            ->exists();
    }

    /**
     * คำนวณสถานะใหม่จากรายชื่อผู้รับทราบแล้วบันทึกถ้าเปลี่ยน
     *
     * เรียกหลังบันทึกรายชื่อผู้รับทราบ และหลังมีคนกดรับทราบ
     */
    public function refreshStatus(): void
    {
        if ($this->status === self::STATUS_DRAFT) {
            return;
        }
        $newStatus = $this->hasPendingAcknowledger() ? self::STATUS_SUBMITTED : self::STATUS_ACKNOWLEDGED;
        if ($newStatus !== $this->status) {
            $this->status = $newStatus;
            $this->save(false, ['status', 'updated_at']);
        }
    }

    /**
     * แจ้งเตือนผู้รับทราบทุกคนว่ามีสรุปผลรอให้อ่าน
     *
     * ส่ง 3 ทาง: แจ้งเตือนในระบบ (หลัก ใช้ได้กับทุกคน) + LINE และ Telegram
     * เฉพาะคนที่ผูกบัญชีไว้ ความล้มเหลวของแต่ละช่องไม่ทำให้การบันทึกล้ม
     *
     * @return int จำนวนคนที่สร้างแจ้งเตือนในระบบสำเร็จ
     */
    public function notifyAcknowledgers(): int
    {
        $development = $this->development;
        if (!$development) {
            return 0;
        }

        $requester = $development->createdByEmp;
        $requesterName = $requester?->fullname ?? '-';
        $title = 'สรุปผลประชุม/อบรม รอท่านรับทราบ';
        $body = implode("\n", [
            'เรื่อง : ' . (string) $development->topic,
            'วันที่ : ' . ThaiDateHelper::formatThaiDate($development->date_start, 'long', 'short'),
            'ผู้สรุป : ' . $requesterName,
        ]);
        // ข้อความที่ลงฐานข้อมูลต้องไม่มีอีโมจิ — connection charset ของระบบเป็น utf8 (3 ไบต์)
        // อักขระ 4 ไบต์จะทำให้ INSERT ล้มทั้งแถว ส่วน LINE/Telegram ไม่ผ่าน DB จึงใส่ได้
        $dbMessage = $title . "\n" . $body;
        $chatMessage = '📘 ' . $title . "\n" . $body;

        $sent = 0;
        foreach ($this->getAcknowledgers() as $row) {
            if ($row->status === 'Pass') {
                continue;
            }
            $emp = $row->employee;
            if (!$emp) {
                continue;
            }

            try {
                if (Notify::createFromApprove(
                    Notify::TYPE_DEVELOPMENT_SUMMARY,
                    $title,
                    (int) $emp->id,
                    'development_summary',
                    (string) $this->development_id,
                    $dbMessage
                )) {
                    $sent++;
                }
            } catch (\Throwable $th) {
                Yii::error('development summary notify fail (emp ' . $emp->id . '): ' . $th->getMessage(), __METHOD__);
            }

            $this->pushToChatChannels($emp, $chatMessage);
        }

        return $sent;
    }

    /**
     * แจ้งกลับผู้ส่งสรุปว่ามีคนกดรับทราบแล้ว เพื่อให้รู้ว่าเรื่องถึงปลายทาง
     */
    public function notifySubmitterAcknowledged(Employees $acknowledger, string $comment = ''): void
    {
        $development = $this->development;
        $requester = $development?->createdByEmp;
        if (!$requester) {
            return;
        }

        $title = 'มีผู้รับทราบสรุปผลประชุม/อบรมของท่านแล้ว';
        $lines = [
            'เรื่อง : ' . (string) ($development->topic ?? '-'),
            'ผู้รับทราบ : ' . $acknowledger->fullname(),
        ];
        if ($comment !== '') {
            $lines[] = 'ความเห็น : ' . $comment;
        }
        // อีโมจิเฉพาะฝั่ง chat เท่านั้น (ดูเหตุผลใน notifyAcknowledgers)
        $dbMessage = $title . "\n" . implode("\n", $lines);
        $chatMessage = '✅ ' . $dbMessage;

        try {
            Notify::createFromApprove(
                Notify::TYPE_DEVELOPMENT_SUMMARY_ACK,
                'รับทราบสรุปผลประชุม/อบรมแล้ว',
                (int) $requester->id,
                'development_summary',
                (string) $this->development_id,
                $dbMessage
            );
        } catch (\Throwable $th) {
            Yii::error('development summary ack notify fail: ' . $th->getMessage(), __METHOD__);
        }

        $this->pushToChatChannels($requester, $chatMessage);
    }

    /**
     * ส่งข้อความเข้า LINE และ Telegram ของพนักงานคนหนึ่ง (ข้ามเงียบ ๆ ถ้าไม่ได้ผูกบัญชีไว้)
     */
    protected function pushToChatChannels(Employees $emp, string $message): void
    {
        $lineId = trim((string) ($emp->user?->line_id ?? ''));
        if ($lineId !== '') {
            try {
                LineMsg::sendMsg($lineId, $message);
            } catch (\Throwable $th) {
                Yii::error('development summary line fail (emp ' . $emp->id . '): ' . $th->getMessage(), __METHOD__);
            }
        }

        $chatId = trim((string) ($emp->user?->telegram_id ?? ''));
        if ($chatId !== '') {
            try {
                Yii::$app->telegram->sendDirectMessage($chatId, $message, ['disable_web_page_preview' => true]);
            } catch (\Throwable $th) {
                Yii::error('development summary telegram fail (emp ' . $emp->id . '): ' . $th->getMessage(), __METHOD__);
            }
        }
    }

    /**
     * เนื้อหาถูกแก้หลังจากผู้รับทราบกดรับทราบไปแล้วหรือไม่
     *
     * ผู้ใช้เลือกให้ "แก้ได้ สถานะคงเดิม" จึงต้องมีร่องรอยให้ผู้รับทราบเห็นว่าเนื้อหาขยับ
     */
    public function isEditedAfterSubmit(): bool
    {
        if (empty($this->submitted_at) || empty($this->updated_at)) {
            return false;
        }
        return strtotime((string) $this->updated_at) > strtotime((string) $this->submitted_at) + 60;
    }
}
