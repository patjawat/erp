<?php

namespace app\modules\am\controllers;

use Yii;
use yii\data\SqlDataProvider;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;

class ReportController extends \yii\web\Controller
{
    public function actionIndex()
    {

        
        $searchModel = new AssetSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        
        // if($searchModel->q_year !='' && $searchModel->q_month !=''){
            // $d1 = ($searchModel->q_year - 543).'-'.$searchModel->q_month.'-01';
            // $sqlMonth = "SELECT LAST_DAY(:d1) As date";
            $d1 = '2017-10-31';
            // $queryMonth = Yii::$app->db->createCommand('SELECT LAST_DAY(:d1)')
            // ->bindValue(':d1',$d1)
            // ->queryScalar();
      

        $sqlCount = "SELECT COUNT(*)  FROM(
            select xx.*,
                    ROUND(((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL life YEAR,'%Y-%m-%d'),date)),2) as price_days,
                     ROUND(((((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL life YEAR,'%Y-%m-%d'),date))*30) * date_number),2)  as price_days_X_datenum,
                            (date_number * month_price) as sum_price_month,
                            ROUND((xx.month_price * date_number),2) as total_month_price,
                            ROUND(IF(xx.price -(xx.month_price * date_number) <= 1,1,(xx.price -(xx.month_price * date_number))),2) as total,
                            DATE_FORMAT(xx.date,'%d') as days
                            FROM (
                            SELECT 
                            a.id,
                            i.title,
                            a.code,
                            asset_type.title as type_name,
                            asset_type.code as type_code,
                            a.data_json->'$.service_life' as life,
                            CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
                            asset_group,
                            receive_date as date,
                            price,
                            ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY(:q_date))+1)) as date_number,
                            (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as day_number,
                            (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                            (price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12) as month_price
                            FROM asset a
                            LEFT JOIN categorise i ON i.code = a.asset_item
                            LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                            ) as xx ) as x2;";

                $sql = "SELECT x2.*,
                SUM(x2.price) as sum_price,
                SUM(x2.total_month_price) as sum_total_month_price
                 FROM( select *,
                ROUND(((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL life YEAR,'%Y-%m-%d'),date)),2) as price_days,
                 ROUND(((((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL life YEAR,'%Y-%m-%d'),date))*30) * date_number),2)  as price_days_X_datenum,
                        (date_number * month_price) as sum_price_month,
                        ROUND((xx.month_price * date_number),2) as total_month_price,
                        ROUND(IF(xx.price -(xx.month_price * date_number) <= 1,1,(xx.price -(xx.month_price * date_number))),2) as total,
                        DATE_FORMAT(xx.date,'%d') as days
                        FROM (
                        SELECT 
                        i.title,
                        a.code,
                        asset_type.title as type_name,
                        asset_type.code as type_code,
                        a.data_json->'$.service_life' as life,
                        CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
                        asset_group,
                        receive_date as date,
                        price,
                        ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY('2024-04-30'))+1)) as date_number,
                        (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as day_number,
                        (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                        (price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12) as month_price
                        FROM asset a
                        LEFT JOIN categorise i ON i.code = a.asset_item
                        LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                        ) as xx ) as x2 GROUP BY x2.type_code ";

        // $querys = Yii::$app->db->createCommand($sql)
        // ->bindValue(':q_date',$d1)
        // ->queryScalar();


        $count = Yii::$app->db->createCommand($sqlCount)->bindValue(':q_date',$d1)->queryScalar();

$dataProvider = new SqlDataProvider([
    'sql' => $sql,
    // 'params' => [':q_date',$d1],
    'totalCount' => $count,
    // 'sort' => [
    //     'attributes' => [
    //         'age',
    //         'name' => [
    //             'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
    //             'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
    //             'default' => SORT_DESC,
    //             'label' => 'Name',
    //         ],
    //     ],
    // ],
    'pagination' => [
        'pageSize' => 20000,
    ],
]);



    $totalPrice = 0;
    // foreach($querys as $summaryItem){
    //     $totalPrice += (int)$summaryItem['total'];

    // }

    
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'querys' => $querys,
            // 'queryMonth' => $queryMonth,
            'totalPrice' => $totalPrice

        ]);
    }

}
