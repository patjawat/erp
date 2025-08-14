<?php
namespace app\commands;

use Yii;
use yii\console\ExitCode;
use yii\console\Controller;
use app\modules\backup\components\BackupHelper;

class BackupsController extends Controller
{
    /**
     * Backup database + fileupload + all
     */

    /**
     * แสดงรายการ action
     */
    public function actionIndex()
    {
        echo "Available actions:\n";
        echo "  database    Backup database\n";
        echo "  files       Backup fileupload\n";
        echo "  all         Backup database + files\n";
    }

    /**
     * Backup database
     */
    public function actionDatabase()
    {
        $res = BackupHelper::backupDatabase();
        echo "Database backup: " . ($res['success'] ? 'Success' : 'Failed') . PHP_EOL;
        if (!$res['success']) echo implode("\n", $res['error']) . PHP_EOL;
        else echo "File: {$res['file']}, Size: {$res['size']}" . PHP_EOL;
    }

    /**
     * Backup fileupload
     */
    public function actionFiles()
    {
        $res = BackupHelper::backupFiles();
        echo "File upload backup: " . ($res['success'] ? 'Success' : 'Failed') . PHP_EOL;
        if (!$res['success']) echo implode("\n", $res['error']) . PHP_EOL;
        else echo "File: {$res['file']}, Size: {$res['size']}" . PHP_EOL;
    }

    /**
     * Backup all (database + files)
     */
    public function actionAll()
    {
        $res = BackupHelper::backupAll();
        echo "Backup all: " . ($res['success'] ? 'Success' : 'Failed') . PHP_EOL;
        if (!$res['success']) echo implode("\n", $res['error']) . PHP_EOL;
        else echo "File: {$res['file']}, Size: {$res['size']}" . PHP_EOL;
    }

}
