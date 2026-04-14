<?php

namespace app\modules\mobile;

use yii\base\BootstrapInterface;

/**
 * โมดูล mobile - แอปมือถือ (บริการออนไลน์), layout แบบ bottom nav + FAB
 */
class Module extends \yii\base\Module implements BootstrapInterface
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

    /**
     * ใช้หน้า error ของโมดูล mobile เมื่อคำขออยู่ภายใต้ /mobile/…
     */
    public function bootstrap($app)
    {
        if (!$app instanceof \yii\web\Application) {
            return;
        }
        $pathInfo = $app->request->pathInfo ?? '';
        $route = (string) $app->request->get('r', '');
        $isMobilePath = $pathInfo === 'mobile' || strncmp($pathInfo, 'mobile/', 7) === 0;
        $isMobileRoute = $route === 'mobile' || strncmp($route, 'mobile/', 7) === 0;
        if ($isMobilePath || $isMobileRoute) {
            $app->errorHandler->errorAction = 'mobile/error/error';
        }
    }
}
