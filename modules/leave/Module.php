<?php

namespace app\modules\leave;

/**
 * Leave module (ระบบลา)
 * จุดรวมการขอลา อนุมัติลา และทางลัดไปยัง HR/นโยบายการลา
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\leave\controllers';

    public function init()
    {
        parent::init();
    }
}
