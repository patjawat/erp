<?php

namespace app\modules\finance\services;

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanFollowup;
use Yii;

/**
 * ติดตามลูกหนี้เงินยืม — ทั้งการเตือนอัตโนมัติและหนังสือราชการ
 *
 * ลำดับของการเตือนอัตโนมัติคือ “เขียนแถวก่อน แล้วค่อยส่ง” ไม่ใช่ส่งก่อนแล้วค่อยเขียน
 * เพราะถ้าส่งสำเร็จแต่เขียนแถวไม่ผ่าน รอบถัดไปของ cron จะส่งซ้ำอีก ผู้ยืมได้ข้อความ
 * ซ้ำ ๆ ส่วนถ้าเขียนผ่านแต่ส่งไม่สำเร็จ เสียแค่การเตือนรอบนั้นซึ่งรอบถัดไปก็จะไล่ต่อ
 * ให้เองอยู่แล้ว — ความเสียหายด้านที่ยอมรับได้กว่า
 */
class FinanceLoanFollowupService
{
    /** จำนวนวันเกินกำหนดของแต่ละจังหวะการเตือน */
    public const OVERDUE_STAGES = [
        7 => FinanceLoanFollowup::STAGE_OVERDUE_7,
        15 => FinanceLoanFollowup::STAGE_OVERDUE_15,
        30 => FinanceLoanFollowup::STAGE_OVERDUE_30,
    ];

    /** เตือนล่วงหน้ากี่วันก่อนครบกำหนด */
    public const BEFORE_DUE_DAYS = 7;

    public function __construct(private ?FinanceLoanTelegramService $telegram = null)
    {
        $this->telegram = $telegram ?: new FinanceLoanTelegramService();
    }

    /**
     * จังหวะการเตือนที่ใบยืมใบนี้ควรได้รับ ณ วันนี้ คืน null ถ้ายังไม่ถึงจังหวะไหน
     *
     * ใช้ตัวเดียวกับที่หน้าจอใช้คำนวณป้ายสี (daysToDue) เพื่อให้สิ่งที่ผู้ใช้เห็น
     * กับสิ่งที่ระบบส่งออกไป ตรงกันเสมอ
     */
    public function dueStage(FinanceLoan $loan): ?string
    {
        if ($loan->isClosed() || (float) $loan->outstanding_amount <= 0 || !$loan->due_at) {
            return null;
        }
        $days = $loan->daysToDue();
        if ($days === self::BEFORE_DUE_DAYS) {
            return FinanceLoanFollowup::STAGE_BEFORE_DUE;
        }
        if ($days === 0) {
            return FinanceLoanFollowup::STAGE_DUE;
        }
        $overdue = -$days;
        // ใช้จังหวะสูงสุดที่ผ่านมาแล้ว ไม่ใช่ต้องตรงวันพอดี เผื่อ cron ไม่ได้รันบางวัน
        $matched = null;
        foreach (self::OVERDUE_STAGES as $threshold => $stage) {
            if ($overdue >= $threshold) {
                $matched = $stage;
            }
        }
        return $matched;
    }

    /**
     * บันทึกและส่งการเตือนอัตโนมัติหนึ่งจังหวะ
     *
     * คืน null เมื่อเคยเตือนจังหวะนี้ไปแล้ว — ตัดสินจาก unique index บน dedupe_key
     * ไม่ใช่จากการ SELECT ก่อน เพราะถ้า cron สองตัวรันพร้อมกัน การเช็คก่อนเขียน
     * จะผ่านทั้งคู่แล้วส่งซ้ำ ส่วนดัชนีกันได้จริงในระดับฐานข้อมูล
     */
    public function sendAuto(FinanceLoan $loan, string $stage, bool $dryRun = false): ?FinanceLoanFollowup
    {
        $key = FinanceLoanFollowup::autoKey((int) $loan->id, $stage);
        if ($dryRun) {
            $exists = FinanceLoanFollowup::find()->where(['dedupe_key' => $key])->exists();
            return $exists ? null : new FinanceLoanFollowup(['loan_id' => $loan->id, 'stage' => $stage]);
        }

        $followup = new FinanceLoanFollowup([
            'loan_id' => $loan->id,
            'channel' => FinanceLoanFollowup::CHANNEL_TELEGRAM,
            'stage' => $stage,
            'notified_at' => date('Y-m-d H:i:s'),
            'days_overdue' => $loan->daysOverdue(),
            'dedupe_key' => $key,
        ]);

        try {
            if (!$followup->save()) {
                return null;
            }
        } catch (\yii\db\IntegrityException $e) {
            return null; // เตือนจังหวะนี้ไปแล้ว
        }

        $recipients = [];
        if ($this->telegram->notifyBorrower($loan, $stage)) {
            $recipients[] = 'ผู้ยืม';
        }
        // เกินกำหนดแล้วให้การเงินเห็นด้วย จะได้ตัดสินใจออกหนังสือได้ทันเวลา
        if (in_array($stage, self::OVERDUE_STAGES, true)) {
            $count = $this->telegram->notifyFinance($loan, $stage);
            if ($count > 0) {
                $recipients[] = 'การเงิน ' . $count . ' คน';
            }
        }
        $followup->updateAttributes(['recipient' => $recipients ? implode(' · ', $recipients) : 'ไม่มีผู้รับที่ผูก Telegram']);
        $this->refreshCounters($loan);

        return $followup;
    }

    /** ออกหนังสือติดตามหนึ่งฉบับ */
    public function issueLetter(FinanceLoan $loan, FinanceLoanFollowup $letter): bool
    {
        $letter->loan_id = $loan->id;
        $letter->channel = FinanceLoanFollowup::CHANNEL_LETTER;
        $letter->stage = FinanceLoanFollowup::STAGE_MANUAL;
        $letter->letter_seq = $letter->letter_seq ?: FinanceLoanFollowup::nextLetterSeq((int) $loan->id);
        $letter->notified_at = $letter->notified_at ?: date('Y-m-d H:i:s');
        $letter->days_overdue = $loan->daysOverdue();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$letter->save()) {
                $transaction->rollBack();
                return false;
            }
            $this->refreshCounters($loan);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            $letter->addError('letter_no', 'บันทึกไม่สำเร็จ: ' . $e->getMessage());
            return false;
        }

        // แจ้งหลังจากบันทึกสำเร็จแล้ว ถ้าส่งไม่ได้ก็ไม่ย้อนลบหนังสือที่ออกไปแล้ว
        if ($this->telegram->notifyLetterIssued($loan, $letter)) {
            $letter->updateAttributes(['recipient' => 'ผู้ยืม (Telegram)']);
        }
        return true;
    }

    public function deleteFollowup(FinanceLoanFollowup $followup): bool
    {
        $loan = $followup->loan;
        if (!$followup->delete()) {
            return false;
        }
        if ($loan) {
            $this->refreshCounters($loan);
        }
        return true;
    }

    /** ยอดนับบนหัวสัญญา ใช้แสดงในทะเบียนโดยไม่ต้อง join ตารางติดตาม */
    public function refreshCounters(FinanceLoan $loan): void
    {
        $count = (int) FinanceLoanFollowup::find()->where(['loan_id' => $loan->id])->count();
        $last = FinanceLoanFollowup::find()->where(['loan_id' => $loan->id])->max('notified_at');
        $loan->updateAttributes([
            'followup_count' => $count,
            'last_followup_at' => $last ? substr((string) $last, 0, 10) : null,
        ]);
    }

    /** ใบยืมที่เกินกำหนดแล้ว เรียงตามค้างนานที่สุด — ฐานของหน้าลูกหนี้ค้างและสรุปรายสัปดาห์ */
    public static function overdueLoans(): array
    {
        return FinanceLoan::findOutstanding()
            ->andWhere(['<', 'due_at', date('Y-m-d')])
            ->orderBy(['due_at' => SORT_ASC])
            ->all();
    }
}
