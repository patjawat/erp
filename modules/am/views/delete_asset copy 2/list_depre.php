  


<?php
// กรณีไม่มีค่าเสื่อมสะสมยกมา
// https://peakaccount.com/blog/accounting/gen-acct/accounting-cal-asset-depreciation
$sql11 = "select ((36000 - 0)-1) /  (DATEDIFF(DATE_FORMAT('2019-09-11' + INTERVAL 5 YEAR,'%Y-%m-%d'),'2019-09-11')) as price,
(DATEDIFF(DATE_FORMAT('2019-09-11' + INTERVAL 5 YEAR,'%Y-%m-%d'),'2019-09-11')) as day_number;";

//ทีละเดือน
$sql1 = "SELECT receive_date,((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY('2017-04-01'))+1)) as date_number FROM asset WHERE id = 62;";
$sql2 = "select *,
(date_number * price_month) as sum_price_month,
(price - (date_number * price_month)) as total_price
FROM (
SELECT 
receive_date as date,
price,
((TIMESTAMPDIFF(MONTH,receive_date,LAST_DAY('2017-04-30'))+1)) as date_number,
(price/CAST(data_json->'$.service_life' as UNSIGNED)) as price_year,
(price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) as price_month
FROM asset) as asset WHERE (price - (date_number * price_month)) > 0;";

//แสดงจำนวนวันใน 5 ปี
$sql= "select xx.*,
(xx.month_price * date_number) as total_month_price,
ROUND(IF(xx.price -(xx.month_price * date_number) <= 1,1,(xx.price -(xx.month_price * date_number))),2) as total,
from (select 
LAST_DAY(m1) as date,
(TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = 62) ,LAST_DAY(m1))+1)  as date_number,
DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL 1 YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
(select price FROM asset where id =62) as price,
(SELECT receive_date FROM asset WHERE id = 62) as receive_date,
(SELECT data_json->'$.service_life' FROM asset WHERE id = 62) as service_life,
(SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED)) FROM asset WHERE id = 62) as year_price,
(SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = 62) as month_price,
(SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = 62) as depreciation
from
(
select ((SELECT receive_date FROM asset WHERE id = 62) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = 62))-1 DAY) 
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
where m1<= DATE_FORMAT((SELECT receive_date FROM asset WHERE id = 62) + INTERVAL 3 YEAR,'%Y-%m-%d')
order by m1)as xx;";
$querys = Yii::$app->db->createCommand($sql)->queryAll();

?>
<h5>จำนวนวันทั้งปี <?php // $querys?></h5>
<h1>ราคา : <?=$model->price?></h1>
<?php
$price = $model->price;
$price1 = ($model->price / $model->data_json['service_life']);
$price2 = $price1/12;
?>
<div
  class="table-responsive-xxl"
>
  <table
    class="table table-primary"
  >
    <thead>
      <tr>
        <th  scope="col">วัน เดือน ปี</th>
        <th scope="col">ค่าเสื่อมราคาประจำปี</th>
        <th scope="col">ค่าเสื่อมราคาสะสม</th>
        <th scope="col">มูลค่าสุทธิ</th>
      </tr>
    </thead>
    <tbody>
      <?php $i=1; foreach($querys as $data):?>
        <?php 
          $sumPrice = $price2*$i++;
          ?>
   <tr>
    <td><?=$data['date']?></td>
    <td><?=$price2?></td>
    <td><?=$sumPrice?></td>
    <td><?=$price - $sumPrice?></td>
    <td></td>
   </tr>
      <?php endforeach?>

    </tbody>
  </table>
</div>


