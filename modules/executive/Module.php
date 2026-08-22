<?php

namespace app\modules\executive;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\executive\controllers';

    /** ให้เข้า /executive เฉย ๆ แล้วไปหน้า Dashboard ได้ ไม่ต้องพิมพ์ /executive/dashboard/index */
    public $defaultRoute = 'dashboard';
}
