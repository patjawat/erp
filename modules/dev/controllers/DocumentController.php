<?php


namespace app\modules\dev\controllers; 

use yii\web\Controller;

class DocumentController extends Controller
{
    public function actionIndex()
    {
        // สั่งให้ไปดึงไฟล์จาก views/document/index.php มาแสดง
        return $this->render('index');
    }
}