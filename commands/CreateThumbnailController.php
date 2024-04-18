<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use Yii;
use yii\console\Controller;
use yii\imagine\Image;
use Imagine\Image\Box;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CreateThumbnailController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {

        $models = Uploads::find()->all();
        foreach ($models as $model) {
            try {
           
            $ref = $model->ref;
            $realFileName = $model->real_filename;
            $filepath = FileManagerHelper::getUploadPath() . $model->ref . '/' . $realFileName;
            if (FileManagerHelper::isImage($filepath)) {
            FileManagerHelper::createThumbnail($ref, $realFileName);
            echo $realFileName." Success \n";
        }else{
            echo "Not Image";
        }
             //code...
            } catch (\Throwable $th) {
                //throw $th;
            }


        }
    }
    public function actionClear()
    {

        $models = Uploads::find()->all();
        foreach ($models as $model) {
            $filename = $model->real_filename;
            $filepath = FileManagerHelper::getUploadPath() . $model->ref . '/' . $filename;
            $thumbnail = FileManagerHelper::getUploadPath() . $model->ref . '/thumbnail/' . $filename;
            if (FileManagerHelper::isImage($filepath)) {
                @unlink($thumbnail);
            } else {

            }
        }
    }

}
