<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\db\Expression;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use \yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use yii\helpers\BaseFileHelper;
use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\filemanager\models\Uploads;
use app\modules\backoffice\models\AssetArticle;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ClearAssetController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        //ลบครุภัณฑ? computer
        $querys = Asset::find()->leftJoin('categorise at', 'at.code=asset.asset_item')
        ->andWhere(['at.category_id' =>12])->all();

        foreach ($querys as $key => $item) {
            // try {
            $model = Uploads::find()->where(['ref' => $item->ref])->one();
            if($model){
                    FileManagerHelper::Deletefile($model->id);
            }
            if($item->delete()){
                echo $item->id." Success! \n";
            }
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }
        }
    }
        
    public static function CreateDir($folderName)
    {
        if ($folderName != null) {
            $basePath = Yii::getAlias('@app') . '/modules/filemanager/fileupload/';
            if (BaseFileHelper::createDirectory($basePath . $folderName, 0777)) {
                BaseFileHelper::createDirectory($basePath . $folderName . '/thumbnail', 0777);
            }
        }
        return;
    }
}
