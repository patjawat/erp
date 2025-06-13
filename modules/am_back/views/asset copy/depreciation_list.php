<?php
use yii\helpers\Html;
?>
<?php if(isset($model->data_json['service_life'])):?>
<?php
    $year = $model->data_json['service_life'];
    $depre = $model->data_json['depreciation'];
    $price = $model->price;

    $sql = "SELECT x3.*,
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
$new_sql = "SELECT x3.*,
ROUND(IF(x3.days = 0,0,(x3.year_price/12)),2) as price_month,
IF((x3.price - total_price) < 1,1,ROUND((x3.price - total_price),2)) as total
FROM(
SELECT x2.*,
 IF(x2.count_days > 15, x2.count_days,0) as days,
 (x2.price / x2.service_life) as year_price,
  IF(x2.count_days > 15, ROUND(x2.date_number * ((x2.price / x2.service_life)/12),2),0) as total_price
FROM(
SELECT x1.*,
IF(x1.date_number = 1, DATEDIFF(x1.end_date,receive_date),x1.days_of_month) as count_days

FROM(
select 
(TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id) ,LAST_DAY(m1))+1)  as date_number,
    (SELECT receive_date FROM asset WHERE id = :id) as receive_date,
    DATE_FORMAT(m1, '%Y-%m-%d') as start_date,
    LAST_DAY(m1) as end_date,
     DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS all_days,
DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d') as begin_date,
    (SELECT price FROM asset where id =:id) as price,
    (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) as service_life,
    (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = :id) as dep
    

from
(
select ((SELECT receive_date FROM asset WHERE id = :id) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = :id))-1 DAY) + INTERVAL m MONTH as m1
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
where m1<=DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d')
order by m1) as x1) as x2) as x3";

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
                    <?=isset($model->data_json['depreciation']) ? $model->data_json['depreciation'].' %' : ''?>
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
                    <?php echo $querys[0]['all_days']?> วัน
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
                    <?php if($querys[0]['begin_date'] <= date('Y-m-d')):?>
                            1
                    <?php else:?>
                        <?php foreach ($querys as $data1):?>
                        <?=$data1['active'] == 'Y' ?  number_format(($data1['total']),2) : ''?>
                        <?php endforeach?>
                    <?php endif;?>
                </span> บาท</h4>
                    <?php
                    ?>
        </div>
        <div>
        <code>**</code> ถ้าวันที่รับถึงสิ้นเดือน เกิน 15 วัน คิดค่าเสื่อม
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
              <?php echo Yii::$app->thaiFormatter->asDate($data['end_date'], 'medium') ?>
            </span>
            </td>
            <td class="text-center">
            <span class="<?=$data['active'] == 'Y' ? 'fs-6  fw-semibold' : ''?>">
            <?php echo $data['count_days']?>
            </span>
            </td>
            <td class="text-end">
                <span class="<?=$data['active'] == 'Y' ? 'fs-6  fw-semibold' : ''?>">
                    <?php echo number_format($data['total_price'],2) ?>
                </span>
            </td>
            <td class="text-end">
              <span class="<?=$data['active'] == 'Y' ? 'text-white bg-primary badge rounded-pill fs-6 shadow fw-semibold border border-white' : 'fw-semibold'?>">
                <?= number_format(($data['total']),2);?>

              </span>
            </td>
            <td class="text-center">
                <?=Html::a('<i class="fa-solid fa-print"></i>',['/ms-word/asset','id' => $model->id,'number' => $data['date_number'],'date' => $data['end_date']], ['class'=> 'open-modal','data' => ['size' => 'modal-xl']])?>
            </td>
        </tr>
        <?php endforeach ?>

    </tbody>
</table>

<?php endif;?>