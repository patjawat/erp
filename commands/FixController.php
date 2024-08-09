<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeeDetail;
use app\modules\hr\models\Organization;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use yii\helpers\ArrayHelper;
use app\models\Categorise;
use DirectoryIterator;
use app\modules\filemanager\models\Uploads;

/**
 * แก้ไขรหัสตำแหน่งใหม่ v2
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FixController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex() {}
    public function actionEmployee()
    {
        if (BaseConsole::confirm("Are you sure?")) {
            $data = [];
            $employees = Employees::find()->all();
            foreach ($employees as $model) {
                if ($model->ref == '') {
                    $emp = Employees::findOne($model->id);
                    $emp->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                    if ($emp->save(false)) {
                        echo $emp->fname . "\n";
                    } else {
                        echo 'ผิดพลาด' . "\n";
                    }
                }
            }
        }
        // echo  $data;
    }

    public function actionAssetItem()
    {
        if (BaseConsole::confirm("Are you sure?")) {
            $data = [];
            $employees = Categorise::find()->where(['name' => 'asset_item'])->all();
            foreach ($employees as $model) {
                if ($model->ref == '') {
                    $item = Categorise::findOne($model->id);
                    $item->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                    if ($item->save(false)) {
                        echo $item->title . "\n";
                    } else {
                        echo 'ผิดพลาด' . "\n";
                    }
                }
            }
        }
        // echo  $data;
    }

    // public function actionClearUpload()
    // {
    //     $directoryPath = Yii::getAlias('@app') . '/modules/filemanager/fileupload/';


    //         // Usage



    //     // Create a DirectoryIterator instance
    //     $directory = new DirectoryIterator($directoryPath);

    //     // Loop through the directory
    //     foreach ($directory as $fileinfo) {
    //         // Skip the current (.) and parent (..) directory links
    //         if (!$fileinfo->isDot()) {
    //             $filename = $fileinfo->getFilename();
    //             $model = Uploads::findOne(['ref' => $filename]);
    //             if($model){
    //                 echo $fileinfo->getFilename() .  "\n";
    //             }else{
    //                 if ($this->deleteDirectory($directoryPath)) {
    //                     echo "Directory and its contents deleted successfully. \n";
    //                 } else {
    //                     echo "Failed to delete the directory. \n";
    //                 }
                    
    //             }
    //         }
    //     }
    // }

    // function deleteDirectory($dir) {
    //     if (!file_exists($dir)) {
    //         return false;
    //     }
    
    //     // Loop through the directory contents
    //     foreach (new DirectoryIterator($dir) as $fileinfo) {
    //         if ($fileinfo->isDot()) {
    //             continue;
    //         }
            
    //         // Recursively delete subdirectories
    //         if ($fileinfo->isDir()) {
    //             $this->deleteDirectory($fileinfo->getRealPath());
    //         } else {
    //             // Delete files
    //             unlink($fileinfo->getRealPath());
    //         }
    //     }
    
    //     // Delete the directory itself
    //     return rmdir($dir);
    // }
    


}
