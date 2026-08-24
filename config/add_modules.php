<?php

use \kartik\datecontrol\Module;

// เพิ่ม module ที่นี่ที่เดียว
$modules = [];

$modules['datecontrol'] = [
    'class' => 'kartik\datecontrol\Module',
    'displaySettings' => [
        Module::FORMAT_DATE => 'dd/MM/yyyy',
        Module::FORMAT_TIME => 'hh:mm:ss a',
        Module::FORMAT_DATETIME => 'mm/dd/yyyy H:i:s',
    ],
    'saveSettings' => [
        Module::FORMAT_DATE => 'php:Y-m-d',
        Module::FORMAT_TIME => 'php:H:i:s',
        Module::FORMAT_DATETIME => 'php:Y-m-d H:i:s',
    ],
    'displayTimezone' => 'Asia/Bangkok',
    'autoWidget' => true,
    'autoWidgetSettings' => [
        Module::FORMAT_DATE => ['type' => 2, 'pluginOptions' => ['autoclose' => true]],  // example
        Module::FORMAT_DATETIME => ['type' => 2, 'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'todayBtn' => true,
        ]],
        Module::FORMAT_TIME => [],
    ],
];  // Oh

$modules['user'] = [
    'class' => 'dektrium\user\Module',
    'enableUnconfirmedLogin' => true,
    'confirmWithin' => 21600,
    'cost' => 12,
    'admins' => ['admin'],
    'controllerMap' => [
        'login' => [
            'class' => \dektrium\user\controllers\SecurityController::className(),
            'on ' . \dektrium\user\controllers\SecurityController::EVENT_AFTER_LOGIN => function ($e) {
                // Yii::$app->response->redirect(array('/user/security/login'))->send();
                Yii::$app->response->redirect(['/site/login'])->send();
                Yii::$app->end();
            },
        ],
    ],
];
$modules['gridview'] = ['class' => '\kartik\grid\Module'];  // system
$modules['admins'] = ['class' => 'mdm\admin\Module'];  // จัดการระบ
$modules['auth'] = ['class' => 'app\modules\auth\Module'];  // ระบยืนยันตัวตน
$modules['gridviewKrajee'] = ['class' => '\kartik\grid\Module'];  // system
$modules['usermanager'] = ['class' => 'app\modules\usermanager\Usermanager'];  // จัดการผู้ใช้งานระบบ
$modules['rbac'] = ['class' => 'dektrium\rbac\RbacWebModule'];  // จัดการสิทธิของผู้ใช้งาน
$modules['settings'] = ['class' => 'app\modules\settings\Module'];  // การตั้งค่า
$modules['filemanager'] = ['class' => 'app\modules\filemanager\Module'];  // ระบบจัดการ file
$modules['employees'] = ['class' => 'app\modules\employees\Module'];  // ข้อมูลพนักงาน
$modules['sm'] = ['class' => 'app\modules\sm\Module'];  // งานพัสดุ
$modules['old'] = ['class' => 'app\modules\old\Module'];  // theme dev
$modules['hr'] = ['class' => 'app\modules\hr\Module'];  // HRMS
$modules['pm'] = ['class' => 'app\modules\pm\Module'];  // แผนงานและโครงการ (Project mansgement)
$modules['am'] = ['class' => 'app\modules\am\Module'];  // งานทรัพย์สิน
$modules['amSurvey'] = ['class' => 'app\modules\amSurvey\Module'];  // การสำรวจครุภัณฑ์ประจำปี
$modules['line'] = ['class' => 'app\modules\line\Module'];  // line officail
$modules['inventory'] = ['class' => 'app\modules\inventory\Module', 'frozen' => false];  // คลัง (freeze ปิดชั่วคราว — ยังใช้งาน V1 คู่ขนานกับ V2 อยู่)
$modules['inventory-v2'] = ['class' => 'app\modules\inventoryV2\Module'];  // คลัง
$modules['sub-warehouse'] = ['class' => 'app\modules\SubWarehouse\Module'];  // คลังหน่วยงาน (คลังย่อย)
$modules['stock'] = ['class' => 'app\modules\stock\Module'];  // คลัง by โอ๋
$modules['helpdesk'] = ['class' => 'app\modules\helpdesk2\Module'];  // บริการช่วยเหลือ (งานซ่อม)
$modules['purchase'] = ['class' => 'app\modules\purchase\Module'];  // ระบบจัดซื้อ
$modules['me'] = ['class' => 'app\modules\me\Module'];  // โปรไฟล์ของฉัน
$modules['lm'] = ['class' => 'app\modules\lm\Module'];  // ระบบลา
$modules['dms'] = ['class' => 'app\modules\dms\Module'];  // document mannger system ระบบสารบรรณ
$modules['finance'] = ['class' => 'app\modules\finance\Module'];  // การเงิน
$modules['executive'] = ['class' => 'app\modules\executive\Module'];  // Dashboard ผู้บริหาร
$modules['accounting'] = ['class' => 'app\modules\accounting\Module'];  // บัญชี
$modules['backoffice'] = ['class' => 'app\modules\backoffice\Module'];  // backoffice เดิม
$modules['treemanager'] = ['class' => '\kartik\tree\Module'];  // Tree Module
$modules['roundSwitch'] = ['class' => 'nickdenry\grid\toggle\Module'];  // Tree Module
$modules['booking'] = ['class' => 'app\modules\booking\Module'];  // module การจอง
$modules['approve'] = ['class' => 'app\modules\approve\Module'];  // module การจอง
$modules['approve-v2'] = ['class' => 'app\modules\approveV2\Module'];  // module การจอง
$modules['approve-v3'] = ['class' => 'app\modules\approveV3\Module'];  // module อนุมัติ V3 (ตาราง approve เดิม)
$modules['telegrambot'] = ['class' => 'app\modules\telegrambot\Module'];  // module Telegram
$modules['formlayout'] = ['class' => 'app\modules\formlayout\Module'];  // module ออกแบบ โนพท pdf
$modules['plan'] = ['class' => 'app\modules\plan\Module'];  // แผนงานและโครงการ (Project management)
$modules['line2'] = ['class' => 'app\modules\line2\Module'];  // line officail 2 By PCH
$modules['health'] = ['class' => 'app\modules\health\Module'];  //ข้อมูลสุขภาพ
$modules['attendance'] = ['class' => 'app\modules\attendance\Module'];  // บันทึกเวลาเข้างาน (Check-in)
$modules['leave'] = ['class' => 'app\modules\leave\Module'];  // ระบบลา (ขอลา / อนุมัติ / ทางลัด)
$modules['appreciation'] = ['class' => 'app\modules\appreciation\Module'];  // พลังแห่งคำขอบคุณ (Appreciation Wall)
$modules['notify'] = ['class' => 'app\modules\notify\Module'];  // แจ้งเตือน (การขออนุมัติลา/จัดซื้อ/ลงเวลา ฯลฯ)
$modules['jd'] = ['class' => 'app\modules\jd\Module'];  // คำอธิบายงาน (JD) ต่อตำแหน่ง / พนักงาน
$modules['kpi'] = ['class' => 'app\modules\kpi\Module'];  // KPI ประจำปีรายบุคคล (seed จาก JD)
$modules['development'] = ['class' => 'app\modules\development\Module'];  // Development (เครื่องมือพัฒนา)
$modules['mobile'] = ['class' => 'app\modules\mobile\Module'];  // บริการออนไลน์ (แอปมือถือ)
$modules['pdf-template'] = ['class' => 'app\modules\pdfTemplate\Module'];  // PDF Template positioning (resolution-independent), URL: /pdf-template
$modules['medsop'] = ['class' => 'app\modules\medsop\Module'];  // ระบบคลังเอกสาร SOP/WI
$modules['am-survey'] = ['class' => 'app\modules\amSurvey\Module'];
$modules['ai'] = ['class' => 'app\modules\ai\Module'];
$modules['housing'] = ['class' => 'app\modules\housing\Module'];  // บ้านพัก
$modules['roster'] = ['class' => 'app\modules\roster\Module'];  // ตารางเวร (จัดเวรรายเดือนต่อหน่วยงาน)
$modules['service-profile'] = ['class' => 'app\modules\serviceProfile\Module'];  // Service Profile ประจำปีของหน่วยงาน
return $modules;
