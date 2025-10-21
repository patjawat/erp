<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Response;
use yii\db\Expression;
use yii\web\Controller;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\WarehouseSearch;
use app\modules\inventory\models\StockEventSearch;

/**
 * Default controller for the `warehouse` module.
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module.
     *
     * @return string
     */


    public function actionIndex()
    {
        // clear session
        Yii::$app->session->remove('warehouse');

        //clear cart
        $cart = Yii::$app->cartSub;
        $cart->checkOut(false);

        $id = \Yii::$app->user->id;
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['delete' => null]);
        $dataProvider->query->andFilterWhere(['warehouse_type' => 'MAIN']);
        $dataProvider->query->andWhere(new Expression("JSON_CONTAINS(data_json->'\$.officer','\"$id\"')"));
        $dataProvider->query->orderBy(['warehouse_type' => SORT_ASC]);
        $dataProvider->pagination->pageSize = 100;

        if ($dataProvider->getTotalCount() == 1) {
            $setWarehouse = $dataProvider->getModels()[0];
            Yii::$app->session->set('warehouse', $setWarehouse);
            return $this->redirect(['/inventory/warehouse']);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionDashboard()
    {
        \Yii::$app->session->remove('warehouse');
        \Yii::$app->session->remove('selectMainWarehouse');
        \Yii::$app->cartMain->checkOut(false);
        \Yii::$app->cartSub->checkOut(false);
        $searchModel = new StockEventSearch([
            'thai_year' => AppHelper::YearBudget()
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        // เรียกครั้งเดียว
        $warehouseData = $this->Warehouse();

        $range = AppHelper::BudgetYearRange($searchModel->thai_year);
        $dateStart =  $range['start']; // 2025-10-01
        $dateEnd =  $range['end'];   // 2026-09-30


        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];
        $conditions = [
            "e.name = 'order'",
            "wi.warehouse_type = 'MAIN'",
            "i.asset_item IS NOT NULL",
        ];

        // ----- Auto GROUP / ORDER -----
        $groupBy = '';
        list($sql, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        $querys = Yii::$app->db->createCommand($sql, $params)->queryOne();

        return $this->render('dashboard', [
            'querys' => $querys,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchModelWarehouse' => $warehouseData['searchModelWarehouse'],
            'dataProviderWarehouse' => $warehouseData['dataProviderWarehouse'],
            'series' => $this->ProductSummary()['series'],
            'label' => $this->ProductSummary()['label'],
        ]);
    }
    protected function Warehouse()
    {
        $id = \Yii::$app->user->id;
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $query = $dataProvider->query;
        $query->where(['delete' => null]);

        // ดึงสิทธิ์ครั้งเดียว
        $isAdmin = \Yii::$app->user->can('admin');
        $isWarehouse = \Yii::$app->user->can('warehouse');

        if (!$isAdmin) {
            if ($isWarehouse) {
                // ใช้ parameter binding ปลอดภัยกว่า
                $query->andWhere(new Expression("JSON_CONTAINS(data_json->'$.officer', JSON_QUOTE(:id))", [':id' => (string)$id]));
            } else {
                $emp = UserHelper::GetEmployee();
                $query->andWhere(['warehouse_type' => 'SUB']);
                // FIND_IN_SET ไม่มี index — แนะนำเก็บ department แยกตาราง mapping
                $query->andWhere(new Expression('FIND_IN_SET(:dept, department)'), [':dept' => $emp->department]);
            }
        }

        $query->orderBy(['warehouse_type' => SORT_ASC]);
        $dataProvider->pagination->pageSize = 50; // ลดลงเล็กน้อย

        return [
            'searchModelWarehouse' => $searchModel,
            'dataProviderWarehouse' => $dataProvider,
        ];
    }






    // ปริมาณวัสดุตามหมวดหมู่
    protected function ProductSummary()
    {
        $sql = "SELECT pt.title,FORMAT(sum(s.qty*s.unit_price),2) as total FROM stock s INNER JOIN categorise p ON p.code = s.asset_item AND p.name = 'asset_item' INNER JOIN categorise pt ON pt.code = p.category_id AND pt.name = 'asset_type' GROUP BY pt.code";
        $querys = \Yii::$app->db->createCommand($sql)->queryAll();
        $series = [];
        $label = [];
        foreach ($querys as $item) {
            $series[] = $item['total'];
            $label[] = $item['title'];
        }

        return [
            'series' => $series,
            'label' => $label,
        ];
    }
}
