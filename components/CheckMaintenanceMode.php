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

        // รายการหน้าที่จะไม่เช็ค (เพื่อป้องกัน Redirect Loop และให้แสดงหน้า error ได้)
        if (strpos($route, 'settings/') === 0 || in_array($route, ['site/login', 'site/warning', 'site/error'])) {
            return;
        }
        if (!Yii::$app->user->isGuest) {
            // 1. Call the check once and store the result in a variable
            $directorCheck = $this->checkDirector();

            if (!$directorCheck['status']) {
                // 2. Handle AJAX requests
                if (Yii::$app->request->isAjax) {
                    throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าใช้งานในส่วนนี้');
                }

                // 3. Set the flash message using the stored result
                Yii::$app->session->setFlash('error', $directorCheck['label']);

                // 4. Redirect and stop further execution
                Yii::$app->response->redirect(['site/warning'])->send();

                // Ensure the action doesn't run
                return $event->isValid = false;
            }
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
        if ($checkOver > 1 || $checkOver2 > 1) {
            return [
                'label' => $label,
                'desc' => 'ตรวจพบสิทธิผู้อำนวยการมีมากกว่า 1 คน',
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
