<?php

namespace app\modules\sm\controllers;

use Yii;
use yii\web\Controller;
use app\modules\sm\components\SmHelper;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use yii\web\Response;
/**
 * Default controller for the `sm` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionListOrder()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterwhere(['name' => 'order']);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/purchase/views/order/list', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        } else {
          
        return $this->render('pr_order_list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        }
    }

    public function actionDemo()
    {
        SmHelper::InitailSm();
    }

    public function actionClear()
    {
        SmHelper::Clear();
    }
    
}
