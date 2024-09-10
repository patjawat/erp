<?php

namespace app\modules\inventory\controllers;

use app\components\UserHelper;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Expression;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\WarehouseSearch;

/**
 * Default controller for the `warehouse` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        Yii::$app->session->remove('warehouse');
        \Yii::$app->cart->checkOut(false);
        return $this->render('index');
    }


    public function actionWarehouse()
    {
        $id = Yii::$app->user->id;
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['delete' => null]);
        if (!Yii::$app->user->can('admin')) {
            if(Yii::$app->user->can('warehouse')){
                $dataProvider->query->andWhere(new Expression("JSON_CONTAINS(data_json->'$.officer','\"$id\"')"));

            }else{
                $emp = UserHelper::GetEmployee();
                $dataProvider->query->andWhere(['warehouse_type' =>'SUB']);
                $dataProvider->query->andWhere(['>', new \yii\db\Expression('FIND_IN_SET('.$emp->department.', department)'), 0]);
            }
        }
        $dataProvider->query->orderBy(['warehouse_type' => SORT_ASC]);


        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_warehouse', [
                    'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('list_warehouse', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

    }


    //ปริมาณวัสดุตามหมวดหมู่
    public function actionProductSummary()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $sql = "SELECT pt.title,count(s.id) as total FROM stock s 
            INNER JOIN categorise p ON p.code = s.asset_item AND p.name = 'asset_item'
            INNER JOIN categorise pt ON pt.code = p.category_id AND pt.name = 'asset_type'
            GROUP BY pt.code";
        $querys = Yii::$app->db->createCommand($sql)->queryAll();
        $series = [];
        $label = [];
        foreach ($querys as $item) {
            $series[] = (int)$item['total'];
            $label[] = $item['title'];
        }

        return [
            'series' => $series,
            'label' => $label
        ];
    }
}
