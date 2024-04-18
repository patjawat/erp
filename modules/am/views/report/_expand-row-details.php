<?php
use yii\data\SqlDataProvider;
use kartik\grid\GridView;
use yii\helpers\Html;
$d1 = '2071-10-31';
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
                ) as xx ) as x2 where x2.type_code = '".$model['type_code']."'";

    $sql = "SELECT x2.* FROM( select *,
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
            ((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY('2017-10-31'))+1)) as date_number,
            (DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(a.data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date)) as day_number,
            (price/CAST(a.data_json->'$.service_life' as UNSIGNED)) as price_year,
            (price/CAST(a.data_json->'$.service_life' as UNSIGNED) / 12) as month_price
            FROM asset a
            LEFT JOIN categorise i ON i.code = a.asset_item
            LEFT JOIN categorise asset_type ON i.category_id = asset_type.code AND asset_type.name = 'asset_type'
            ) as xx) as x2 where  x2.type_code = '".$model['type_code']."' ";

// $querys = Yii::$app->db->createCommand($sql)
// ->bindValue(':q_date',$d1)
// ->queryScalar();


$count = Yii::$app->db->createCommand($sqlCount)->bindValue(':q_date',$d1)->queryScalar();

$dataProvider = new SqlDataProvider([
'sql' => $sql,
// 'params' => [':q_date',$d1],
'totalCount' => 1000,
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
