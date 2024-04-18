<?php

namespace app\modules\employees;
use Yii;
/**
 * employees module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\employees\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        Yii::$app->view->theme = new \yii\base\Theme([
            'pathMap' => ['@app/views' => '@app/themes/v2'],
        ]);
        // custom initialization code goes here
    }
}
