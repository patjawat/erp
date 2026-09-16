<?php

namespace app\modules\complaint;

/**
 * โมดูล Complaint — ระบบรับเรื่องร้องเรียน
 *
 * พอร์ตจากระบบเดิม Google Apps Script + Sheets (รพ.แวงน้อย) เป็น Yii2
 * workflow 5 ขั้น: แจ้งเรื่อง → รับเรื่อง → ประเมิน → ดำเนินงาน → ปิดเคส
 * ขอบเขต: ภายในเท่านั้น (เจ้าหน้าที่บันทึกให้) — ไม่มีหน้าสาธารณะ
 *
 * อยู่โซนเมนู "งานคุณภาพ" คู่กับ QMS (มาตรฐาน) และ KM (คลังกิจกรรม)
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\complaint\controllers';

    public function init()
    {
        parent::init();
    }
}
