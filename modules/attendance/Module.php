<?php

namespace app\modules\attendance;

/**
 * Attendance (บันทึกเวลาเข้างาน) module.
 * รองรับลงเวลา: สแกน QR, ถ่ายรูป, กดลงเวลา; ตรวจบริเวณ; อนุมัติหัวหน้า; นำเข้า CSV; เชื่อมตารางเวร (ภายหลัง).
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\attendance\controllers';

    public function init()
    {
        parent::init();
    }
}
