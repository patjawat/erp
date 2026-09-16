<?php

namespace app\modules\flowchart;

/**
 * flowchart module definition class
 *
 * เครื่องมือสร้างผังกระบวนการทำงานจากการ "ป้อนขั้นตอน" (ไม่ใช่การวาดมือ)
 * ป้อนขั้นตอน -> สร้างผัง Mermaid (auto-layout) + ตารางกระบวนการสำหรับพิมพ์เป็นเอกสาร ให้อัตโนมัติ
 * standalone — เผื่อ org_unit_id ไว้ให้โมดูลอื่น (medsop/qms/pm) อ้างผังในเฟสถัดไป
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\flowchart\controllers';

    public function init()
    {
        parent::init();
    }
}
