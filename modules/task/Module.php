<?php

namespace app\modules\task;

/**
 * โมดูลงานมอบหมาย (To Do)
 *
 * รวมงานจากทุกต้นทางไว้ที่เดียว ไม่ว่าจะมาจากหนังสือใน DMS
 * จากโครงการ หรือสั่งงานตรง ผู้ใช้เปิดดูที่ /task ที่เดียว
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\task\controllers';

    public function init()
    {
        parent::init();
    }
}
