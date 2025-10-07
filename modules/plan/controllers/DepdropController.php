<?php

namespace app\modules\plan\controllers;

use Yii;
use app\models\Categorise;
use app\modules\plan\models\PlanOrder;

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
                $planGroupId = $parents[0];
                $out = PlanOrder::find()
                    ->select(['id' => 'plan_category_id', 'name' => 'description'])
                    ->where(['plan_group_id' => $planGroupId])
                    ->asArray()
                    ->all();

                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

     public function actionPlanOrder($id = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $planCategoryId = $parents[0];
                $out = PlanOrder::find()
                    ->select(['id' => 'id', 'name' => 'description'])
                    ->where(['plan_category_id' => $planCategoryId])
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
