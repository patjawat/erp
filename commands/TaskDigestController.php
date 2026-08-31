<?php

namespace app\commands;

use app\modules\hr\models\Employees;
use app\modules\task\services\TaskTelegramService;
use app\modules\telegrambot\services\TelegramLinkService;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * สรุปงานประจำวันส่งเข้า Telegram
 *
 * ตั้ง cron ให้รันวันละครั้งตอนเช้า เช่น
 *   0 8 * * *  docker exec dansai php /app/yii task-digest/send
 *
 * งานปกติที่ยังไม่ถึงกำหนดจะไม่เด้งทันทีตอนสร้าง (ดู TaskTelegramService)
 * แต่มารวมอยู่ในสรุปนี้แทน คนจึงไม่พลาดงานโดยไม่ต้องถูกกวนทั้งวัน
 */
class TaskDigestController extends Controller
{
    /** ทดสอบโดยไม่ส่งจริง */
    public $dryRun = false;

    /** จำกัดให้ส่งเฉพาะพนักงานคนเดียว ใช้ตอนทดสอบกับตัวเอง */
    public $empId;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['dryRun', 'empId']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['d' => 'dryRun', 'e' => 'empId']);
    }

    /**
     * ส่งสรุปงานประจำวันให้ทุกคนที่ผูก Telegram ไว้แล้ว
     *
     * ตัวอย่าง
     *   php yii task-digest/send --dryRun=1          ดูว่าจะส่งใครบ้าง โดยไม่ส่งจริง
     *   php yii task-digest/send --empId=8           ส่งให้คนเดียว ใช้ตอนทดสอบ
     *   php yii task-digest/send                     ส่งจริงทุกคน
     */
    public function actionSend()
    {
        $dryRun = (bool) $this->dryRun;

        if (!TelegramLinkService::isEnabled()) {
            $this->stdout('ระบบแจ้งเตือน Telegram ถูกปิดอยู่ในหน้าตั้งค่า ไม่ส่งอะไรทั้งสิ้น' . PHP_EOL, Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $query = Employees::find()->alias('e')
            ->innerJoin('{{%user}} u', 'u.id = e.user_id')
            ->where(['not', ['u.telegram_id' => null]])
            ->andWhere(['<>', 'u.telegram_id', 0]);

        if ($this->empId) {
            $query->andWhere(['e.id' => (int) $this->empId]);
        }

        $employees = $query->all();

        if (!$employees) {
            $this->stdout('ไม่พบพนักงานที่ผูก Telegram ไว้' . PHP_EOL, Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout(
            ($dryRun ? '[ทดสอบ ไม่ส่งจริง] ' : '') . 'ตรวจ ' . count($employees) . ' คน' . PHP_EOL . PHP_EOL
        );

        $stat = ['sent' => 0, 'skipped_no_task' => 0, 'skipped_leave' => 0, 'skipped_no_chat' => 0, 'failed' => 0];

        foreach ($employees as $employee) {
            $name = trim($employee->fname . ' ' . $employee->lname);
            try {
                $result = TaskTelegramService::sendDailyDigest((int) $employee->id, $dryRun);
            } catch (\Throwable $e) {
                $result = 'failed';
                Yii::error('ส่งสรุปงานประจำวันไม่สำเร็จ emp ' . $employee->id . ': ' . $e->getMessage(), __METHOD__);
            }

            $stat[$result] = ($stat[$result] ?? 0) + 1;

            if ($result === 'sent') {
                $digest = TaskTelegramService::dailyDigestFor((int) $employee->id);
                $this->stdout(sprintf(
                    "  ส่ง    %-28s ค้าง %d งาน (ต้องสนใจ %d)\n",
                    mb_substr($name, 0, 28),
                    $digest['total'] ?? 0,
                    $digest['attention'] ?? 0
                ), Console::FG_GREEN);
            } elseif ($result === 'failed') {
                $this->stdout(sprintf("  พลาด  %-28s\n", mb_substr($name, 0, 28)), Console::FG_RED);
            }
        }

        $this->stdout(PHP_EOL . 'สรุป' . PHP_EOL);
        $this->stdout(sprintf("  ส่งแล้ว            %d\n", $stat['sent']));
        $this->stdout(sprintf("  ข้าม ไม่มีงานค้าง   %d\n", $stat['skipped_no_task']));
        $this->stdout(sprintf("  ข้าม ลาอยู่         %d\n", $stat['skipped_leave']));
        $this->stdout(sprintf("  ข้าม ไม่มี chat id  %d\n", $stat['skipped_no_chat']));
        if ($stat['failed'] > 0) {
            $this->stdout(sprintf("  ส่งไม่สำเร็จ        %d\n", $stat['failed']), Console::FG_RED);
        }

        return ExitCode::OK;
    }

    /** ดูตัวอย่างข้อความของคนใดคนหนึ่งโดยไม่ส่ง */
    public function actionPreview($empId)
    {
        $digest = TaskTelegramService::dailyDigestFor((int) $empId);
        if ($digest === null) {
            $this->stdout('พนักงานคนนี้ไม่มีงานค้าง จึงไม่มีสรุปให้ส่ง' . PHP_EOL, Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $this->stdout('--- ข้อความที่จะส่ง ---' . PHP_EOL, Console::FG_CYAN);
        $this->stdout(strip_tags($digest['text']) . PHP_EOL);
        $this->stdout('--- จบข้อความ ---' . PHP_EOL, Console::FG_CYAN);
        return ExitCode::OK;
    }
}
