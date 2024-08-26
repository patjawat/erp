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

    public function actionChart()
    {
        // $sql1 = "SELECT thai_year,
        //         (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 10 ) as m10,
        //          (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 11 ) as m11,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 12 ) as m12,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 1 ) as m1,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 2 ) as m2,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 3 ) as m3,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 4 ) as m4,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 5 ) as m5,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 6 ) as m6,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 7 ) as m7,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 8 ) as m8,
        //           (SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = 9) as m9
        //         FROM orders GROUP BY thai_year;";
        $data1  = [];
        $data2  = [];
        $data3  = [];

        Yii::$app->response->format = Response::FORMAT_JSON;
      
        $arr = [10,11,12,1,2,3,4,5,6,7,8,9];
                  
        foreach ($arr as $key => $value) {
         
               $data1[] = $this->getData1($value);
               $data2[] = $this->getData2($value);
               $data3[] = $this->getData3($value);
           
        }

 
          return [
            [

                'name' => 'วัสดุ',
                'data' => $data1,
            ],
            [

                'name' => 'ครุภัณฑ์',
                'data' => $data2,
            ],
            [
                'name' => 'จ้างเหมา',
                'data' => $data3
                ]
          ];

    }

    //วัสดุ
    private function getData1($month)
    {

        return  Yii::$app->db->createCommand("SELECT count(id) FROM orders WHERE group_id = 4 AND MONTH(created_at) = :month")
        ->bindValue(':month',$month)
        ->queryScalar();
    }
//ครุภัณฑ์
    private function getData2($month)
    {

        return  Yii::$app->db->createCommand("SELECT count(id) FROM orders WHERE category_id != 'M25' AND MONTH(created_at) = :month")

        ->bindValue(':month',$month)
        ->queryScalar();
    }
//จ้างเหมา
    private function getData3($month)
    {

        return  Yii::$app->db->createCommand("SELECT count(id) FROM orders WHERE category_id = 'M25' AND MONTH(created_at) = :month")
        ->bindValue(':month',$month)
        ->queryScalar();
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
