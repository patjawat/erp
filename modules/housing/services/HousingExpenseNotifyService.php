<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\components\AppHelper;
use app\modules\housing\models\BillingPeriod;
use app\modules\housing\models\MonthlyAccount;
use app\modules\hr\models\Employees;
use app\modules\notify\models\Notify;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แจ้งค่าใช้จ่ายบ้านพักประจำเดือนให้ผู้พักอาศัย ผ่าน 2 ช่องทาง
 *  - ในแอป (โมดูล notify → กระดิ่งแจ้งเตือน + หน้า /notify)
 *  - Telegram (กล่องส่วนตัวของผู้พัก ถ้าผูก Telegram ไว้)
 *
 * ทุกช่องทางกลืนข้อผิดพลาดไว้เอง การส่งล้มเหลวต้องไม่ทำให้งานหลักล้มตาม
 */
final class HousingExpenseNotifyService
{
    /**
     * ส่งแจ้งเตือนค่าใช้จ่ายของทุกบัญชีในรอบเดือนที่มีผู้พักและมียอด > 0
     *
     * @return array{targets:int,inApp:int,telegram:int,skipped:int}
     */
    public function notifyPeriod(BillingPeriod $period): array
    {
        $accounts = MonthlyAccount::find()
            ->where(['billing_period_id' => $period->id, 'status' => MonthlyAccount::STATUS_SAVED])
            ->andWhere(['not', ['payer_emp_id' => null]])
            ->andWhere(['>', 'total_amount', 0])
            ->all();

        $result = ['targets' => 0, 'inApp' => 0, 'telegram' => 0, 'skipped' => 0];
        foreach ($accounts as $account) {
            $empId = (int) $account->payer_emp_id;
            if ($empId <= 0) {
                $result['skipped']++;
                continue;
            }
            $result['targets']++;
            if ($this->notifyInApp($period, $account, $empId)) {
                $result['inApp']++;
            }
            if ($this->notifyTelegram($period, $account, $empId)) {
                $result['telegram']++;
            } else {
                $result['skipped']++;
            }
        }

        return $result;
    }

    private function location(MonthlyAccount $account): string
    {
        return implode(' / ', array_filter([$account->building_name, $account->unit_name, $account->room_name]))
            ?: (string) $account->building_name;
    }

    private function dueText(BillingPeriod $period): string
    {
        return $period->due_date ? (AppHelper::convertToThai($period->due_date) ?? '—') : '—';
    }

    private function amount($value): string
    {
        return number_format((float) $value, 2) . ' บาท';
    }

    private function notifyInApp(BillingPeriod $period, MonthlyAccount $account, int $empId): bool
    {
        $title = 'ค่าใช้จ่ายบ้านพักประจำ' . $period->name;
        $message = implode("\n", [
            'บ้านพัก: ' . $this->location($account),
            'ค่าใช้จ่ายรวม: ' . $this->amount($account->total_amount),
            'ชำระแล้ว: ' . $this->amount($account->paid_amount),
            'คงเหลือ: ' . $this->amount($account->balance_amount),
            'กำหนดชำระ: ' . $this->dueText($period),
        ]);
        $notify = Notify::createFromApprove(
            Notify::TYPE_HOUSING_EXPENSE,
            $title,
            $empId,
            'housing_monthly_account',
            (string) $account->id,
            $message,
            ['billing_period_id' => (int) $period->id, 'balance' => (float) $account->balance_amount]
        );
        return $notify !== null;
    }

    private function notifyTelegram(BillingPeriod $period, MonthlyAccount $account, int $empId): bool
    {
        try {
            $employee = Employees::findOne($empId);
            $chatId = trim((string) ($employee?->user?->telegram_id ?? ''));
            if ($chatId === '') {
                Yii::info('บ้านพัก: บุคลากร emp_id=' . $empId . ' ยังไม่ได้ผูก Telegram', __METHOD__);
                return false;
            }
            $text = implode("\n", [
                '🏠 <b>ค่าใช้จ่ายบ้านพักประจำ' . Html::encode($period->name) . '</b>',
                '',
                'บ้านพัก: ' . Html::encode($this->location($account)),
                'ค่าใช้จ่ายรวม: <b>' . $this->amount($account->total_amount) . '</b>',
                'ชำระแล้ว: ' . $this->amount($account->paid_amount),
                'คงเหลือ: <b>' . $this->amount($account->balance_amount) . '</b>',
                'กำหนดชำระ: ' . Html::encode($this->dueText($period)),
                '',
                'กรุณาชำระภายในกำหนด ขอบคุณครับ',
            ]);
            return (bool) Yii::$app->telegram->sendDirectMessage($chatId, $text, [
                'reply_markup' => [
                    'inline_keyboard' => [[[
                        'text' => '📄 ดูรายละเอียด',
                        'url' => Url::to(['/housing/my', 'housing_tab' => 'expenses'], true),
                    ]]],
                ],
            ]);
        } catch (\Throwable $e) {
            Yii::error('ส่ง Telegram ค่าใช้จ่ายบ้านพักไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}
