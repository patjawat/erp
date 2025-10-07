<?php

namespace app\modules\plan\controllers;

use Yii;
use app\models\Categorise;
use app\modules\plan\models\PlanOrder;
use app\modules\plan\models\PlanCategory;

class DepdropController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionPlanCategory($id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $categoryId = $parents[0];
                $out = PlanCategory::find()
                    ->select(['id' => 'code', 'name' => 'title'])
                    ->where(['name' => 'plan_category','category_id' => $categoryId])
                    ->asArray()
                    ->all();

                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

     public function actionPlanItem($id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $categoryId = $parents[0];
                $out = PlanItem::find()
                    ->select(['id' => 'code', 'name' => 'title'])
                    ->where(['category_id' => $categoryId])
                    ->asArray()
                    ->all();

                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }


    INV_01
     public function actionPlanOrder($id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $planItemId = $parents[0];
                return $planItemId;
                $out = PlanOrder::find()
                    ->select(['id' => 'id', 'name' => 'description'])
                    ->where(['plan_item_id' => $planItemId])
                    ->asArray()
                    ->all();

                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionGetPlanInfo($id)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $plan = PlanOrder::findOne($id);
        if ($plan) {
            return [
                'status' => 'success',
                'data' => $plan,
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลแผนที่เลือก',
            ];
        }
    }

}
