  


<?php

$sql_xx = "select xx.*,xx.month_year
from (select 
LAST_DAY(m1) as month_year,
DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL 1 YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year

from
(
select ('2020-01-01' - INTERVAL DAYOFMONTH('2020-01-01')-1 DAY) 
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
where m1<= DATE_FORMAT('2020-01-01' + INTERVAL 3 YEAR,'%Y-%m-%d')
order by m1)as xx
WHERE xx.month_year = '2020-10-31';";

$sqlxx2 = "
select xx.*,
(DATEDIFF(xx.month_year,xx.receive_date)) as a1,
(xx.days_of_year-DATEDIFF(xx.month_year,xx.receive_date)) as a2,
xx.month_year
from (select 
LAST_DAY(m1) as month_year,
DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL 5 YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
(SELECT receive_date FROM asset WHERE id = 256) as receive_date
from
(
select ((SELECT receive_date FROM asset WHERE id = 256) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = 256))-1 DAY) 
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
where m1<= DATE_FORMAT((SELECT receive_date FROM asset WHERE id = 256) + INTERVAL 5 YEAR,'%Y-%m-%d')
order by m1)as xx;";

//แสดงจำนวนวันใน 5 ปี
$sql1 = "SELECT DATEDIFF(DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(data_json, '$.service_life') YEAR,'%Y-%m-%d'),receive_date) from asset;";
$querys1 = Yii::$app->db->createCommand($sql1)->queryScalar();
// แสดงวันทั้งหมดในเดือน
$sql2 = "select 
DATE_FORMAT(m1, '%Y-%m-%d') as month_year,
DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month

from
(
select ('2020-01-01' - INTERVAL DAYOFMONTH('2020-01-01')-1 DAY) 
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
where m1<= DATE_FORMAT('2020-01-01' + INTERVAL 3 YEAR,'%Y-%m-%d')
order by m1";
$sql3 = "
select 
DATE_FORMAT(m1, '%Y-%m-%d') as month_year,
DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
asset.price,
JSON_EXTRACT(data_json, '$.depreciation') as value_of_depreciation,
DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL 1 YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
(asset.price - 1) * (JSON_EXTRACT(data_json, '$.depreciation') / 100) * (DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) / DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL 1 YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d'))) AS depreciation

from 
(
    select (receive_date - INTERVAL DAYOFMONTH(receive_date)-1 DAY) 
    +INTERVAL m MONTH as m1
    from
    (
        select @rownum:=@rownum+1 as m from
        (select 1 union select 2 union select 3 union select 4) t1,
        (select 1 union select 2 union select 3 union select 4) t2,
        (select 1 union select 2 union select 3 union select 4) t3,
        (select 1 union select 2 union select 3 union select 4) t4,
        (select @rownum:=-1) t0
    ) d1 ,asset WHERE id = ". Yii::$app->request->get('id') ."
) d2 , asset 
where id = ". Yii::$app->request->get('id') ."
AND
m1<= DATE_FORMAT(receive_date + INTERVAL JSON_EXTRACT(data_json, '$.service_life') YEAR,'%Y-%m-%d')
order by m1;
";

$querys = Yii::$app->db->createCommand($sql3)->queryAll();

$price = $model->price;
$result = 0;
$allday = 0;
?>
<h5>จำนวนวันทั้งปี <?= $querys1 ?></h5>
<div
  class="table-responsive-xxl"
>
  <table
    class="table table-primary"
  >
    <thead>
      
				
      <tr>
        <th class='text-center' scope="col">ปี/เดือน/วัน</th>
        <th class='text-center' scope="col">มูลค่าตามบัญชียกมา</th>
        <th class='text-center' scope="col">อัตราค่าเสื่อมราคา</th>
        <th class='text-center' scope="col">ค่าเสื่อมราคา</th>
        <th class='text-center' scope="col">มูลค่าตามบัญชียกไป</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($querys as $data):?>
      <?php 
      $result = $result + $data['depreciation'];
      $allday = $allday + $data['days_of_month'];
        $now = new DateTime();
        $month = new DateTime($data['month_year']);
        if ($now->format('Y-m') == $month->format('Y-m')){ ?>
      <tr class='bg-warning'>
        <td class='text-center'><?= $data['month_year'] ?></td>
        <td class='text-center'><?= number_format($price,8) ?></td>
        <td class='text-center'><?= $model->data_json['depreciation'] ."%" ?></td>
        <td class='text-center'><?= number_format($data['depreciation'],8)  ?></td>
        <td class='text-center'><?= number_format($price - $data['depreciation'],8)  ?></td>
        <td class='text-center'><?= number_format($price) . " - " . number_format($data['depreciation']). " = " .number_format($price - $data['depreciation'])  ?></td>
        <td class='text-center'><?= $result  ?></td>
        <td class='text-center'><?= $allday  ?></td>
      </tr>
        <?php }else{ ?>
          <tr class="">
            <td class='text-center'><?= $data['month_year'] ?></td>
            <td class='text-center'><?= number_format($price,8) ?></td>
            <td class='text-center'><?= $model->data_json['depreciation'] ."%" ?></td>
            <td class='text-center'><?= number_format($data['depreciation'],8)  ?></td>
            <td class='text-center'>xx<?= ceil($data['depreciation'])  ?></td>
            <td class='text-center'><?= number_format($price - $data['depreciation'],8)  ?></td>
            <td class='text-center'><?= number_format($price) . " - " . number_format($data['depreciation']). " = " .number_format($price - $data['depreciation'])  ?></td>
            <td class='text-center'><?= $result  ?></td>
        <td class='text-center'><?= $allday  ?></td>
        </tr>
        <?php } ?>
        <?php
            $price = $price  - $data['depreciation']
          ?>
      <?php endforeach?>

    </tbody>
  </table>
</div>


