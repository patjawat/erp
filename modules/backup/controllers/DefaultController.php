<?php

namespace app\modules\backup\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\FileHelper;
use yii\web\Response;

class DefaultController extends Controller
{
    public $backupPath = '@app/runtime/backup';
    public $fileUploadPath = '@app/modules/filemanager/fileupload';

    public function actionIndex()
    {
        // แสดงรายการไฟล์ backup
        $backupFiles = FileHelper::findFiles(Yii::getAlias($this->backupPath), [
            'only' => ['*.gz', '*.sql.gz'],
            'recursive' => false,
        ]);

        return $this->render('index', [
            'backupFiles' => $backupFiles,
        ]);
    }
public function actionBackupAll()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $backupPath = Yii::getAlias($this->backupPath);
    $dbTempDir = $backupPath . '/database';
    $fileTempDir = $backupPath . '/fileupload';

    // สร้าง directory ชั่วคราว
    foreach([$dbTempDir, $fileTempDir] as $dir){
        if(!is_dir($dir)) mkdir($dir, 0777, true);
    }

    $date = date('Y-m-d_H-i-s');
    $archiveFile = "$backupPath/backup_all_{$date}.tar.gz";

    // 1️⃣ Backup Database
    $db = Yii::$app->db;
    preg_match('/host=([^;]+)/', $db->dsn, $matches); $host = $matches[1];
    preg_match('/dbname=([^;]+)/', $db->dsn, $matches); $dbname = $matches[1];

    $dbFile = "$dbTempDir/{$dbname}_{$date}.sql";
    $mysqldumpPath = '/usr/bin/mysqldump';
    $cmd = "$mysqldumpPath -h $host -u {$db->username} -p'{$db->password}' $dbname > $dbFile 2>&1";
    exec($cmd, $out, $ret);
    if($ret !== 0){
        return ['success'=>false, 'error'=>$out];
    }

    // 2️⃣ Backup File Upload
    $uploadPath = Yii::getAlias($this->fileUploadPath);
    exec("cp -r $uploadPath/. $fileTempDir/");

    // 3️⃣ บีบอัดทั้งสอง folder เป็น tar.gz
    $tarCmd = "tar -czf $archiveFile -C $backupPath database -C $backupPath fileupload";
    exec($tarCmd, $out2, $ret2);

    // 4️⃣ ลบโฟลเดอร์ชั่วคราว
    if(is_dir($dbTempDir)) $this->deleteDir($dbTempDir);
    if(is_dir($fileTempDir)) $this->deleteDir($fileTempDir);

    if($ret2 === 0){
        clearstatcache();
        $sizeBytes = filesize($archiveFile);
        $sizeText = $sizeBytes >= 1024*1024 ? round($sizeBytes/(1024*1024),2).' MB' :
                    ($sizeBytes >= 1024 ? round($sizeBytes/1024,2).' KB' : $sizeBytes.' B');

        return ['success'=>true, 'file'=>basename($archiveFile), 'size'=>$sizeText];
    } else {
        return ['success'=>false, 'error'=>$out2];
    }
}


public function actionBackupDatabase()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $backupPath = Yii::getAlias($this->backupPath); // runtime/backup
    $dbTempDir = $backupPath . '/database'; // โฟลเดอร์ชั่วคราว
    if(!is_dir($backupPath)) mkdir($backupPath, 0777, true);
    if(!is_dir($dbTempDir)) mkdir($dbTempDir, 0777, true);

    $date = date('Y-m-d_H-i-s');

    // 1️⃣ Backup Database
    $db = Yii::$app->db;
    preg_match('/host=([^;]+)/', $db->dsn, $matches); $host = $matches[1];
    preg_match('/dbname=([^;]+)/', $db->dsn, $matches); $dbname = $matches[1];

    $dbFile = "$dbTempDir/{$dbname}_{$date}.sql";
    $mysqldumpPath = '/usr/bin/mysqldump'; // ตรวจสอบ path จริงใน container

    $cmd = "$mysqldumpPath -h $host -u {$db->username} -p'{$db->password}' $dbname > $dbFile 2>&1";
    exec($cmd, $out, $ret);

    if($ret !== 0){
        return ['success' => false, 'error' => $out];
    }

    // 2️⃣ บีบอัด SQL เป็น .gz ใน runtime/backup
    $gzFile = "$backupPath/{$dbname}_{$date}.sql.gz";
    exec("gzip -c $dbFile > $gzFile");

    // 3️⃣ ลบไฟล์ SQL ชั่วคราวทั้งหมด
    if(is_dir($dbTempDir)){
        $this->deleteDir($dbTempDir);
    }

    clearstatcache();
    $sizeBytes = filesize($gzFile);
    $sizeText = $sizeBytes >= 1024*1024 ? round($sizeBytes/(1024*1024),2).' MB' :
                ($sizeBytes >= 1024 ? round($sizeBytes/1024,2).' KB' : $sizeBytes.' B');

    return [
        'success' => true,
        'file' => basename($gzFile),
        'size' => $sizeText
    ];
}

/**
 * ลบ directory แบบ recursive
 */




    // --- Backup File Upload ---
  public function actionBackupFiles()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $backupPath = Yii::getAlias($this->backupPath); // runtime/backup
    $fileTempDir = $backupPath . '/fileupload'; // โฟลเดอร์ชั่วคราว
    $uploadPath = Yii::getAlias($this->fileUploadPath); // modules/filemanager/fileupload

    // สร้าง directory ชั่วคราว
    if(!is_dir($fileTempDir)) mkdir($fileTempDir, 0777, true);

    // คัดลอกไฟล์ upload มายังโฟลเดอร์ชั่วคราว
    exec("cp -r $uploadPath/. $fileTempDir/");

    $date = date('Y-m-d_H-i-s');
    $fileArchive = "$backupPath/fileupload_{$date}.tar.gz";

    // บีบอัด directory fileupload ชั่วคราวเป็น tar.gz
    // เวลาแตกไฟล์จะอยู่ภายใต้โฟลเดอร์ fileupload
    $tarCmd = "tar -czf $fileArchive -C $backupPath fileupload";
    exec($tarCmd, $out, $ret);

    // ลบโฟลเดอร์ชั่วคราว fileupload
    if(is_dir($fileTempDir)){
        $this->deleteDir($fileTempDir);
    }

    if ($ret === 0) {
        clearstatcache();
        $sizeBytes = filesize($fileArchive);
        $sizeText = $sizeBytes >= 1024*1024 ? round($sizeBytes/(1024*1024),2).' MB' :
                    ($sizeBytes >= 1024 ? round($sizeBytes/1024,2).' KB' : $sizeBytes.' B');

        return ['success' => true, 'file' => basename($fileArchive), 'size' => $sizeText];
    } else {
        return ['success' => false, 'error' => $out];
    }
}

/**
 * ลบ directory แบบ recursive
 */
protected function deleteDir($dir)
{
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        if (is_dir($path)) {
            $this->deleteDir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}


    // --- Download backup file ---
    public function actionDownload($file)
    {
        $filePath = Yii::getAlias($this->backupPath) . DIRECTORY_SEPARATOR . $file;
        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        }
        throw new \yii\web\NotFoundHttpException("File not found: $file");
    }

    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $file = $this->request->post('file');

        $backupPath = Yii::getAlias($this->backupPath);
        $filePath = $backupPath . DIRECTORY_SEPARATOR . $file;

        if (file_exists($filePath)) {
            if (is_writable($filePath) && unlink($filePath)) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Cannot delete file. Permission denied?'];
            }
        } else {
            return ['success' => false, 'error' => 'File not found: ' . $filePath];
        }
    }
}
