<?php

declare(strict_types=1);

namespace app\modules\housing;

final class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\housing\controllers';
    public $defaultRoute = 'dashboard/index';
}
