<?php

namespace app\modules\roster;

/**
 * ตารางเวร (Duty Roster)
 *
 * หัวหน้าหน่วยงานจัดเวรรายเดือนบนกริด คน × วัน → ส่งอนุมัติ → ประกาศให้ทีมดู
 * เจ้าหน้าที่ยื่นคำขอหยุด/ขออยู่ล่วงหน้าผ่าน /me/roster แล้วหัวหน้าเห็นบนกริดตอนจัด
 *
 * เชื่อมกับระบบลงเวลา (ประเมินสาย/ขาดของคนขึ้นเวร) และค่าตอบแทนเวร — ภายหลัง
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\roster\controllers';

    public function init()
    {
        parent::init();
    }
}
