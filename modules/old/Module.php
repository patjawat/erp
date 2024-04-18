<?php

namespace app\modules\old;
use Yii;
/**
 * v2 module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\v2\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        Yii::$app->view->theme = new \yii\base\Theme([
            'pathMap' => [ '@app/views' => '@app/themes/v1'],
        ]);
        // custom initialization code goes here
    }
}
