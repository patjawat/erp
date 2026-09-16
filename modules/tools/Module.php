<?php

namespace app\modules\tools;

/**
 * tools module — หน้า hub รวม "เครื่องมือ" ข้ามสายงาน (SWOT, ผังกระบวนการ, ฯลฯ)
 * เป็นบ้านกลางของเครื่องมือช่วยคิด/วิเคราะห์/จัดทำเอกสาร ที่ไม่ผูกกับหน่วยงานใดหน่วยงานหนึ่ง
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\tools\controllers';

    public function init()
    {
        parent::init();
    }
}
