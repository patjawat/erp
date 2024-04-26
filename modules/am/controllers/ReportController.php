<?php

namespace app\modules\am\controllers;

use Yii;
use yii\data\SqlDataProvider;
use yii\web\Response;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;

class ReportController extends \yii\web\Controller
{
    public function actionIndex()
    {

        
        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        
        if($searchModel->q_year !='' && $searchModel->q_month !=''){
            $d1 = ($searchModel->q_year - 543).'-'.$searchModel->q_month.'-01';
            $queryMonth = Yii::$app->db->createCommand('SELECT LAST_DAY(:d1)')
            ->bindValue(':d1',$d1)
            ->queryScalar();
            $searchModel->q_lastDay = $queryMonth;
        }else{
            $queryMonth = Yii::$app->db->createCommand('SELECT LAST_DAY(now())')
            ->queryScalar();
            $searchModel->q_lastDay = $queryMonth;
        }
                        $sql_old = "SELECT *,
                        SUM(x3.total_days * price_days) as total_price,
                           SUM(x3.days_x2 * price_days) as total_price2,
                           SUM(IF(ROUND(x3.price-(x3.days_x2 * price_days),2) <=1, 1,ROUND(x3.price-(x3.days_x2 * price_days),2))) as total
                       FROM (SELECT *,
                       ROUND(x2.price  /  (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL life YEAR,'%Y-%m-%d'),receive_date)),2) as price_days,
                       DATEDIFF(x2.date,x2.receive_date) as days_x2,
                       IF(x2.date_number = 1, DATEDIFF(date,receive_date),x2.days_of_month) as total_days
                       
                       FROM (select *,
                       
                        DAYOFMONTH(LAST_DAY(DATE_FORMAT(date, '%Y-%m-%d'))) as days_of_month,
                                               ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY(date))+1)) as date_number
                                               FROM (
                                               SELECT 
                                               i.title,
                                               a.code,
                                               asset_type.title as type_name,
                                               asset_type.code as type_code,
                                               a.data_json->'$.service_life' as life,
                                               CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
                                               asset_group,
                                               receive_date,
                                                ('".$searchModel->q_lastDay."') as date,
                                               price,asset_status,
                                               (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as all_days,
                                               (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                                               (price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12) as month_price
                                                   
                                               FROM asset a
                                               LEFT JOIN categorise i ON i.code = a.asset_item
                                               LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                                               ) as xx) as x2) as x3 WHERE x3.receive_date <= x3.date AND x3.asset_status = 1 GROUP BY x3.type_code ORDER BY x3.type_code";


                                               $sql = "SELECT x5.*,
                                               SUM(x5.x_total+x5.month_price) as price_last_month,
                                               SUM(x5.x_total) as total
                                               FROM(
                                               SELECT x4.*,
                                               IF((x4.price - total_price) < 1,1,ROUND((x4.price - total_price),2)) as x_total
                                               FROM (
                                               SELECT x3.*,
                                               IF(x3.count_days > 15, ROUND(x3.date_number * ((x3.price / x3.service_life)/12),2),0) as total_price,
                                                      (x3.days_x2 * price_days) as total_price2
                                                  FROM (
                                                      SELECT x2.*,
                                                        
                                                  ROUND(x2.price  /  (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL x2.service_life YEAR,'%Y-%m-%d'),receive_date)),2) as price_days,
                                                  DATEDIFF(x2.date,x2.receive_date) as days_x2,
                                                  IF(x2.date_number = 1, DATEDIFF(date,receive_date),x2.days_of_month) as count_days
                                                        
                                                  FROM (select x1.*,
                                                  
                                                   DAYOFMONTH(LAST_DAY(DATE_FORMAT(date, '%Y-%m-%d'))) as days_of_month,
                                                                          ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY(date))+1)) as date_number
                                                                          FROM (
                                                                          SELECT 
                                                                          a.id,
                                                                          i.title,
                                                                          a.code,
                                                                          asset_type.title as type_name,
                                                                          asset_type.code as type_code,
                                                                          a.data_json->'$.service_life' as service_life,
                                                                          CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
                                                                          asset_group,
                                                                          receive_date,
                                                                          ('".$searchModel->q_lastDay."') as date,
                                                                          price,
                                                  asset_status,
                                                                          (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as all_days,
                                                                          (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                                                                          ROUND((price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12),2) as month_price
                                                                              
                                                                          FROM asset a
                                                                          LEFT JOIN categorise i ON i.code = a.asset_item
                                                                          LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                                                                          ) as x1) as x2) as x3 WHERE   x3.receive_date <= x3.date AND x3.receive_date <= x3.date AND x3.asset_status = 1) as x4) as x5 GROUP BY x5.type_code";
        $querys = Yii::$app->db->createCommand($sql)->queryAll();
        // ->bindValue(':q_date',$d1)
        // ->queryScalar();
        $data = [];
        foreach($querys as $query){
                $data[] = [
                    'total' => $query['total']
                ];
        }
        $count = count(Yii::$app->db->createCommand($sql)->queryAll());

$dataProvider = new SqlDataProvider([
    'sql' => $sql,
    'totalCount' => $count,
]);

    $totalPrice = 0;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'querys' => $querys,
            'queryMonth' => $queryMonth,
            'totalPrice' => $totalPrice
        ]);
    }

}