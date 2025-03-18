<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\db\Expression;
use yii\web\Controller;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockSearch;
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
        $items = $cart->getItems();
        $cart->checkOut(false);


        $id = \Yii::$app->user->id;
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['delete' => null]);
        $dataProvider->query->andWhere(new Expression("JSON_CONTAINS(data_json->'\$.officer','\"$id\"')"));
        $dataProvider->query->orderBy(['warehouse_type' => SORT_ASC]);
        $dataProvider->pagination->pageSize = 100;
        
        if($dataProvider->getTotalCount() == 1){
            $setWarehouse = $dataProvider->getModels()[0];
            Yii::$app->session->set('warehouse',$setWarehouse);
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
            'thai_year' => AppHelper::YearBudget(),
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchModelWarehouse' => $this->Warehouse()['searchModelWarehouse'],
            'dataProviderWarehouse' => $this->Warehouse()['dataProviderWarehouse'],
            'series' => $this->ProductSummary()['series'],
            'label' => $this->ProductSummary()['label'],
        ]);
    }

    protected function Warehouse()
    {
        $id = \Yii::$app->user->id;
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['delete' => null]);
        if (!\Yii::$app->user->can('admin')) {
            if (\Yii::$app->user->can('warehouse')) {
                $dataProvider->query->andWhere(new Expression("JSON_CONTAINS(data_json->'$.officer','\"$id\"')"));
            } else {
                $emp = UserHelper::GetEmployee();
                $dataProvider->query->andWhere(['warehouse_type' => 'SUB']);
                $dataProvider->query->andWhere(['>', new Expression('FIND_IN_SET('.$emp->department.', department)'), 0]);
            }
        }
        $dataProvider->query->orderBy(['warehouse_type' => SORT_ASC]);
        $dataProvider->pagination->pageSize = 100;

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
