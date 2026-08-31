<?php

namespace app\modules\task\services;

use app\components\ThaiDate;
use app\modules\task\models\Task;
use app\modules\telegrambot\services\TelegramLinkService;
use Yii;
use yii\helpers\Html;

/**
 * ส่งแจ้งเตือนงานเข้า Telegram รายบุคคล
 *
 * ส่งเฉพาะงานที่ "ต้องรู้เดี๋ยวนี้" เท่านั้น คืองานด่วน หรืองานที่ครบกำหนดวันนี้/เลยกำหนด
 * งานทั่วไปไม่ส่งทันที เพราะคำสัญญาของระบบนี้คือทำให้ถูกรบกวนน้อยลง ไม่ใช่มากขึ้น
 * งานที่เหลือจะไปรวมอยู่ในสรุปประจำวันแทน
 */
class TaskTelegramService
{
    /**
     * แจ้งผู้รับผิดชอบเมื่อได้รับงาน
     *
     * @return bool ส่งสำเร็จหรือไม่ (false รวมถึงกรณีที่ตั้งใจไม่ส่ง)
     */
    public static function notifyAssigned(Task $task, ?int $actorEmpId = null): bool
    {
        try {
            if (!self::shouldPushNow($task)) {
                return false;
            }
            if ($actorEmpId !== null && (int) $actorEmpId === (int) $task->assignee_emp_id) {
                return false;   // สั่งงานให้ตัวเอง ไม่ต้องเด้งบอกตัวเอง
            }

            $chatId = self::chatIdOf($task);
            if ($chatId === null) {
                return false;   // ยังไม่ได้ผูกบัญชี Telegram
            }

            return (bool) Yii::$app->telegram->sendDirectMessage(
                $chatId,
                self::buildMessage($task),
                self::buildOptions()
            );
        } catch (\Throwable $e) {
            // แจ้งเตือนล้มเหลวต้องไม่ทำให้การบันทึกงานพัง
            Yii::warning('ส่งแจ้งเตือนงานเข้า Telegram ไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /** ช่วงเวลาที่ส่งแจ้งเตือนได้ตามปกติ นอกช่วงนี้ถือว่าเป็นเวลาส่วนตัว */
    public const QUIET_START_HOUR = 20;
    public const QUIET_END_HOUR = 7;

    /**
     * เกณฑ์ว่างานนี้ควรเด้งทันทีหรือไม่
     *
     * ตั้งใจให้เข้มงวด ถ้าทุกอย่างด่วนก็เท่ากับไม่มีอะไรด่วน
     * และเคารพเวลาส่วนตัวของผู้รับ ซึ่งเป็นสิ่งที่ไลน์ทำไม่ได้
     */
    public static function shouldPushNow(Task $task): bool
    {
        if (!TelegramLinkService::isEnabled()) {
            return false;
        }
        if (!$task->assignee_emp_id || !$task->isOpen()) {
            return false;
        }

        // คนลาอยู่ไม่ควรถูกตามงาน กลับมาแล้วเห็นในระบบและในสรุปประจำวันเอง
        if (self::isOnLeave((int) $task->assignee_emp_id)) {
            return false;
        }

        $cannotWait = self::cannotWait($task);

        // นอกเวลา ส่งเฉพาะของที่รอถึงเช้าไม่ได้จริง ๆ
        if (self::isQuietHours() && !$cannotWait) {
            return false;
        }

        if ($task->priority === Task::PRIORITY_URGENT) {
            return true;
        }
        // ครบกำหนดวันนี้หรือเลยกำหนดแล้ว ถือว่าต้องรู้เดี๋ยวนี้
        return $task->due_date !== null && $task->due_date <= date('Y-m-d');
    }

    /** ของที่รอถึงเช้าไม่ได้ = ด่วน และถึงกำหนดแล้ว */
    private static function cannotWait(Task $task): bool
    {
        return $task->priority === Task::PRIORITY_URGENT
            && $task->due_date !== null
            && $task->due_date <= date('Y-m-d');
    }

    public static function isQuietHours(?int $hour = null): bool
    {
        $hour = $hour ?? (int) date('G');
        return $hour >= self::QUIET_START_HOUR || $hour < self::QUIET_END_HOUR;
    }

    /** ลาที่อนุมัติแล้วและคลุมวันนี้ */
    public static function isOnLeave(int $empId, ?string $date = null): bool
    {
        if ($empId <= 0) {
            return false;
        }
        $date = $date ?: date('Y-m-d');

        try {
            return (new \yii\db\Query())
                ->from('{{%leave}}')
                ->where(['emp_id' => $empId, 'status' => 'Approve'])
                ->andWhere(['<=', 'date_start', $date])
                ->andWhere(['>=', 'date_end', $date])
                ->andWhere(['deleted_at' => null])
                ->exists();
        } catch (\Throwable $e) {
            Yii::warning('ตรวจวันลาไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * แจ้งเป็นชุดเมื่อสร้างงานหลายชิ้นพร้อมกัน
     *
     * คนเดียวได้หลายงานในคราวเดียวจะได้ข้อความเดียว ไม่ใช่เด้งเท่าจำนวนงาน
     *
     * @param Task[] $tasks
     */
    public static function notifyBatch(array $tasks, ?int $actorEmpId = null): void
    {
        $byChat = [];
        foreach ($tasks as $task) {
            if (!self::shouldPushNow($task)) {
                continue;
            }
            if ($actorEmpId !== null && (int) $actorEmpId === (int) $task->assignee_emp_id) {
                continue;
            }
            $chatId = self::chatIdOf($task);
            if ($chatId === null) {
                continue;
            }
            $byChat[$chatId][] = $task;
        }

        foreach ($byChat as $chatId => $group) {
            try {
                $text = count($group) === 1
                    ? self::buildMessage($group[0])
                    : self::buildGroupMessage($group);
                Yii::$app->telegram->sendDirectMessage($chatId, $text, self::buildOptions());
            } catch (\Throwable $e) {
                Yii::warning('ส่งแจ้งเตือนงานเป็นชุดไม่สำเร็จ: ' . $e->getMessage(), __METHOD__);
            }
        }
    }

    /** @param Task[] $tasks */
    private static function buildGroupMessage(array $tasks): string
    {
        $lines = ['📌 <b>คุณได้รับงานใหม่ ' . count($tasks) . ' รายการ</b>', ''];
        foreach ($tasks as $task) {
            $line = '• <b>' . Html::encode($task->title) . '</b>';
            if ($task->priority === Task::PRIORITY_URGENT) {
                $line .= ' 🔴';
            }
            if ($task->due_date) {
                $line .= PHP_EOL . '   🗓 ' . ThaiDate::toThaiDate($task->due_date, false);
            }
            $lines[] = $line;
        }
        return implode(PHP_EOL, $lines);
    }

    private static function chatIdOf(Task $task): ?string
    {
        $employee = $task->assignee;
        $chatId = trim((string) ($employee->user->telegram_id ?? ''));
        return $chatId !== '' ? $chatId : null;
    }

    private static function buildMessage(Task $task): string
    {
        $overdueDays = $task->overdueDays();
        $isUrgent = $task->priority === Task::PRIORITY_URGENT;

        if ($overdueDays > 0) {
            $head = '🔴 <b>งานเลยกำหนดแล้ว</b>';
        } elseif ($isUrgent) {
            $head = '🔴 <b>งานด่วน</b>';
        } else {
            $head = '📌 <b>งานครบกำหนดวันนี้</b>';
        }

        $lines = [$head, '', '<b>' . Html::encode($task->title) . '</b>'];

        if ($task->detail) {
            $lines[] = Html::encode(mb_substr((string) $task->detail, 0, 300));
        }

        $lines[] = '';

        if ($task->due_date) {
            $due = '🗓 กำหนดส่ง ' . ThaiDate::toThaiDate($task->due_date, false);
            if ($overdueDays > 0) {
                $due .= ' (' . $task->ageText() . ')';
            }
            $lines[] = $due;
        }

        if ($task->ownerUnit) {
            $lines[] = '🏢 ' . Html::encode((string) $task->ownerUnit->name);
        }

        if ($task->assigner) {
            $lines[] = '👤 มอบหมายโดย ' . Html::encode(trim($task->assigner->fname . ' ' . $task->assigner->lname));
        }

        return implode(PHP_EOL, $lines);
    }

    private static function buildOptions(): array
    {
        $options = [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        $url = self::taskPageUrl();
        if ($url !== null) {
            $options['reply_markup'] = [
                'inline_keyboard' => [[
                    ['text' => 'เปิดปฏิทินงาน', 'url' => $url],
                ]],
            ];
        }

        return $options;
    }

    /**
     * ลิงก์เปิดหน้าปฏิทินงาน สร้างจากโดเมนที่ตั้งไว้ในค่าตั้งค่าบอท
     *
     * ใช้ปุ่มแบบ url ธรรมดา ไม่ใช่ web_app เพราะ web_app ผูกกับโดเมนที่ลงทะเบียนไว้กับบอท
     * ถ้าโดเมนไม่ตรงปุ่มจะกดไม่ได้เลย
     */
    private static function taskPageUrl(): ?string
    {
        $base = trim((string) (TelegramLinkService::settings()['mini_app_base_url'] ?? ''));
        if ($base === '') {
            return null;
        }

        $parts = parse_url($base);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return null;
        }

        return 'https://' . $parts['host'] . '/task';
    }
}
