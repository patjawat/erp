<?php

namespace app\modules\jobdescription;

/**
 * Job Description (JD) module.
 * สร้าง template JD ต่อตำแหน่งงาน; พนักงานโหลด template ตามตำแหน่งและแก้ไข/เพิ่มเติมได้
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\jobdescription\controllers';

    public function init()
    {
        parent::init();
    }
}
