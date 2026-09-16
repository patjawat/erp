<?php

namespace app\modules\km;

/**
 * โมดูล KM — คลังกิจกรรม/หลักฐานการดำเนินงาน (Knowledge Management)
 *
 * แกนคือ "กิจกรรม" ของหน่วยงาน แนบรูปเป็นชุด แล้วชี้ (link) ไปหาหลักฐาน
 * ที่มีอยู่แล้วในระบบ — งาน (task), KPI, ความเสี่ยง (iacRisk), เอกสาร (dms/medsop)
 * โดยไม่คัดลอกของซ้ำเข้ามาเก็บเอง เป็น "evidence hub" ของงานคุณภาพ (HA)
 *
 * อยู่โซนเมนู "งานคุณภาพ" คู่กับ QMS (มาตรฐานโรงพยาบาล)
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\km\controllers';

    public function init()
    {
        parent::init();
    }
}
