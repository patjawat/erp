<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use app\modules\am\models\Asset;
/**
 * Default controller for the `am` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        //มูลค่าทรัพสินทั้งหมด
         $totalPrice = Asset::find()
        // ->where(['NOT IN','asset_status',[2]])
        ->sum('price');
        // มูลค่าสิ่งปลูกสร้าง
         $totalPriceGroup2 = Asset::find()
        ->where(['asset_group' => 2])
        // ->andWhere(['NOT IN','asset_status',[2]])
        ->sum('price');

         // มูลค่าครุภัณฑ?ทั้งหมด
        $totalPriceGroup3 = Asset::find()
        ->where(['asset_group' => 3])
        // ->andWhere(['NOT IN','asset_status',[2]])
        ->sum('price');

        //มูลค่าครุภัณฑ์ (ย้อนหลัง 5 ปี)
        $priceLastOfYear = Yii::$app->db->createCommand("SELECT ROUND(sum(price),0) as total ,on_year FROM `asset` WHERE asset_group = 3 GROUP by on_year ORDER BY on_year desc limit 10")->queryAll();
        $totalGroup2 = Yii::$app->db->createCommand("SELECT count(id) as total FROM asset WHERE asset_group = 2")->queryScalar();
        $totalGroup3 = Yii::$app->db->createCommand("SELECT count(id) as total FROM asset WHERE asset_group = 3")->queryScalar();
        return $this->render('index',[
            'totalPrice' => isset($totalPrice) ? $totalPrice : 0,
            'totalPriceGroup2' => isset($totalPriceGroup2) ? $totalPriceGroup2 : 0,
            'totalPriceGroup3' => isset($totalPriceGroup3) ? $totalPriceGroup3 : 0,
            'priceLastOfYear' => $priceLastOfYear,
            'totalGroup2' => $totalGroup2,
            'totalGroup3' => $totalGroup3,
        ]);
    }
}
