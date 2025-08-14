<?php
namespace app\modules\backup\components;

use Yii;

class BackupHelper
{
    public static function backupDatabase(): array
    {
        $backupPath = Yii::getAlias('@app/runtime/backup');
        $dbTempDir = $backupPath . '/database';

        if (!is_dir($backupPath)) mkdir($backupPath, 0777, true);
        if (!is_dir($dbTempDir)) mkdir($dbTempDir, 0777, true);

        $date = date('Y-m-d_H-i-s');

        $db = Yii::$app->db;
        preg_match('/host=([^;]+)/', $db->dsn, $matches); $host = $matches[1];
        preg_match('/dbname=([^;]+)/', $db->dsn, $matches); $dbname = $matches[1];

        $dbFile = "$dbTempDir/{$dbname}_{$date}.sql";
        $mysqldumpPath = '/usr/bin/mysqldump';

        $cmd = "$mysqldumpPath -h $host -u {$db->username} -p'{$db->password}' $dbname > $dbFile 2>&1";
        exec($cmd, $out, $ret);
        if ($ret !== 0) return ['success' => false, 'error' => $out];

        $gzFile = "$backupPath/{$dbname}_{$date}.sql.gz";
        exec("gzip -c $dbFile > $gzFile");

        // ลบไฟล์ SQL ชั่วคราว
        self::deleteDir($dbTempDir);

        $sizeText = self::formatSize($gzFile);

        return ['success' => true, 'file' => basename($gzFile), 'size' => $sizeText];
    }

    public static function backupFiles(): array
    {
        $backupPath = Yii::getAlias('@app/runtime/backup');
        $fileTempDir = $backupPath . '/fileupload';
        $uploadPath = Yii::getAlias('@app/modules/filemanager/fileupload');

        if (!is_dir($fileTempDir)) mkdir($fileTempDir, 0777, true);

        exec("cp -r $uploadPath/. $fileTempDir/");

        $date = date('Y-m-d_H-i-s');
        $fileArchive = "$backupPath/fileupload_{$date}.tar.gz";

        exec("tar -czf $fileArchive -C $backupPath fileupload", $out, $ret);

        // ลบโฟลเดอร์ชั่วคราว
        self::deleteDir($fileTempDir);

        if ($ret === 0) {
            $sizeText = self::formatSize($fileArchive);
            return ['success' => true, 'file' => basename($fileArchive), 'size' => $sizeText];
        }

        return ['success' => false, 'error' => $out];
    }

    public static function backupAll(): array
    {
        $backupPath = Yii::getAlias('@app/runtime/backup');
        $dbDir = $backupPath . '/database';
        $fileDir = $backupPath . '/fileupload';

        if (!is_dir($dbDir)) mkdir($dbDir, 0777, true);
        if (!is_dir($fileDir)) mkdir($fileDir, 0777, true);

        $resDb = self::backupDatabase();
        $resFiles = self::backupFiles();

        $date = date('Y-m-d_H-i-s');
        $archiveFile = "$backupPath/backup_all_{$date}.tar.gz";

        exec("tar -czf $archiveFile -C $backupPath database -C $backupPath fileupload", $out, $ret);

        // ลบโฟลเดอร์ชั่วคราว
        self::deleteDir($dbDir);
        self::deleteDir($fileDir);

        if ($ret === 0) {
            $sizeText = self::formatSize($archiveFile);
            return ['success' => true, 'file' => basename($archiveFile), 'size' => $sizeText];
        }

        return ['success' => false, 'error' => $out];
    }

    protected static function deleteDir($dir)
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) self::deleteDir($path);
            else unlink($path);
        }
        rmdir($dir);
    }

    protected static function formatSize($file): string
    {
        $bytes = filesize($file);
        if ($bytes >= 1024*1024) return round($bytes/(1024*1024),2).' MB';
        if ($bytes >= 1024) return round($bytes/1024,2).' KB';
        return $bytes.' B';
    }
}
