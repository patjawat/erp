<?php

namespace app\modules\ha12;

/**
 * HA12-PCT — ระบบทบทวน 12 กิจกรรม + สรุปประเมินโดย PCT
 *
 * พอร์ตจากระบบเดิม Google Apps Script + Google Sheets (รพ.แวงน้อย) มาเป็น Yii2/RDBMS
 * แกนคือการแยก "การทบทวนจริง" (ข้อมูลจากหน้างาน) ออกจาก "ผลสรุป/ประเมินของ PCT"
 * แต่เชื่อมกันด้วย id + revision และเก็บหลักฐานเป็น snapshot เฉพาะรุ่น
 *
 * อยู่โซนเมนู "งานคุณภาพ" ต่อจาก "มาตรฐานโรงพยาบาล" (qms)
 *
 * สถานะ: เฟส 0 — ทะเบียนกิจกรรม/เกณฑ์ + landing (ยังไม่มีการบันทึกทบทวน)
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\ha12\controllers';
    public $defaultRoute = 'default/index';
}
