<?php

namespace app\modules\backup\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\helpers\FileHelper;
use app\modules\backup\components\BackupHelper;

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
public function actionBackupDatabase()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    return BackupHelper::backupDatabase();
}

public function actionBackupFiles()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    return BackupHelper::backupFiles();
}

public function actionBackupAll()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    return BackupHelper::backupAll();
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
