<?php

namespace app\modules\amSurvey;

use Yii;

/**
 * Asset Survey Module – การสำรวจครุภัณฑ์ประจำปี
 * Handles survey campaigns, CSV/Excel upload, web survey, QR scan; does not replace am module.
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\amSurvey\controllers';

    public function init()
    {
        parent::init();
    }
}
