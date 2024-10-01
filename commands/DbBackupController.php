<?php
namespace app\commands;

use yii\console\Controller;
use Yii;

class DbBackupController extends Controller
{
    public function actionIndex()
    {
        // Define MySQL credentials and database details
        $db = Yii::$app->db;
        $dsn = $db->dsn;
        preg_match('/host=(.*);dbname=(.*)/', $dsn, $matches);
        
        $host = $matches[1];
        $dbName = $matches[2];
        $username = $db->username;
        $password = $db->password;

        // Define backup file name and path
        $backupDir = Yii::getAlias('@app') . '/backups';
        $backupFile = $backupDir . '/' . $dbName . '_backup_' . date('Y-m-d_H-i-s') . '.sql';

        // Check if the backups directory exists, if not, create it
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);  // Create directory with permissions
        }

        // Create backup command
        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$dbName} > {$backupFile}";

        // Execute the command
        system($command, $output);

        if ($output === 0) {
            echo "Database backup completed: {$backupFile}\n";
        } else {
            echo "Database backup failed.\n";
        }
    }
}