<?php

namespace app\commands;

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\services\FinanceLoanFollowupService;
use app\modules\finance\services\FinanceLoanTelegramService;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * แจ้งเตือนลูกหนี้เงินยืมทาง Telegram — ตั้ง cron ให้รันวันละครั้ง
 *
 * ทุกคำสั่งเป็น dry-run โดยปริยาย ต้องใส่ --apply=1 จึงจะส่งจริงและบันทึกลงฐานข้อมูล
 *
 *   php yii loan-remind/scan              # ดูว่าวันนี้จะเตือนใครบ้าง
 *   php yii loan-remind/scan --apply=1    # ส่งจริง (ตั้ง cron ตัวนี้ทุกเช้า)
 *   php yii loan-remind/weekly --apply=1  # สรุปลูกหนี้ค้างให้การเงิน (ทุกวันจันทร์)
 *   php yii loan-remind/outstanding       # รายงานลูกหนี้ค้างบนหน้าจอ ไม่ส่งอะไร
 *
 * รันซ้ำในวันเดียวกันไม่ทำให้ส่งซ้ำ เพราะการเตือนแต่ละจังหวะถูกกันด้วย unique index
 * บน finance_loan_followup.dedupe_key
 */
class LoanRemindController extends Controller
{
    /** @var bool ส่งจริงและบันทึกลงฐานข้อมูล (ค่าเริ่มต้นคือ dry-run) */
    public $apply = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['apply']);
    }

    /** ไล่ใบยืมที่ค้างอยู่ แล้วเตือนตามจังหวะที่ถึงกำหนด */
    public function actionScan()
    {
        $service = new FinanceLoanFollowupService();
        $loans = FinanceLoan::findOutstanding()->andWhere(['not', ['due_at' => null]])->orderBy(['due_at' => SORT_ASC])->all();

        $this->stdout('ใบยืมที่ยังค้าง ' . count($loans) . " ใบ\n", Console::FG_CYAN);
        if (!$this->apply) {
            $this->stdout("โหมดดูอย่างเดียว ยังไม่ส่งและไม่บันทึก — ใส่ --apply=1 เพื่อทำจริง\n\n", Console::FG_YELLOW);
        }

        $sent = 0;
        $skipped = 0;
        foreach ($loans as $loan) {
            $stage = $service->dueStage($loan);
            if ($stage === null) {
                continue;
            }
            $followup = $service->sendAuto($loan, $stage, !$this->apply);
            $label = sprintf(
                '%-14s %-28s ค้าง %10s บาท  %s',
                $loan->contract_no,
                mb_substr($loan->borrower_name, 0, 26),
                number_format($loan->outstanding_amount, 2),
                $loan->dueLabel()
            );
            if ($followup === null) {
                $skipped++;
                $this->stdout('  ข้าม  ' . $label . " (เตือนจังหวะนี้ไปแล้ว)\n");
                continue;
            }
            $sent++;
            $this->stdout('  เตือน ' . $label . "\n", Console::FG_GREEN);
        }

        $this->stdout("\nสรุป: เตือน {$sent} ใบ · ข้าม {$skipped} ใบ (เคยเตือนจังหวะเดิมแล้ว)\n", Console::FG_CYAN);
        return ExitCode::OK;
    }

    /** สรุปลูกหนี้ค้างส่งให้เจ้าหน้าที่การเงิน */
    public function actionWeekly()
    {
        $loans = FinanceLoanFollowupService::overdueLoans();
        $this->stdout('ลูกหนี้เกินกำหนด ' . count($loans) . " ราย\n", Console::FG_CYAN);
        if (!$loans) {
            return ExitCode::OK;
        }
        if (!$this->apply) {
            $this->stdout("โหมดดูอย่างเดียว — ใส่ --apply=1 เพื่อส่งจริง\n");
            return ExitCode::OK;
        }
        $sent = (new FinanceLoanTelegramService())->notifyWeeklySummary($loans);
        $this->stdout("ส่งสรุปให้เจ้าหน้าที่การเงิน {$sent} คน\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /** รายงานลูกหนี้ค้างบนหน้าจอ ไม่ส่งอะไรทั้งสิ้น */
    public function actionOutstanding()
    {
        $loans = FinanceLoanFollowupService::overdueLoans();
        if (!$loans) {
            $this->stdout("ไม่มีลูกหนี้เกินกำหนด\n", Console::FG_GREEN);
            return ExitCode::OK;
        }
        $total = 0.0;
        foreach ($loans as $loan) {
            $total += (float) $loan->outstanding_amount;
            $this->stdout(sprintf(
                "%-14s %-26s %12s  เกิน %4d วัน  ติดตามแล้ว %d ครั้ง\n",
                $loan->contract_no,
                mb_substr($loan->borrower_name, 0, 24),
                number_format($loan->outstanding_amount, 2),
                $loan->daysOverdue(),
                (int) $loan->followup_count
            ));
        }
        $this->stdout(sprintf("\nรวม %d ราย เป็นเงิน %s บาท\n", count($loans), number_format($total, 2)), Console::FG_CYAN);
        return ExitCode::OK;
    }
}
