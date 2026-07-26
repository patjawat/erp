<?php

namespace app\modules\health;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * health module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\health\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Yii::$app->user->isGuest
            || (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin'))) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าถึงระบบข้อมูลสุขภาพบุคลากร');
        }

        return true;
    }
}
