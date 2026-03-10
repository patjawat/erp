<?php

namespace app\modules\mobile;

/**
 * โมดูล mobile - แอปมือถือ (บริการออนไลน์), layout แบบ bottom nav + FAB
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\mobile\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->layout = 'main';

        // เมื่ออยู่ในโมดูล mobile ให้ redirect ไปหน้า login ของ mobile แทน /auth/login
        $pathInfo = \Yii::$app->request->pathInfo ?? '';
        if (strpos($pathInfo, 'mobile') === 0) {
            \Yii::$app->user->loginUrl = ['/mobile/auth/login'];
        }
    }
}
