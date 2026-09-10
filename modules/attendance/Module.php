<?php

namespace app\modules\attendance;

/**
 * Attendance (บันทึกเวลาเข้างาน) module.
 * รองรับลงเวลา: สแกน QR, ถ่ายรูป, กดลงเวลา; ตรวจบริเวณ; อนุมัติหัวหน้า; นำเข้า CSV; เชื่อมตารางเวร (ภายหลัง).
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\attendance\controllers';

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) return false;
        \Yii::$app->view->registerCssFile('@web/css/attendance.css', ['depends' => [\app\assets\AppAsset::class]]);
        if (\Yii::$app->user->isGuest) {
            if (in_array($action->id, ['save','position','shifts'], true)) throw new \yii\web\UnauthorizedHttpException('กรุณาเข้าสู่ระบบใหม่');
            \Yii::$app->user->loginRequired();
            return false;
        }
        $controller = $action->controller->id;
        $id = $action->id;
        $manager = \Yii::$app->user->can('admin') || \Yii::$app->user->can('hr');
        if (($controller === 'location' || ($controller === 'checkin' && in_array($id, ['import-csv', 'import-form', 'delete'], true))
            || ($controller === 'default' && $id === 'qr-code')) && !$manager) {
            throw new \yii\web\ForbiddenHttpException('สำหรับผู้ดูแลระบบลงเวลาเท่านั้น');
        }
        $writes = ['default' => ['save', 'position', 'upload-photo'], 'location' => ['delete'], 'checkin' => ['delete', 'import-csv']];
        if (in_array($id, $writes[$controller] ?? [], true) && !\Yii::$app->request->isPost) {
            throw new \yii\web\MethodNotAllowedHttpException('อนุญาตเฉพาะ POST');
        }
        return true;
    }

    public function init()
    {
        parent::init();
    }
}
