<?php
namespace app\modules\backup\commands;

use yii\console\Controller;
use Yii;

class BackupController extends Controller
{
    public $backupPath = '@app/runtime/backup';

    public function actionDatabase()
    {
        $backupPath = Yii::getAlias($this->backupPath);
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0777, true);
        }

        $db = Yii::$app->db;
        $dbName = $db->dsn; // parse ชื่อ database จาก dsn
        preg_match('/dbname=([^;]+)/', $dbName, $matches);
        $dbname = $matches[1];

        $date = date('Y-m-d_H-i-s');
        $file = "$backupPath/{$dbname}_{$date}.sql";

        $command = "mysqldump -h {$db->host} -u {$db->username} -p{$db->password} {$dbname} > $file";

        exec($command, $output, $returnVar);
        if ($returnVar === 0) {
            echo "✅ Database backup completed: $file\n";
            return $file;
        } else {
            echo "❌ Database backup failed\n";
            return false;
        }
    }

    public function actionFiles()
    {
        $source = Yii::getAlias('@app/modules/filemanager/fileupload');
        $date = date('Y-m-d_H-i-s');
        $backupPath = Yii::getAlias($this->backupPath);
        $archive = "$backupPath/fileupload_backup_{$date}.tar.gz";

        $command = "tar -czf $archive -C $source .";
        exec($command, $output, $returnVar);
        if ($returnVar === 0) {
            echo "✅ File backup completed: $archive\n";
            return $archive;
        } else {
            echo "❌ File backup failed\n";
            return false;
        }
    }
}
