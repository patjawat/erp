<?php

use \kartik\datecontrol\Module;

//เพิ่ม module ที่นี่ที่เดียว
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
        Module::FORMAT_DATE => ['type' => 2, 'pluginOptions' => ['autoclose' => true]], // example
        Module::FORMAT_DATETIME => ['type' => 2, 'pluginOptions' => [
                'autoclose' => true,
                'todayHighlight' => true,
                'todayBtn' => true,
            ]],
        Module::FORMAT_TIME => [],
    ],]; //Oh

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
$modules['gridview'] = ['class' => '\kartik\grid\Module']; //system
$modules['admins'] = ['class' => 'mdm\admin\Module']; // จัดการระบ
$modules['gridviewKrajee'] = ['class' => '\kartik\grid\Module']; //system
$modules['usermanager'] = ['class' => 'app\modules\usermanager\Usermanager']; //จัดการผู้ใช้งานระบบ
$modules['rbac'] = ['class' => 'dektrium\rbac\RbacWebModule']; // จัดการสิทธิของผู้ใช้งาน
$modules['settings'] = ['class' => 'app\modules\settings\Module']; // การตั้งค่า
$modules['filemanager'] = ['class' => 'app\modules\filemanager\Module']; //ระบบจัดการ file
$modules['employees'] = ['class' => 'app\modules\employees\Module']; // ข้อมูลพนักงาน
$modules['sm'] = ['class' => 'app\modules\sm\Module']; // งานพัสดุ
$modules['old'] = ['class' => 'app\modules\old\Module']; // theme dev
$modules['hr'] = ['class' => 'app\modules\hr\Module']; // HRMS
$modules['pm'] = ['class' => 'app\modules\pm\Module']; // แผนงานและโครงการ (Project mansgement)
$modules['am'] = ['class' => 'app\modules\am\Module']; // งานทรัพย์สิน
$modules['warehouse'] = ['class' => 'app\modules\warehouse\Module']; // คลัง
$modules['stock'] = ['class' => 'app\modules\stock\Module']; // คลัง by โอ๋
$modules['helpdesk'] = ['class' => 'app\modules\helpdesk\Module']; //บริการช่วยเหลือ (งานซ่อม)
$modules['backoffice'] = ['class' => 'app\modules\backoffice\Module']; // backoffice เดิม
$modules['treemanager'] = ['class' => '\kartik\tree\Module']; // Tree Module
$modules['roundSwitch'] = ['class' => 'nickdenry\grid\toggle\Module']; // Tree Module

return $modules;
