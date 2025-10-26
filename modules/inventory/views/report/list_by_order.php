<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
$this->title = 'สรุปรายงานวัสดุคงคลังหลัก';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu_dashbroad',['active' => 'report'])?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?=$this->render('list_by_order_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทั้งหมด
                <span class="badge text-bg-light">
                    <?=number_format(count($querys))?></span> รายการ
                </h6>
                <p><?=Html::a('Excel', ['/inventory/report/list-by-order'] + Yii::$app->request->queryParams, ['class' => 'btn btn-success']) ?>
</p>
            </div>
    </div>
    <div class="card-body">

        <table class="table table-striped table-hover table-bordered">
            <thead>
                <tr>
                    <th scope="col" class="text-center fw-bold">ลำดับ</th>
                    <th scope="col" class="text-start fw-bold">ชื่อคลัง</th>
                    <th scope="col" class="text-start fw-bold">ประเภทคลัง</th>
                    <th scope="col" class="text-start fw-bold">ประเภทวัสดุ</th>
                    <th scope="col" class="text-center fw-bold">วันที่</th>
                    <th scope="col" class="text-start fw-bold">เลขที่</th>
                    <th scope="col" class="text-center fw-bold">ความเคลื่อนไหว</th>
                    <th scope="col" class="text-start fw-bold">รหัสวัสดุ</th>
                    <th scope="col" class="text-start fw-bold">ชื่อวัสดุ</th>
                    <th scope="col" class="text-center fw-bold">หน่วย</th>
                    <th scope="col" class="text-center fw-bold">จำนวน</th>
                    <th scope="col" class="text-end fw-bold">ราคาต่อหน่วย</th>
                    <th scope="col" class="text-end fw-bold">รวมราคา</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalPrice = 0;?>
                <?php $n=1;foreach ($querys as $item): ?>
                    <tr>
                        <td class="text-center fw-semibold"><?=$n++?></td>
                        <td><?= $item['warehouse_name']?></td>
                        <td>
                            <?php
                                if ($item['warehouse_type'] == 'MAIN') {
                                    echo 'คลังหลัก';
                                } elseif ($item['warehouse_type'] == 'SUB') {
                                    echo 'คลังย่อย';
                                } elseif ($item['warehouse_type'] == 'BRANCH') {
                                    echo 'คลังรพ.สต.';
                                } else {
                                    echo '-'; // กรณีไม่ตรงเงื่อนไขใดเลย
                                }
                                ?>
                        </td>
                        <td><?= $item['asset_type_name']?></td>
                        <td><?= AppHelper::convertToThai($item['movement_date']);?></td>
                        <td><?= $item['asset_item']?></td>
                        <td  class="text-center"><?=$item['transaction_type'] == 'IN' ? 'รับเข้า' : 'จ่ายออก' ?></td>
                        <td><?= $item['asset_item']?></td>
                        <td><?= $item['asset_name']?></td>
                        <td class="text-center"><?= $item['unit']?></td>
                        <td class="text-center fw-bold"><?= $item['item_qty']?></td>
                        <td class="text-end fw-bold"><?= number_format($item['unit_price'] ?? 0,2)?></td>
                        <td class="text-end fw-bold"><?= number_format(($item['item_qty']*$item['unit_price']) ?? 0,2)?></td>
                    </tr>
                    <?php $totalPrice +=($item['item_qty']*$item['unit_price']);?>
                <?php endforeach; ?>
                <tr>
                    <td colspan="12" class="text-end fw-bold bg-warning">รวมราคาทั้งหมด</td>
                    <td class="fw-bold text-end  bg-warning"><?=number_format($totalPrice,2)?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>