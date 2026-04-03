
<?php
use app\components\AppHelper;


?>

<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
    <h4>ตัวอย่างข้อมูล</h4> 
    <div class="d-flex gap-2">  
        </div>
    </div>
    </div>
</div>


<table class="table table-hover table-striped">
  <thead>
    <tr>
      <th scope="col" class="text-center">ปี</th>
      <th scope="col" class="text-center">มูลค่าตามบัญชียกมา</th>
      <th scope="col" class="text-center">อัตราค่าเสื่อมราคา</th>
      <th scope="col" class="text-center">ค่าเสื่อมราคา</th>
      <th scope="col" class="text-center">มูลค่าตามบัญชียกไป</th>
    </tr>
  </thead>
  <tbody>
      <?php $i = 0; foreach ($data as $x) { ?>
            <?php $i++ ?>
    <tr>
      <th scope="row" class="text-center"><?= $i ?></th>
      <td class="text-center"><?= strpos($x[0], '.') !== false ?  number_format($x[0], 1) : number_format($x[0]) ?></td>
      <td class="text-center"><?= $x[1] ?>%</td>
      <td class="text-center"><?= strpos($x[2], '.') !== false ?  number_format($x[2], 1) : number_format($x[2]) ?></td>
      <td class="text-center"><?= strpos($x[3], '.') !== false ?  number_format($x[3], 1) : number_format($x[3]) ?></td>
    </tr>
    <?php } ?>
    <tr>
    <?php // 10000 *  (((10000 - (10000 * 40) / 100) / 10000) ** ($n-1)) ?>
    <?php
      for($i = 1;$i <= 5;$i++ ){
      $price = 10000;
      $RatePrice = ($price * 40) / 100;
      $discountedPrice = $price - $RatePrice;
      $total = $price * (($discountedPrice / 10000) ** ( $i-1 ));
      ?>
      <th scope="row" class="text-center"><?= $i ?></th>
      <td class="text-center"><?= $price * (($discountedPrice / 10000) ** ( $i-1 )) ?></td>
      <td class="text-center">40%</td>
      <td class="text-center"><?= $RatePrice ?></td>
      <td class="text-center"><?= $price * (($discountedPrice / 10000) ** ( $i)) ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>




<table class="table table-hover table-striped mt-5">
  <thead>
    <tr>
      <th scope="col" class="text-center">รหัสสินทรัพย์</th>
      <th scope="col" class="text-center">วันที่ได้รับ</th>
      <th scope="col" class="text-center">มูลค่า</th>
      <th scope="col" class="text-center">ค่าเสื่อมยกมา</th>
      <th scope="col" class="text-center">อัตรา</th>
      <th scope="col" class="text-center">ค่าเสื่อมราคาเดือนนี้</th>
      <th scope="col" class="text-center">ค่าเสื่อมยกไป</th>
      <th scope="col" class="text-center">มูลค่าสุทธิ</th>
    </tr>
  </thead>
  <tbody>
    <tr>
    <?php // 10000 *  (((10000 - (10000 * 40) / 100) / 10000) ** ($n-1)) ?>
    <?php
      for($i = 1;$i <= 5;$i++ ){
      $price = 10000;
      $RatePrice = ($price * 40) / 100;
      $discountedPrice = $price - $RatePrice;
      $total = $price * (($discountedPrice / 10000) ** ( $i-1 ));
      ?>
      <th scope="row" class="text-center"><?= $i ?></th>
      <td class="text-center">30/05/64</td>
      <td class="text-center"><?= $price * (($discountedPrice / 10000) ** ( 0 )) ?></td>
      <td class="text-center"><?= $price * (($discountedPrice / 10000) ** ( $i - 1)) ?></td>
      <td class="text-center">40%</td>
      <td class="text-center"><?= ($price * (($discountedPrice / 10000) ** ( $i - 1))) - ($price * (($discountedPrice / 10000) ** ( $i))) ?></td>
      <td class="text-center"><?= $price * (($discountedPrice / 10000) ** ( $i)) ?></td>
      <td class="text-center"><?= $price * (($discountedPrice / 10000) ** ( $i)) ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>






<table class="table table-hover table-striped mt-5">
  <thead>
    <tr>
      <th scope="col" class="text-center">รหัสสินทรัพย์</th>
      <th scope="col" class="text-center">วันที่ได้รับ</th>
      <th scope="col" class="text-center">มูลค่า</th>
      <th scope="col" class="text-center">ค่าเสื่อมยกมา</th>
      <th scope="col" class="text-center">อัตรา</th>
      <th scope="col" class="text-center">ค่าเสื่อมราคาเดือนนี้</th>
      <th scope="col" class="text-center">ค่าเสื่อมยกไป</th>
    </tr>
  </thead>
  <tbody>
  <?php 
  
  
                                        //มูลค่า ,ค่าเสื่อม, จำนวนปี, ปีที่รับเข้า
  $data = AppHelper::GetDepreciationByDay(10000, 40, 3,"2019-12-16")
  
  
  
  ?>
    <tr>
      <th scope="row" class="text-center">1</th>
      <td class="text-center">2019-12-16</td>
      <td class="text-center"><?= $data[0] ?></td>
      <td class="text-center"><?= number_format($data[1], 8) ?></td>
      <td class="text-center"><?= $data[2] ?></td>
      <td class="text-center"><?= number_format($data[3], 8) ?></td>
      <td class="text-center"><?= number_format($data[4], 8) ?></td>
    </tr>
   
  </tbody>
</table>

<?php
//แสดงจำนวนวันใน 5 ปี
$sql1 = "SELECT DATEDIFF(DATE_FORMAT(NOW() + INTERVAL 5 YEAR,'%Y-%m-%d'),NOW());";
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
$querys = Yii::$app->db->createCommand($sql2)->queryAll();
?>
<h5>จำนวนวันทั้งปี <?=$querys1?></h5>
<div
  class="table-responsive-xxl"
>
  <table
    class="table table-primary"
  >
    <thead>
      <tr>
        <th scope="col">Column 1</th>
        <th scope="col">Column 2</th>
        <th scope="col">Column 3</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($querys as $data):?>
      <tr class="">
        <td><?=$data['month_year']?></td>
        <td><?=$data['days_of_month']?></td>
        <td>R1C3</td>
      </tr>
      <?php endforeach?>

    </tbody>
  </table>
</div>

