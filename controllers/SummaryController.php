<?php

namespace app\controllers;
use Yii;
use yii\web\Response;
use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;

class SummaryController extends \yii\web\Controller
{
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $totalEmployee = Employees::find()
        ->andWhere(['status' => 1])
        ->andWhere(['<>','id',1])
        ->count('id');
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
        return [
            'totalEmployee' => [
                'title' => 'จำนวนบุคลากรทั้งหมด',
                'total' => $totalEmployee
            ],
            'totalAssetPrice' => [
                'title' => 'มูลค่าทรัพย์สินทั้งหมด',
                'total' => isset($totalPrice) ? number_format($totalPrice,2) : 0
            ],
            'totalAssetPriceGroup2' => [
                'title' => 'มูลค่าสิ่งก่อสร้าง',
                'total' =>  isset($totalPriceGroup2) ? number_format($totalPriceGroup2,2) : 0
            ],
            'totalAssetPriceGroup3' => [
                'title' => 'มูลค่าครุภัณฑ์',
                'total' =>  isset($totalPriceGroup3) ? number_format($totalPriceGroup3,2) : 0
            ],
         
            'priceLastOfYear' =>    
            [
                'title' => 'มูลค่าครุภัณฑ์ (ย้อนหลัง 10 ปี)',
                'data' => $priceLastOfYear
            ],
            'totalGroup2' => number_format($totalGroup2,2),
            'totalGroup3' => number_format($totalGroup3,2),
        ];
    }

}
