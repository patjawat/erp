<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('../default/menu_dashbroad'); ?>
<?php $this->endBlock(); ?>

<?php
$sql = "SELECT x1.*,
(select sum(o.qty * o.price) 
     FROM orders o 
      	INNER JOIN categorise oi ON oi.code = o.asset_item AND oi.name ='asset_item'
		INNER JOIN categorise ot ON ot.code = oi.category_id AND ot.name='asset_type'
     where o.name = 'order_item' AND ot.code = x1.code AND MONTH(STR_TO_DATE(o.data_json->>'$.po_date', '%Y-%m-%d')) = 9
    ) as purchase
FROM(SELECT 
	t.code,
	t.title,sum(s.qty*s.unit_price) as total
FROM stock s 
INNER JOIN categorise i ON i.code = s.asset_item AND i.name ='asset_item'
INNER JOIN categorise t ON t.code = i.category_id AND t.name='asset_type'
GROUP BY t.code) as x1;";
$querys = Yii::$app->db->createCommand($sql)->queryAll();
?>

<div class="card">
    <div class="card-body">
        <h6><i class="fa-solid fa-chart-pie"></i> สรุปงานวัสดุคงคลัง คลังวัสดุ</h6>
            <table class="table table-bordered table-striped">
                <thead class="align-middle text-center">
                    <tr>
                        <th rowspan="2">ที่</th>
                        <th rowspan="2">รายการ</th>
                        <th rowspan="2"><span>สินค้าคงเหลือ</span></th>
                        <th rowspan="2">ซื้อระว่างเดือน</th>
                        <th rowspan="2">รวม</th>
                        <th colspan="3">สินค้าที่ใช้ไป</th>
                        <th rowspan="2">สินค้าคงเหลือ</th>
                    </tr>
                    <tr>
                        <th class="text-center">จ่ายส่วนของ รพ.สต.</th>
                        <th class="text-center">จ่ายส่วนของโรงพยาบาล</th>
                        <th class="text-center">รวม</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php $num = 1;foreach($querys as $item):?>
                    <tr>
                        <td><?=$num++;?></td>
                        <td><?=$item['title']?></td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder">0.00</td>
                        <td class="text-end fw-bolder"><?=number_format($item['total'],2)?></td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>


    </div>
</div>
