<?php
use yii\data\SqlDataProvider;
use kartik\grid\GridView;
use yii\helpers\Html;

//     $sql = "SELECT *,
//     (x3.total_days * price_days) as total_price,
//        (x3.days_x2 * price_days) as total_price2,
//        IF(ROUND(x3.price-(x3.days_x2 * price_days),2) <=1, 1,ROUND(x3.price-(x3.days_x2 * price_days),2)) as total
//    FROM (SELECT *,
//    ROUND(x2.price  /  (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL life YEAR,'%Y-%m-%d'),receive_date)),2) as price_days,
//    DATEDIFF(x2.date,x2.receive_date) as days_x2,
//    IF(x2.date_number = 1, DATEDIFF(date,receive_date),x2.days_of_month) as total_days
   
//    FROM (select *,
   
//     DAYOFMONTH(LAST_DAY(DATE_FORMAT(date, '%Y-%m-%d'))) as days_of_month,
//                            ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY(date))+1)) as date_number
//                            FROM (
//                            SELECT 
//                            a.id,
//                            i.title,
//                            a.code,
//                            asset_type.title as type_name,
//                            asset_type.code as type_code,
//                            a.data_json->'$.service_life' as life,
//                            CAST(a.data_json->'$.depreciation'as DECIMAL(4,2)) as depreciation,
//                            asset_group,
//                            receive_date,
//                            ('".$model['date']."') as date,
//                            price,
//    asset_status,
//                            (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as all_days,
//                            (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
//                            (price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12) as month_price
                               
//                            FROM asset a
//                            LEFT JOIN categorise i ON i.code = a.asset_item
//                            LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
//                            ) as xx) as x2) as x3 WHERE   x3.receive_date <= x3.date AND x3.receive_date <= x3.date AND x3.asset_status = 1 AND x3.type_code = '".$model['type_code']."' ";


$sql = "SELECT x5.*,
(x5.total+x5.month_price) as price_last_month
FROM(
SELECT x4.*,
IF((x4.price - total_price) < 1,1,ROUND((x4.price - total_price),2)) as total
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
                           ('".$model['date']."') as date,
                           price,
   asset_status,
                           (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as all_days,
                           (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
                           ROUND((price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12),2) as month_price
                               
                           FROM asset a
                           LEFT JOIN categorise i ON i.code = a.asset_item
                           LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
                           ) as x1) as x2) as x3 WHERE   x3.receive_date <= x3.date AND x3.receive_date <= x3.date AND x3.asset_status = 1) as x4) as x5 where x5.type_code = '".$model['type_code']."' ";
$count = Yii::$app->db->createCommand($sql)->queryAll();

$dataProvider = new SqlDataProvider([
'sql' => $sql,
'totalCount' => count($count),
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

?>
<?php
// echo $model['type_code'];

echo GridView::widget([
    'id' => 'kv-grid-demo',
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    // 'filterModel' => $searchModel,
    'columns' => require(__DIR__.'/_columns2.php'),        
    // 'columns' => [],        
    'headerContainer' => ['style' => 'top:50px', 'class' => 'kv-table-header'], // offset from top
    'floatHeader' => true, // table header floats when you scroll
    'floatPageSummary' => true, // table page summary floats when you scroll
    'floatFooter' => false, // disable floating of table footer
    'pjax' => true, // pjax is set to always false for this demo
    // parameters from the demo form
    'responsive' => false,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'hover' => true,
    'showPageSummary' => true,
    // set export properties
    'export' => [
        'fontAwesome' => true
    ],
    'exportConfig' => [
        'html' => [],
        'csv' => [],
        'txt' => [],
        'xls' => [],
        // 'pdf' => [],
        'json' => [],
    ],
    // set your toolbar
    'toolbar' =>  [
        [
            'content' =>
                Html::button('<i class="fas fa-plus"></i>', [
                    'class' => 'btn btn-success',
                    'title' =>'Add Book',
                    'onclick' => 'alert("This should launch the book creation form.\n\nDisabled for this demo!");'
                ]) . ' '.
                Html::a('<i class="fas fa-redo"></i>', ['grid-demo'], [
                    'class' => 'btn btn-outline-secondary',
                    'title'=>'Reset Grid',
                    'data-pjax' => 0, 
                ]), 
            'options' => ['class' => 'btn-group mr-2 me-2']
        ],
        '{export}',
        '{toggleData}',
    ],
    'toggleDataContainer' => ['class' => 'btn-group mr-2 me-2'],
    'persistResize' => false,
    'toggleDataOptions' => ['minCount' => 10],
    'itemLabelSingle' => 'book',
    'itemLabelPlural' => 'books'
]);
?>
