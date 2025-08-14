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
        Yii::$app->response->format = Response::FORMAT_JSON;

        $backupPath = Yii::getAlias($this->backupPath);
        if(!is_dir($backupPath)) mkdir($backupPath, 0777, true);

        $date = date('Y-m-d_H-i-s');
        $archiveFile = "$backupPath/backup_all_{$date}.tar.gz";

        // 1️⃣ Backup Database
        $db = Yii::$app->db;
        preg_match('/host=([^;]+)/', $db->dsn, $matches); $host = $matches[1];
        preg_match('/dbname=([^;]+)/', $db->dsn, $matches); $dbname = $matches[1];

        $dbFile = "$backupPath/{$dbname}_{$date}.sql";
        $mysqldumpPath = '/usr/bin/mysqldump'; // ตรวจสอบ path จริงใน container
        $cmd = "$mysqldumpPath -h $host -u {$db->username} -p'{$db->password}' $dbname > $dbFile 2>&1";
        exec($cmd, $out, $ret);
        if($ret !== 0){
            return ['success'=>false, 'error'=>$out];
        }

        // 2️⃣ รวม File Upload และ Database SQL เข้าใน .tar.gz
        $fileUploadPath = Yii::getAlias($this->fileUploadPath);
        $tarCmd = "tar -czf $archiveFile -C $backupPath " . basename($dbFile) . " -C $fileUploadPath .";
        exec($tarCmd, $out2, $ret2);

        // ลบไฟล์ SQL ชั่วคราว
        if(file_exists($dbFile)) unlink($dbFile);

        if($ret2 === 0){
            return ['success'=>true, 'file'=>basename($archiveFile)];
        } else {
            return ['success'=>false, 'error'=>$out2];
        }
    }
    
    // --- Backup Database ---
    public function actionBackupDatabase()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $backupPath = Yii::getAlias($this->backupPath);
        if (!is_dir($backupPath)) mkdir($backupPath, 0777, true);

        $db = Yii::$app->db;
        preg_match('/host=([^;]+)/', $db->dsn, $matches);
        $host = $matches[1];
        preg_match('/dbname=([^;]+)/', $db->dsn, $matches);
        $dbname = $matches[1];

        $date = date('Y-m-d_H-i-s');
        $dbFile = "$backupPath/{$dbname}_{$date}.sql";

        $mysqldumpPath = '/usr/bin/mysqldump'; // ตรวจสอบ path จริง
        $cmd = "$mysqldumpPath -h $host -u {$db->username} -p'{$db->password}' $dbname > $dbFile 2>&1";
        exec($cmd, $out, $ret);

        if ($ret === 0) {
            exec("gzip -f $dbFile");
            $dbArchive = "$dbFile.gz";
            return ['success' => true, 'file' => basename($dbArchive)];
        } else {
            return ['success' => false, 'error' => $out];
        }
    }


    // --- Backup File Upload ---
    public function actionBackupFiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $backupPath = Yii::getAlias($this->backupPath);
        if (!is_dir($backupPath)) mkdir($backupPath, 0777, true);

        $date = date('Y-m-d_H-i-s');
        $fileArchive = "$backupPath/fileupload_{$date}.tar.gz";
        $uploadPath = Yii::getAlias($this->fileUploadPath);

        // Backup ไฟล์
        exec("tar -czf $fileArchive -C $uploadPath .", $out, $ret);

        if ($ret === 0) {
            return ['success' => true, 'file' => basename($fileArchive)];
        } else {
            return ['success' => false, 'error' => $out];
        }
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
