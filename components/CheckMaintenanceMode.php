<?php

namespace app\components;

use Yii;
use yii\helpers\Url;
use yii\base\Behavior;
use yii\web\Application;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

class CheckMaintenanceMode extends Behavior
{

    public function events()
    {
        return [
            // ดักตอนเริ่ม Request ก่อนที่จะเริ่มรัน Controller Action
            Application::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }
    public function beforeAction($event)
    {
        $action = $event->action;
        $route = $action->uniqueId; // เช่น 'settings/user/index'
        $controllerId = $action->controller->id; // เช่น 'settings' หรือ 'settings/user'

        // รายการหน้าที่จะไม่เช็ค (เพื่อป้องกัน Redirect Loop)
        $excluded = ['site/login', 'site/warning', 'site/error'];
  if (strpos($route, 'settings/') === 0 || in_array($route, ['site/login', 'site/warning'])) {
    return;
}
        if (!$this->checkDirector()['status']) {
            if (Yii::$app->request->isAjax) {
                throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าใช้งานในส่วนนี้');
            }

            // เก็บข้อความไว้ใน Flash Session
            Yii::$app->session->setFlash('error', $this->checkDirector()['label']);

            Yii::$app->response->redirect(['site/warning'])->send();
            $event->isValid = false;
        }
    }

    public function afterAction($action, $result)
    {
        // ทำงานหลังจาก Action รันเสร็จแล้ว
        return parent::afterAction($action, $result);
    }



    public static function checkList()
    {
        return [
            'company' => self::checkDirector()
        ];
    }
    public static function checkDirector()
    {
        $checkOver = Yii::$app->db->createCommand("SELECT count(*)  FROM `auth_assignment` WHERE `item_name` = 'director'")->queryScalar();
        $checkOver2 = Yii::$app->db->createCommand("SELECT count(*) FROM `auth_item_child` WHERE `child` LIKE 'director'")->queryScalar();
        $label = "ตรวจสอบการตั้งค่าองค์กร";
        if ($checkOver > 1 || $checkOver2 > 0) {
            return [
                'label' => $label,
                'desc' => 'ครวจพบสิทธิผู้อำนวยการมีมากกว่า 1 คน',
                'url' => Url::to(['/settings/company']),
                'status' => false,

            ];
        } else {
            return [
                 'label' => $label,
                'desc' => 'สำเร็จ',
                'status' => true
            ];
        }
    }
}
