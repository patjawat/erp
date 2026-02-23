<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Controller;
use app\components\UserHelper;

/**
 * คู่มือให้งาน - หน้าแสดงคู่มือการใช้งานระบบสำหรับบุคลากร
 */
class GuideController extends Controller
{
    /**
     * หน้าหลักคู่มือให้งาน
     */
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if ($me === null) {
            return $this->redirect(['/me/default/index']);
        }

        return $this->render('index');
    }
}
