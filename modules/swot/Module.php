<?php

namespace app\modules\swot;

/**
 * swot module definition class
 *
 * เครื่องมือวิเคราะห์เชิงกลยุทธ์ SWOT & SOAR (คลังการวิเคราะห์หลายเรื่อง)
 * standalone — ยังไม่ผูกกับ plan/pm (มี org_unit_id เตรียมไว้เท่านั้น)
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\swot\controllers';

    public function init()
    {
        parent::init();
    }
}
