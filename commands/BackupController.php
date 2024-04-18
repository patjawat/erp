<?php
namespace app\commands;

use Yii;
class BackupController extends \yii\console\Controller
{
    public function actionIndex()
    {
        // system("rm -rf " . escapeshellarg($dir . '/' . $object));
        // $backup = \Yii::$app->backup;
        // $databases = ['db'];
        // foreach ($databases as $k => $db) {
        //     $index = (string)$k;
        //     $backup->fileName = 'myapp-part';
        //     $backup->fileName .= str_pad($index, 3, '0', STR_PAD_LEFT);
        //     $backup->directories = [];
        //     $backup->databases = [$db];
        //     $file = $backup->create();
        //     $this->stdout('Backup file created: ' . $file . PHP_EOL, \yii\helpers\Console::FG_GREEN);
        // }
    }
    // public function actionIndex()
    // {
    //     /** @var \demi\backup\Component $backup */
    //     $backup = Yii::$app->backup;
        
    //     $file = $backup->create();

    //     $this->stdout('Backup file created: ' . $file . PHP_EOL, \yii\helpers\Console::FG_GREEN);
    // }

}