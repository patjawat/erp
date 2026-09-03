<?php

namespace app\modules\finance\models;

use yii\db\ActiveRecord;

/**
 * การติดตามลูกหนี้เงินยืมหนึ่งครั้ง — ทั้งข้อความแจ้งเตือนและหนังสือราชการ
 *
 * เก็บรวมตารางเดียวเพราะการเงินอยากเห็นประวัติทั้งหมดเรียงต่อกันว่าติดตามไปแล้วกี่ทาง
 * แต่แยกเลขนับสองชุด — letter_seq นับเฉพาะหนังสือ เพราะเลข “ครั้งที่ N” บนหัวหนังสือ
 * ต้องไม่ถูกข้อความแจ้งเตือนอัตโนมัติดันให้เขยิบ
 *
 * dedupe_key กันแจ้งซ้ำเมื่อ cron รันหลายรอบในวันเดียว วิธีใช้คือเขียนแถวก่อนแล้วค่อยส่ง
 * ถ้าเขียนไม่ผ่านเพราะซ้ำ แปลว่าเคยส่งไปแล้ว ไม่ต้องส่งซ้ำ ค่า null ซ้ำกันได้ใน MySQL
 * หนังสือจึงออกกี่ฉบับก็ได้โดยไม่ชน
 *
 * @property FinanceLoan $loan
 */
class FinanceLoanFollowup extends ActiveRecord
{
    use LoanAuditTrait;

    public const CHANNEL_LETTER = 'letter';
    public const CHANNEL_TELEGRAM = 'telegram';

    public const STAGE_BEFORE_DUE = 'before_due';
    public const STAGE_DUE = 'due';
    public const STAGE_OVERDUE_7 = 'overdue_7';
    public const STAGE_OVERDUE_15 = 'overdue_15';
    public const STAGE_OVERDUE_30 = 'overdue_30';
    public const STAGE_MANUAL = 'manual';

    public static function tableName()
    {
        return '{{%finance_loan_followup}}';
    }

    public function rules()
    {
        return [
            [['loan_id', 'channel'], 'required'],
            [['loan_id', 'letter_seq', 'days_overdue', 'created_by', 'updated_by'], 'integer'],
            [['channel'], 'in', 'range' => array_keys(self::channelOptions())],
            [['stage'], 'in', 'range' => array_keys(self::stageOptions())],
            [['letter_date', 'new_due_at'], 'date', 'format' => 'php:Y-m-d'],
            [['notified_at'], 'safe'],
            [['letter_no'], 'string', 'max' => 100],
            [['recipient'], 'string', 'max' => 255],
            [['dedupe_key'], 'string', 'max' => 64],
            [['note'], 'string'],
            [['letter_no', 'letter_date'], 'required', 'when' => fn(self $m) => $m->channel === self::CHANNEL_LETTER,
                'whenClient' => 'function () { return false; }',
                'message' => 'หนังสือติดตามต้องมีเลขที่และลงวันที่'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'channel' => 'ช่องทาง',
            'stage' => 'จังหวะการเตือน',
            'letter_seq' => 'หนังสือครั้งที่',
            'letter_no' => 'เลขที่หนังสือ',
            'letter_date' => 'ลงวันที่',
            'new_due_at' => 'กำหนดใหม่ให้แล้วเสร็จภายใน',
            'notified_at' => 'เวลาที่แจ้ง',
            'days_overdue' => 'เกินกำหนด (วัน)',
            'recipient' => 'ผู้รับ',
            'note' => 'หมายเหตุ',
        ];
    }

    public static function channelOptions(): array
    {
        return [
            self::CHANNEL_LETTER => 'หนังสือติดตาม',
            self::CHANNEL_TELEGRAM => 'แจ้งเตือน Telegram',
        ];
    }

    public static function stageOptions(): array
    {
        return [
            self::STAGE_BEFORE_DUE => 'ก่อนครบกำหนด 7 วัน',
            self::STAGE_DUE => 'วันครบกำหนด',
            self::STAGE_OVERDUE_7 => 'เกินกำหนด 7 วัน',
            self::STAGE_OVERDUE_15 => 'เกินกำหนด 15 วัน',
            self::STAGE_OVERDUE_30 => 'เกินกำหนด 30 วัน',
            self::STAGE_MANUAL => 'ออกหนังสือเอง',
        ];
    }

    public function channelLabel(): string
    {
        return self::channelOptions()[$this->channel] ?? $this->channel;
    }

    public function stageLabel(): string
    {
        return self::stageOptions()[$this->stage] ?? '—';
    }

    public function getLoan()
    {
        return $this->hasOne(FinanceLoan::class, ['id' => 'loan_id']);
    }

    /** เลข “ครั้งที่ N” ของหนังสือฉบับถัดไป นับเฉพาะหนังสือ ไม่นับข้อความแจ้งเตือน */
    public static function nextLetterSeq(int $loanId): int
    {
        return 1 + (int) self::find()
            ->where(['loan_id' => $loanId, 'channel' => self::CHANNEL_LETTER])
            ->max('letter_seq');
    }

    /** กุญแจกันแจ้งซ้ำของการเตือนอัตโนมัติ หนึ่งใบยืมต่อหนึ่งจังหวะเตือน ส่งได้ครั้งเดียว */
    public static function autoKey(int $loanId, string $stage): string
    {
        return 'loan-' . $loanId . '-' . $stage;
    }
}
