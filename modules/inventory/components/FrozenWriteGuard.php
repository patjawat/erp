<?php

namespace app\modules\inventory\components;

use Yii;
use yii\web\Response;

/**
 * Big-bang freeze: บล็อก action ที่เปลี่ยน state ในระบบ Inventory V1 เมื่อ Module::isFrozen()
 * ใช้ร่วมกับ app\modules\inventory\Module::isFrozen() — controller ที่ใช้ trait นี้ต้อง implement frozenWriteActions()
 */
trait FrozenWriteGuard
{
    /**
     * @return string[] action id (kebab-case) ที่ต้องบล็อกเมื่อ frozen
     */
    abstract protected function frozenWriteActions(): array;

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (\app\modules\inventory\Module::isFrozen() && in_array($action->id, $this->frozenWriteActions(), true)) {
            $message = 'ระบบ Inventory V1 ปิดการสร้าง/แก้ไขเอกสารแล้ว — กรุณาใช้ Inventory V2';
            Yii::$app->session->setFlash('error', $message);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->data = ['status' => 'error', 'message' => $message];
            } else {
                Yii::$app->response->redirect(['/inventory-v2'])->send();
            }
            return false;
        }
        return true;
    }
}
