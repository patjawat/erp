<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\Plan;
use app\modules\plan\models\PlanItem;

class PlanItemController extends Controller
{
    public function actionManage($plan_id)
    {
        $plan = Plan::findOne($plan_id);
        if (!$plan) {
            throw new NotFoundHttpException('ไม่พบแผน');
        }

        if (Yii::$app->request->isPost) {
            $items = Yii::$app->request->post('items', []);
            PlanItem::deleteAll(['plan_id' => $plan_id]); // เคลียร์รายการเก่า
            foreach ($items as $item) {
                if (!empty($item['item_name'])) {
                    $pi = new PlanItem();
                    $pi->plan_id = $plan_id;
                    $pi->item_name = $item['item_name'];
                    $pi->quantity = (int)$item['quantity'];
                    $pi->unit_price = (float)$item['unit_price'];
                    $pi->save(false);
                }
            }
            Yii::$app->session->setFlash('success', 'บันทึกรายการสำเร็จ');
            return $this->redirect(['/plan/plan/view', 'id' => $plan_id]);
        }

        $items = PlanItem::find()->where(['plan_id' => $plan_id])->all();

        return $this->render('manage', [
            'plan' => $plan,
            'items' => $items
        ]);
    }
}
