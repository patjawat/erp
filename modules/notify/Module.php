<?php

namespace app\modules\notify;

/**
 * Notify module.
 * แจ้งเตือนให้รับทราบเหตุการณ์ เช่น การขออนุมัติลา การขออนุมัติจัดซื้อ การขออนุมัติลงเวลาเข้างาน
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\notify\controllers';

    public function init()
    {
        parent::init();
    }
}
