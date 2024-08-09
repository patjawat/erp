<?php

namespace app\modules\sm\controllers;

use Yii;
use yii\web\Controller;
use app\modules\sm\components\SmHelper;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use yii\web\Response;
use yii\db\Expression;
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


    // แสดงรายการขอซื้อ
    public function actionPrOrder()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andwhere(['is not', 'pr_number', null]);
        $dataProvider->query->andWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_director_confirm')"), ""]);
        $dataProvider->query->andFilterwhere(['name' => 'order']);
        $dataProvider->pagination->pageSize = 5;

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_order', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'title' => 'รายการขอซื้อ/ขอจ้าง',
                    'container' => 'pr-order'
                ]),
            ];
        } else {
            
            return $this->render('list_order', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'title' => 'รายการขอซื้อ/ขอจ้าง',
                 'container' => 'purchase-order'
            ]);
        }
    }


     // แสดงรายการขอซื้อที่อนุมัติแล้ว
     public function actionAcceptPrOrder()
     {
         $searchModel = new OrderSearch();
         $dataProvider = $searchModel->search($this->request->queryParams);
         $dataProvider->query->andwhere(['is not', 'pr_number', null]);
         $dataProvider->query->andFilterWhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_director_confirm')"), 'Y']);
         $dataProvider->query->andFilterwhere(['name' => 'order']);
         $dataProvider->query->andFilterwhere(['status' => '1']);
        $dataProvider->pagination->pageSize = 5;

         if ($this->request->isAjax) {
             Yii::$app->response->format = Response::FORMAT_JSON;
             return [
                 'title' => $this->request->get('title'),
                 'content' => $this->renderAjax('list_order', [
                     'searchModel' => $searchModel,
                     'dataProvider' => $dataProvider,
                     'title' => 'อนุมัติ',
                      'container' => 'pr-accept-order'
                 ]),
             ];
         } else {
 
             return $this->render('list_order', [
                 'searchModel' => $searchModel,
                 'dataProvider' => $dataProvider,
                 'title' => 'อนุมัติ',
                 'container' => 'pr-accept-order'
             ]);
         }
     }

      // แสดงรายการทะเบียนคุม
      public function actionPqOrder()
      {
          $searchModel = new OrderSearch();
          $dataProvider = $searchModel->search($this->request->queryParams);
          $dataProvider->query->andwhere(['is not', 'pq_number', null]);
          $dataProvider->query->andwhere(['status' => 2]);
          $dataProvider->query->andFilterwhere(['name' => 'order']);
          $dataProvider->pagination->pageSize = 5;
          
          if ($this->request->isAjax) {
              Yii::$app->response->format = Response::FORMAT_JSON;
              return [
                  'title' => $this->request->get('title'),
                  'content' => $this->renderAjax('list_order', [
                      'searchModel' => $searchModel,
                      'dataProvider' => $dataProvider,
                      'title' => 'ทะเบียนคุม',
                      'container' => 'pq-order'
                  ]),
              ];
          } else {
  
              return $this->render('list_order', [
                  'searchModel' => $searchModel,
                  'dataProvider' => $dataProvider,
                  'title' => 'ทะเบียนคุม',
                  'container' => 'pq-order'
              ]);
          }
      }

      

     

    // public function actionListOrder()
    // {
    //     $searchModel = new OrderSearch();
    //     $dataProvider = $searchModel->search($this->request->queryParams);
    //     $dataProvider->query->andFilterwhere(['name' => 'order']);

    //     if ($this->request->isAjax) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         return [
    //             'title' => $this->request->get('title'),
    //             'content' => $this->renderAjax('@app/modules/purchase/views/order/list', [
    //                 'searchModel' => $searchModel,
    //                 'dataProvider' => $dataProvider,
    //             ]),
    //         ];
    //     } else {
          
    //     return $this->render('pr_order_list', [
    //         'searchModel' => $searchModel,
    //         'dataProvider' => $dataProvider,
    //     ]);
    //     }
    // }

    public function actionDemo()
    {
        SmHelper::InitailSm();
    }

    public function actionClear()
    {
        SmHelper::Clear();
    }
    
}
