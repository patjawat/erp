<?php

use yii\helpers\Html;
    $year = $model->useful_life ?? 0;
    $depre = $model->depreciation_rate ?? 0;
    $price = $model->price ?? 0;
?>

<?php if (!empty($model->useful_life)): ?>
<?php

$sql = "
SELECT x3.*,
ROUND(IF(x3.days = 0,0,(x3.year_price/12)),2) as price_month,
IF((x3.price - total_price) < 1,1,ROUND((x3.price - total_price),2)) as total
FROM(
    SELECT x2.*,
    IF(x2.count_days > 15, x2.count_days,0) as days,
    (x2.price / x2.service_life) as year_price,
    IF(x2.count_days > 15,
        ROUND(x2.date_number * ((x2.price / x2.service_life)/12),2),
        0
    ) as total_price
FROM(
    SELECT x1.*,
    IF(x1.date_number = 1,
        DATEDIFF(x1.end_date,x1.receive_date),
        x1.days_of_month
    ) as count_days
FROM(

SELECT 
(TIMESTAMPDIFF(MONTH,a.receive_date,LAST_DAY(m1))+1) as date_number,
a.receive_date,
DATE_FORMAT(m1,'%Y-%m-%d') as start_date,
LAST_DAY(m1) as end_date,
DAYOFMONTH(LAST_DAY(m1)) as days_of_month,

IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m'),'Y','N') as active,

DATEDIFF(
DATE_FORMAT(m1 + INTERVAL a.useful_life YEAR,'%Y-%m-%d'),
DATE_FORMAT(m1,'%Y-%m-%d')
) as all_days,

DATE_FORMAT(
DATE_FORMAT(a.receive_date + INTERVAL a.useful_life YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,
'%Y-%m-%d'
) as begin_date,

a.price,
a.useful_life as service_life

FROM asset a

JOIN (

SELECT 
((:receive_date - INTERVAL DAYOFMONTH(:receive_date)-1 DAY) + INTERVAL m MONTH) as m1
FROM
(
SELECT @rownum:=@rownum+1 as m FROM
(select 1 union select 2 union select 3 union select 4) t1,
(select 1 union select 2 union select 3 union select 4) t2,
(select 1 union select 2 union select 3 union select 4) t3,
(select 1 union select 2 union select 3 union select 4) t4,
(select @rownum:=-1) t0
) d1

) d2

WHERE a.id = :id

AND m1 <= DATE_FORMAT(
DATE_FORMAT(a.receive_date + INTERVAL a.useful_life YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,
'%Y-%m-%d'
)

ORDER BY m1

) as x1
) as x2
) as x3
";

$querys = Yii::$app->db->createCommand($sql)
    ->bindValue(':id', $model->id)
    ->bindValue(':receive_date', $model->receive_date)
    ->queryAll();
?>

<div class="alert alert-success">

<div class="row">

<div class="col-6">

<ul class="list-inline">

<li>
<i class="bi bi-check2-circle text-primary fs-5"></i>
<span class="fw-semibold">หมายเลขครุภัณฑ์ </span>
<span class="text-danger"><?= $model->code ?></span>
</li>

<li>
<i class="bi bi-check2-circle text-primary fs-5"></i>
<span class="fw-semibold">วันเดือนปีทีซื้อ</span> :
<?= Yii::$app->thaiFormatter->asDate($model->receive_date,'medium') ?>
</li>

<li>
<i class="bi bi-check2-circle text-primary fs-5"></i>
<span class="fw-semibold">อัตราค่าเสื่อม</span> :
<?= $depre ?> %
</li>

<li>
<i class="bi bi-check2-circle text-primary fs-5"></i>
<span class="fw-semibold">อายุการใช้งาน</span> :
<?= $year ?> ปี
</li>

</ul>

</div>

<div class="col-6">

<ul class="list-inline">

<li>
<i class="bi bi-check2-circle text-primary fs-5"></i>
<span class="fw-semibold">ราคาซื้อ</span> :

<span class="text-white bg-primary badge rounded-pill fs-6">
<?= number_format($price,2) ?>
</span> บาท

</li>

<li>
<i class="bi bi-check2-circle text-primary fs-5"></i>
<span class="fw-semibold">จำนวนวัน</span> :
<?= $querys[0]['all_days'] ?? '-' ?> วัน
</li>

<li>
<i class="bi bi-check2-circle text-primary fs-5"></i>
<span class="fw-semibold">ค่าเสื่อมราคาประจำปี</span> :
<?= number_format($price/$year,2) ?> บาท
</li>

<li>
<i class="bi bi-check2-circle text-primary fs-5"></i>
<span class="fw-semibold">ค่าเสื่อมราคาประจำเดือน</span> :
<?= number_format(($price/$year)/12,2) ?> บาท
</li>

</ul>

</div>

</div>

<hr>

<div class="d-flex justify-content-between">

<div>

<h4 class="alert-heading">

มูลค่าสุทธิ

<span class="text-white bg-danger badge rounded-pill fs-6">

<?php

if (!empty($querys) && $querys[0]['begin_date'] <= date('Y-m-d')) {

echo "1";

} elseif (!empty($querys)) {

foreach ($querys as $row) {

if ($row['active'] == 'Y') {

echo number_format($row['total'],2);

}

}

} else {

echo "-";

}

?>

</span> บาท

</h4>

</div>

<div>

<code>**</code> ถ้าวันที่รับถึงสิ้นเดือน เกิน 15 วัน คิดค่าเสื่อม

</div>

</div>

</div>


<table class="table table-hover table-striped">

<thead class="table-dark">

<tr>

<th class="text-center">#</th>
<th class="text-center">เดือน</th>
<th class="text-center">วัน</th>
<th class="text-end">ค่าเสื่อมราคาสะสม</th>
<th class="text-end">มูลค่าสุทธิ</th>
<th class="text-center">พิมพ์</th>

</tr>

</thead>

<tbody>

<?php $i=0; foreach($querys as $data): $i++; ?>

<tr class="<?= $data['active']=='Y' ? 'bg-primary-subtle':'' ?>">

<td class="text-center"><?= $data['date_number'] ?></td>

<td class="text-center">

<?= Yii::$app->thaiFormatter->asDate($data['end_date'],'medium') ?>

</td>

<td class="text-center">

<?= $data['count_days'] ?>

</td>

<td class="text-end">

<?= number_format($data['total_price'],2) ?>

</td>

<td class="text-end">

<span class="<?= $data['active']=='Y'
? 'text-white bg-primary badge rounded-pill fs-6 shadow border border-white'
: 'fw-semibold' ?>">

<?= number_format($data['total'],2) ?>

</span>

</td>

<td class="text-center">

<div class="d-inline-flex align-items-center gap-2">
    <?php Html::a(
        '<i class="fa-solid fa-print"></i>',
        ['/ms-word/asset','id'=>$model->id,'number'=>$data['date_number'],'date'=>$data['end_date']],
        ['class'=>'open-modal','data'=>['size'=>'modal-xl'],'title'=>'พิมพ์เอกสาร Word']
    ) ?>
    <?= Html::a(
        '<i class="fa-solid fa-file-pdf text-danger"></i>',
        ['/am/asset/depreciation-pdf','id'=>$model->id,'number'=>$data['date_number'],'date'=>$data['end_date']],
        ['target'=>'_blank','rel'=>'noopener noreferrer','data-pjax'=>0,'title'=>'พิมพ์ PDF']
    ) ?>
</div>

</td>

</tr>

<?php endforeach ?>

</tbody>

</table>

<?php endif; ?>
