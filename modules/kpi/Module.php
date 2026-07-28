<?php

namespace app\modules\kpi;

/**
 * KPI ประจำปีรายบุคคล
 * seed จาก JD, บันทึกผลรายงวด, สรุปคะแนนถ่วงน้ำหนักรอบ 6 เดือน
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\kpi\controllers';

    public function init()
    {
        parent::init();
    }
}
