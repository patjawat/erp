<?php

namespace app\modules\me\controllers;

use Yii;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;
use yii\db\Expression;
use yii\web\Response;
use app\components\UserHelper;

class OwnerController extends \yii\web\Controller
{
    public function actionIndex()
    {

        $emp = UserHelper::GetEmployee();
        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['owner' => $emp->cid]);
        // $dataProvider->pagination = ['pageSize' => 20];
        // $dataProvider->query->where(['=', new Expression("JSON_EXTRACT(data_json, '$.checker')"),  (string) Yii::$app->user->id]);

        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('asset', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('asset', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

}
