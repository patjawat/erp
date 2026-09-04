<?php

namespace app\modules\finance\services;

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanFollowup;
use app\modules\hr\models\Employees;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แจ้งเตือนลูกหนี้เงินยืมทาง Telegram
 *
 * ส่งเข้ากล่องส่วนตัวทุกกรณี ไม่ส่งเข้าห้องกลุ่ม เพราะยอดหนี้รายบุคคลไม่ควรขึ้นห้องรวม
 * ฝั่งการเงินหาผู้รับจากสิทธิ์ financeOperate ไม่ใช่จากรายชื่อที่ตั้งไว้ตายตัว
 * คนดูแลทะเบียนจึงเปลี่ยนตัวได้โดยไม่ต้องมาแก้ค่าตั้งต้นที่ไหน
 *
 * ทุกเมธอดกลืนข้อผิดพลาดของ Telegram ไว้เอง เพราะการส่งข้อความล้มเหลว
 * (เน็ตหลุด โทเคนหมดอายุ ผู้ใช้บล็อกบอท) ต้องไม่ทำให้งานหลักที่เรียกมาล้มตาม
 */
class FinanceLoanTelegramService
{
    /** ข้อความตามจังหวะการเตือน */
    public function notifyBorrower(FinanceLoan $loan, string $stage): bool
    {
        if (!$loan->borrower_emp_id) {
            return false;
        }
        $lines = array_merge([$this->heading($loan, $stage), ''], $this->detailLines($loan), ['', $this->callToAction($stage)]);
        return $this->sendToEmp(
            (int) $loan->borrower_emp_id,
            implode("\n", $lines),
            '📄 ดูใบยืม',
            Url::to(['/finance/loan/view', 'id' => $loan->id], true)
        );
    }

    /** แจ้งเจ้าหน้าที่การเงินทุกคนที่มีสิทธิ์และผูก Telegram ไว้ */
    public function notifyFinance(FinanceLoan $loan, string $stage): int
    {
        $lines = array_merge(
            ['💼 <b>ลูกหนี้เงินยืมเกินกำหนด</b>', ''],
            $this->detailLines($loan),
            ['ผู้ยืม: ' . Html::encode($loan->borrower_name), '', 'ควรพิจารณาออกหนังสือติดตาม'],
        );
        $text = implode("\n", $lines);
        $url = Url::to(['/finance/loan/view', 'id' => $loan->id], true);

        $sent = 0;
        foreach ($this->financeEmployees() as $employee) {
            if ($this->sendToEmp((int) $employee->id, $text, '📌 เปิดใบยืม', $url)) {
                $sent++;
            }
        }
        return $sent;
    }

    /** แจ้งผู้ยืมว่ามีหนังสือติดตามออกแล้ว */
    public function notifyLetterIssued(FinanceLoan $loan, FinanceLoanFollowup $letter): bool
    {
        if (!$loan->borrower_emp_id) {
            return false;
        }
        $lines = [
            '📨 <b>มีหนังสือติดตามลูกหนี้เงินยืม</b>',
            '',
            'สัญญาเลขที่: ' . Html::encode($loan->contract_no),
            'หนังสือครั้งที่: ' . (int) $letter->letter_seq,
            'ยอดคงเหลือ: ' . number_format($loan->outstanding_amount, 2) . ' บาท',
        ];
        if ($letter->new_due_at) {
            $lines[] = 'ขอให้ส่งใช้ภายใน: ' . Yii::$app->formatter->asDate($letter->new_due_at, 'php:d/m/Y');
        }
        return $this->sendToEmp(
            (int) $loan->borrower_emp_id,
            implode("\n", $lines),
            '📄 ดูใบยืม',
            Url::to(['/finance/loan/view', 'id' => $loan->id], true)
        );
    }

    /** สรุปลูกหนี้ค้างรายสัปดาห์ ส่งให้เจ้าหน้าที่การเงิน */
    public function notifyWeeklySummary(array $loans): int
    {
        if (!$loans) {
            return 0;
        }
        $lines = ['📊 <b>สรุปลูกหนี้เงินยืมค้างชำระ</b>', ''];
        $total = 0.0;
        foreach (array_slice($loans, 0, 15) as $loan) {
            $total += (float) $loan->outstanding_amount;
            $lines[] = Html::encode($loan->contract_no) . ' · ' . Html::encode($loan->borrower_name)
                . ' · ' . number_format($loan->outstanding_amount, 2) . ' บาท'
                . ' · เกิน ' . $loan->daysOverdue() . ' วัน';
        }
        if (count($loans) > 15) {
            $lines[] = '… และอีก ' . (count($loans) - 15) . ' ราย';
            foreach (array_slice($loans, 15) as $loan) {
                $total += (float) $loan->outstanding_amount;
            }
        }
        $lines[] = '';
        $lines[] = 'รวม ' . count($loans) . ' ราย เป็นเงิน ' . number_format($total, 2) . ' บาท';
        $text = implode("\n", $lines);
        $url = Url::to(['/finance/loan/outstanding'], true);

        $sent = 0;
        foreach ($this->financeEmployees() as $employee) {
            if ($this->sendToEmp((int) $employee->id, $text, '📋 ดูลูกหนี้ค้าง', $url)) {
                $sent++;
            }
        }
        return $sent;
    }

    // ── ส่วนประกอบข้อความ ────────────────────────────────────────

    private function heading(FinanceLoan $loan, string $stage): string
    {
        return match ($stage) {
            FinanceLoanFollowup::STAGE_BEFORE_DUE => '⏰ <b>ใกล้ครบกำหนดส่งใช้เงินยืม</b>',
            FinanceLoanFollowup::STAGE_DUE => '📌 <b>วันนี้ครบกำหนดส่งใช้เงินยืม</b>',
            default => '🔴 <b>เงินยืมเกินกำหนดส่งใช้แล้ว</b>',
        };
    }

    private function callToAction(string $stage): string
    {
        return match ($stage) {
            FinanceLoanFollowup::STAGE_BEFORE_DUE => 'กรุณาเตรียมใบสำคัญและเงินเหลือจ่ายเพื่อส่งใช้ภายในกำหนด',
            FinanceLoanFollowup::STAGE_DUE => 'กรุณาติดต่องานการเงินเพื่อส่งใช้เงินยืมภายในวันนี้',
            default => 'กรุณาติดต่องานการเงินโดยด่วน หากพ้นกำหนดจะดำเนินการตามระเบียบต่อไป',
        };
    }

    private function detailLines(FinanceLoan $loan): array
    {
        $lines = [
            'สัญญาเลขที่: ' . Html::encode($loan->contract_no),
            'เรื่อง: ' . Html::encode(mb_substr((string) $loan->purpose, 0, 80)),
            'ยอดคงเหลือ: ' . number_format($loan->outstanding_amount, 2) . ' บาท',
        ];
        if ($loan->due_at) {
            $lines[] = 'ครบกำหนด: ' . Yii::$app->formatter->asDate($loan->due_at, 'php:d/m/Y');
            $overdue = $loan->daysOverdue();
            if ($overdue > 0) {
                $lines[] = 'เกินกำหนดมาแล้ว: ' . $overdue . ' วัน';
            }
        }
        return $lines;
    }

    /** @return Employees[] เจ้าหน้าที่การเงินที่มีสิทธิ์ดำเนินการ */
    private function financeEmployees(): array
    {
        try {
            $auth = Yii::$app->authManager;
            if (!$auth) {
                return [];
            }
            $employees = [];
            foreach (array_unique($auth->getUserIdsByRole('financeOperate')) as $userId) {
                $employee = Employees::findOne(['user_id' => $userId]);
                if ($employee) {
                    $employees[$employee->id] = $employee;
                }
            }
            return array_values($employees);
        } catch (\Throwable $e) {
            Yii::error('หาเจ้าหน้าที่การเงินไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            return [];
        }
    }

    private function sendToEmp(int $empId, string $text, string $buttonText, string $url): bool
    {
        try {
            if ($empId <= 0) {
                return false;
            }
            $employee = Employees::findOne($empId);
            $chatId = trim((string) ($employee?->user?->telegram_id ?? ''));
            if ($chatId === '') {
                Yii::info('เงินยืม: บุคลากร emp_id=' . $empId . ' ยังไม่ได้ผูก Telegram', __METHOD__);
                return false;
            }
            return (bool) Yii::$app->telegram->sendDirectMessage($chatId, $text, [
                'reply_markup' => ['inline_keyboard' => [[['text' => $buttonText, 'url' => $url]]]],
            ]);
        } catch (\Throwable $e) {
            Yii::error('ส่ง Telegram เงินยืมไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
