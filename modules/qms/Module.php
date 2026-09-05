<?php

namespace app\modules\qms;

/**
 * QMS — ระบบติดตามมาตรฐานโรงพยาบาล (Quality Management System)
 *
 * เก็บทะเบียนมาตรฐาน + ข้อกำหนดรายปี ติดตามความครบถ้วนของหลักฐาน
 * โดยหลักฐานผูก (tag) กลับไปยังเอกสารต้นทางในโมดูลเดิม (DMS / medsop) หรือแนบไฟล์เอง
 *
 * สถานะ: โครงเปล่า (scaffold) — ยังไม่มีตาราง/โมเดล รอออกแบบ migration เฟส 1
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\qms\controllers';
    public $defaultRoute = 'default/index';
}
