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
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order']);

       return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

// public function actionBudgetChart()
// {
//     Yii::$app->response->format = Response::FORMAT_JSON;
    // $sql = "SELECT b.code,b.title,IFNULL(sum(i.price * i.qty),0) AS total
    //     FROM categorise b
    //     LEFT JOIN orders o ON JSON_UNQUOTE(o.data_json->'$.pq_budget_type') = b.code
    //     LEFT JOIN orders as i ON i.category_id = o.id AND i.name = 'order_item'
    //     WHERE 
    //         b.`name` LIKE 'budget_type'
    //     AND b.code <> 8
    //         GROUP BY 
    //         b.code";
    //            $querys =   Yii::$app->db->createCommand($sql)
    //            ->queryAll();
    
//                $data = [];
//                $categorise = [];
//                foreach ($querys as $item) {
//                 $data[] = $item['total'];
//                 $categorise[] = $item['title'];
//                }
//                return [
//                 'data' => $data,
//                 'categorise' => $categorise
//                ];


// }
//     public function actionChart()
//     {
//         $data1  = [];
//         $data2  = [];
//         $data3  = [];

//         Yii::$app->response->format = Response::FORMAT_JSON;
      
//         $arr = [10,11,12,1,2,3,4,5,6,7,8,9];
                  
//         foreach ($arr as $key => $value) {
//                $data1[] = $this->getData1($value);
//                $data2[] = $this->getData2($value);
//                $data3[] = $this->getData3($value);  
//         }
//           return [
//             [
//                 'name' => 'วัสดุ',
//                 'data' => $data1,
//             ],
//             [

//                 'name' => 'ครุภัณฑ์',
//                 'data' => $data2,
//             ],
//             [
//                 'name' => 'จ้างเหมา',
//                 'data' => $data3
//                 ]
//           ];
//     }

//     //วัสดุ
//     private function getData1($month)
//     {

//         return  Yii::$app->db->createCommand("SELECT IFNULL(sum(i.price * i.qty),0) FROM orders o 
//                                                 INNER JOIN orders as i ON i.category_id = o.id AND i.name = 'order_item'
//                                                 INNER JOIN categorise item ON item.code = i.asset_item AND item.name = 'asset_item' WHERE o.group_id = 4 AND MONTH(i.created_at) = :month")
//         ->bindValue(':month',$month)
//         ->queryScalar();
//     }
// //ครุภัณฑ์
//     private function getData2($month)
//     {

//         return  Yii::$app->db->createCommand("SELECT IFNULL(sum(i.price * i.qty),0) FROM orders o 
//                                                 INNER JOIN orders as i ON i.category_id = o.id AND i.name = 'order_item'
//                                                 INNER JOIN categorise item ON item.code = i.asset_item AND item.name = 'asset_item' WHERE item.category_id = 3 AND MONTH(i.created_at) = :month")

//         ->bindValue(':month',$month)
//         ->queryScalar();
//     }
// //จ้างเหมา
//     private function getData3($month)
//     {

//         return  Yii::$app->db->createCommand("SELECT IFNULL(sum(i.price * i.qty),0) FROM orders o 
//                                                 INNER JOIN orders as i ON i.category_id = o.id AND i.name = 'order_item'
//                                                 INNER JOIN categorise item ON item.code = i.asset_item AND item.name = 'asset_item' WHERE item.category_id = 'M25' AND MONTH(i.created_at) = :month")
//         ->bindValue(':month',$month)
//         ->queryScalar();
//     }



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
