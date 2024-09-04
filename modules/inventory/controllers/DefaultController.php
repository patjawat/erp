<?php

namespace app\modules\inventory\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
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
        return $this->render('index');
    }

    public function actionChart()
    {

      
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
