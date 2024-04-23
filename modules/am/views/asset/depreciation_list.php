<?php
use yii\helpers\Html;
?>
<?php if(isset($model->data_json['service_life'])):?>
<?php
    $year = $model->data_json['service_life'];
    $depre = $model->data_json['depreciation'];
    $price = $model->price;


    // $sqlxx = "select xx.*,
    // (xx.month_price * date_number) as total_month_price,
    // (xx.price -(xx.month_price * date_number)) as total,
    //   ROUND(((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL service_life YEAR,'%Y-%m-%d'),date)),2) as price_days,
    //   ROUND(((((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL service_life YEAR,'%Y-%m-%d'),date))* (IF(xx.date_number = 1,(DATEDIFF(DATE_FORMAT(date,'%Y-%m-%d'),xx.receive_date)),xx.days_of_month))) * date_number),2)  as price_days_to_month,
    // receive_date
    // from (select 
    // LAST_DAY(m1) as date,
    // IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
    // (TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id) ,LAST_DAY(m1))+1)  as date_number,
          
    // DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,  
    //  IF((TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id) ,LAST_DAY(m1))+1) = 1, (DATEDIFF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m-%d'),(SELECT receive_date FROM asset WHERE id = :id))),DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) ) as sum_days,
    // DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
    // (select price FROM asset where id =:id) as price,
    // (SELECT receive_date FROM asset WHERE id = :id) as receive_date,
    // (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) as service_life,
    // (SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = :id) as month_price,
    // (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = :id) as depreciation
    // from
    // (
    // select ((SELECT receive_date FROM asset WHERE id = :id) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = :id))-1 DAY) 
    // +INTERVAL m MONTH as m1
    // from
    // (
    // select @rownum:=@rownum+1 as m from
    // (select 1 union select 2 union select 3 union select 4) t1,
    // (select 1 union select 2 union select 3 union select 4) t2,
    // (select 1 union select 2 union select 3 union select 4) t3,
    // (select 1 union select 2 union select 3 union select 4) t4,
    // (select @rownum:=-1) t0
    // ) d1
    // ) d2 
    // where m1<= DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id) + INTERVAL 3 YEAR,'%Y-%m-%d')
    // order by m1)as xx;;";


    // $sqlold1 = "select xx.*,
    // ROUND((xx.month_price * date_number),2) as total_month_price,
    // ROUND((xx.price -(xx.month_price * date_number)),2) as total,
    //  IF(xx.date_number = 1,(DATEDIFF(DATE_FORMAT(date,'%Y-%m-%d'),xx.receive_date)),xx.days_of_month) as sum_days,
    //   ROUND(((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL service_life YEAR,'%Y-%m-%d'),date)),2) as price_days,
    //   ROUND(((((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL service_life YEAR,'%Y-%m-%d'),date))* (IF(xx.date_number = 1,(DATEDIFF(DATE_FORMAT(date,'%Y-%m-%d'),xx.receive_date)),xx.days_of_month))) * date_number),2)  as price_of_month,
    // receive_date
    // from (select 
    // LAST_DAY(m1) as date,
    // IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
    // (TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id) ,LAST_DAY(m1))+1)  as date_number,
    // DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
    // DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
    // (select price FROM asset where id =:id) as price,
    // (SELECT receive_date FROM asset WHERE id = :id) as receive_date,
    // (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) as service_life,
    // (SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = :id) as month_price,
    // (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = :id) as depreciation
    // from
    // (
    // select ((SELECT receive_date FROM asset WHERE id = :id) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = :id))-1 DAY) 
    // +INTERVAL m MONTH as m1
    // from
    // (
    // select @rownum:=@rownum+1 as m from
    // (select 1 union select 2 union select 3 union select 4) t1,
    // (select 1 union select 2 union select 3 union select 4) t2,
    // (select 1 union select 2 union select 3 union select 4) t3,
    // (select 1 union select 2 union select 3 union select 4) t4,
    // (select @rownum:=-1) t0
    // ) d1
    // ) d2 
    // where m1<= DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d')
    // order by m1)as xx;";


    // $sql = "select xx.*,
    // ROUND((xx.month_price * date_number),2) as total_month_price,
    // ROUND(IF(xx.price -(xx.month_price * date_number) <= 1,1,(xx.price -(xx.month_price * date_number))),2) as total,
    //  IF(xx.date_number = 1,(DATEDIFF(DATE_FORMAT(date,'%Y-%m-%d'),xx.receive_date)),xx.days_of_month) as sum_days,
    //   ROUND(((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL service_life YEAR,'%Y-%m-%d'),date)),2) as price_days,
    //   ROUND(((((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date + INTERVAL service_life YEAR,'%Y-%m-%d'),date))* (IF(xx.date_number = 1,(DATEDIFF(DATE_FORMAT(date,'%Y-%m-%d'),xx.receive_date)),xx.days_of_month))) * date_number),2)  as price_of_month,
    // receive_date
    // from (select 
    // LAST_DAY(m1) as date,
    // IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
    // (TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id AND asset_status  = 1) ,LAST_DAY(m1))+1)  as date_number,
    // DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
    // DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id AND asset_status  = 1) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
    // (select price FROM asset where id =:id AND asset_status  = 1) as price,
    // (SELECT receive_date FROM asset WHERE id = :id AND asset_status  = 1) as receive_date,
    // (SELECT data_json->'$.service_life' FROM asset WHERE id = :id AND asset_status  = 1) as service_life,
    // (SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = :id AND asset_status  = 1) as month_price,
    // (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = :id AND asset_status  = 1) as depreciation
    // from
    // (
    // select ((SELECT receive_date FROM asset WHERE id = :id AND asset_status  = 1) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = :id AND asset_status  = 1))-1 DAY) 
    // +INTERVAL m MONTH as m1
    // from
    // (
    // select @rownum:=@rownum+1 as m from
    // (select 1 union select 2 union select 3 union select 4) t1,
    // (select 1 union select 2 union select 3 union select 4) t2,
    // (select 1 union select 2 union select 3 union select 4) t3,
    // (select 1 union select 2 union select 3 union select 4) t4,
    // (select @rownum:=-1) t0
    // ) d1
    // ) d2 
    // where m1<= DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id AND asset_status  = 1) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id AND asset_status  = 1) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d')
    // order by m1)as xx;";


    $new_sql = "SELECT x3.*,
    (x3.total_days * price_days) as total_price,
    (x3.days_x2 * price_days) as total_price2,
    ROUND(x3.price-(x3.days_x2 * price_days),2) as total
    FROM
    (
    SELECT x2.*,DATEDIFF(date,receive_date) as days_x2,
    IF(x2.date_number = 1, DATEDIFF(date,receive_date),x2.days_of_month) as total_days,
    ROUND((x2.price / x2.all_days),2) as price_days
    FROM (
    select x1.*
        from (select LAST_DAY(m1) as date,
        IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
        (TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id) ,LAST_DAY(m1))+1)  as date_number,
        DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
        DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS all_days,
        ((select price FROM asset where id =:id)-1) as price,
        (SELECT receive_date FROM asset WHERE id = :id) as receive_date,
        (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) as service_life,
        (SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = :id) as month_price,
        (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = :id) as depreciation
        from
        (
        select ((SELECT receive_date FROM asset WHERE id = :id) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = :id))-1 DAY) 
        +INTERVAL m MONTH as m1
        from
        (
        select @rownum:=@rownum+1 as m from
        (select 1 union select 2 union select 3 union select 4) t1,
        (select 1 union select 2 union select 3 union select 4) t2,
        (select 1 union select 2 union select 3 union select 4) t3,
        (select 1 union select 2 union select 3 union select 4) t4,
        (select @rownum:=-1) t0
        ) d1
        ) d2 
        where m1<= DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d')
        order by m1)as x1) as x2) as x3;";

    $querys = Yii::$app->db->createCommand($new_sql)
    ->bindValue(':id', $model->id)
    ->bindValue(':receive_date', $model->receive_date)
    // ->getRawSql();
    ->queryAll();
    // $depre = 50;
    // $price = 10000
    // print_r($querys);



    ?>


<div class="alert alert-success" role="alert">
   
    <div class="row">
        <div class="col-6">
            <ul class="list-inline">
            <li> <i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">หมายเลขครุภัณฑ์ </span> <span
        class="text-danger"><?=$model->code?></span>
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                        class="fw-semibold">วันเดือนปีทีซื้อ</span> :
                    <?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?>
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                        class="fw-semibold">อัตราค่าเสื่อม</span> :
                    <?=isset($model->data_json['depreciation']) ? $model->data_json['depreciation'] : ''?>
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">อายุการใช้งาน</span>
                    :
                    <?=isset($model->data_json['service_life']) ? $model->data_json['service_life'] : ''?> ปี
                </li>

            </ul>
        </div>
        <div class="col-6">
            <ul class="list-inline">
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">ราคาซื้อ</span> :
                    <span class="text-white bg-primary badge rounded-pill fs-6">
                        <?=number_format($model->price,2)?></span> บาท
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span class="fw-semibold">จำนวนวัน</span> :
                    <?php // $querys[0]['days_of_year']?> วัน
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                        class="fw-semibold">ค่าเสื่อมราคาประจำปี</span> :
                    <?=number_format($model->price / $model->data_json['service_life'],2)?> บาท <span class="fs-6">
                        <!-- (<?=$model->price?>/<?=$model->data_json['service_life']?>)</span> -->
                </li>
                <li><i class="bi bi-check2-circle text-primary fs-5"></i> <span
                        class="fw-semibold">ค่าเสื่อมราคาประจำเดือน</span> :
                    <?=number_format((round($model->price / $model->data_json['service_life']/12,2)),2)?> บาท
                    <!-- (((<?=$model->price?> / <?=$model->data_json['service_life']?>))/12) -->
                </li>
            </ul>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between">
        <div>
            <h4 class="alert-heading">มูลค่าสุทธิ <span class="text-white bg-danger badge rounded-pill fs-6">
                    <?php foreach ($querys as $data1):?>
                    <?=$data1['active'] == 'Y' ?  number_format(($data1['total']),2) : ''?>
                    <?php endforeach?></span> บาท</h4>
                    <?php
                    
                    ?>
        </div>

    </div>
</div>


<table class="table table-hover table-striped">
    <thead class="table-dark">
        <tr>
            <th class="text-center" style="width:1px;">#</th>
            <th style="width: 150px;" class="text-center">เดือน</th>
            <th style="width: 150px;" class="text-center">วัน</th>
            <th class="text-end">ค่าเสื่อมราคาสะสม</th>
            <th class="text-end">มูลค่าสุทธิ</th>
            <th class="text-end">print</th>
        </tr>
    </thead>
    <tbody>
        <?php
    $data = app\components\AppHelper::GetDepreciation($year,$price,$depre);
    ?>
        <?php $i = 0; foreach ($querys as $data):?>
        <?php $i++ ?>
        <tr class="<?=$data['active'] == 'Y' ? 'bg-primary-subtle' : ''?>">
            <td class="text-center"><?=$data['date_number']?></td>
            <td scope="row" class="text-center">
            <span class="<?=$data['active'] == 'Y' ? 'fs-6  fw-semibold' : ''?>">
              <?php echo Yii::$app->thaiFormatter->asDate($data['date'], 'medium') ?>
            </span>
            </td>
            <td class="text-center">
            <span class="<?=$data['active'] == 'Y' ? 'fs-6  fw-semibold' : ''?>">
            <?= $data['total_days']?>
        </span>
        </td>
            <td class="text-end">
                <span class="<?=$data['active'] == 'Y' ? 'fs-6  fw-semibold' : ''?>">
                    <?=  number_format($data['total_price2'],2) ?>
                </span>
            </td>
            <td class="text-end">
              <span class="<?=$data['active'] == 'Y' ? 'text-white bg-primary badge rounded-pill fs-6 shadow fw-semibold border border-white' : 'fw-semibold'?>">
                <?= number_format(($data['total']),2);?>

              </span>
            </td>
            <td class="text-center">
                <?=Html::a('<i class="fa-solid fa-print"></i>',['/ms-word/asset','id' => $model->id,'number' => $data['date_number']], ['target'=>'_blank'])?>
                <?=Html::a('<i class="fa-solid fa-print"></i>',['/ms-word/asset','id' => $model->id,'number' => $data['date_number']], ['class'=> 'open-modal','data' => ['size' => 'modal-xl']])?>
            </td>
        </tr>
        <?php endforeach ?>

    </tbody>
</table>

<?php endif;?>