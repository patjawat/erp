<?php

namespace app\modules\me;

use app\components\SiteHelper;
use app\components\UserHelper;

/**
 * me module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\me\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $me = UserHelper::GetEmployee();
        $info = SiteHelper::getInfo();
        if ($me && !$me->pdpa && $info['active_pdpa'] == 1) {
            \Yii::$app->response->redirect(['/site/conditions-register'])->send();
            return;
        }
    }
}
