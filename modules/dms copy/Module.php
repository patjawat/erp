<?php

namespace app\modules\dms;
use Yii;

/**
 * dms module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\dms\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Yii::$app->view->theme = new Yii\base\Theme([
        //     'pathMap' => ['@app/views' => '@app/modules/dms/'],
        // ]);
        // custom initialization code goes here
    }
}
