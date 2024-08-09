<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
/** @var yii\web\View $this */
$this->title = "เบิกวัสดุ/อุปกรณ์";
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cart-plus"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php yii\widgets\Pjax::begin(['id' => 'me','enablePushState' => false,'timeout' => 88888]); ?>

<div class="card">
    <div
        class="card-body d-flex align-middle flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex gap-3 justify-content-start">
            <?= Html::a('<i class="fa-solid fa-cart-plus"></i> เบิกวัสดุ <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white">10</span> ', ['/helpdesk/default/repair-select'], ['class' => 'btn btn-primary rounded-pill shadow position-relative', 'data' => ['size' => 'modal-md']]) ?>

            <?php //  Html::a('<i class="fa-solid fa-circle-plus me-1"></i> ขอซื้อ/ขอจ้าง', ['/purchase/pr-order/create', 'name' => 'pr', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มใบขอซื้อ-ขอจ้าง'], ['class' => 'btn btn-light open-modal','data' =>['size' => 'modal-md']]) ?>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?=$this->render('_search', ['model' => $searchModel])?>
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
            <?php //  Html::a('<i class="fa-solid fa-gear"></i>', ['#', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th><i class="bi bi-ui-checks"></i> ทะเบียนวัสดุ <?=$dataProvider->getTotalCount()?> รายการ</th>
                        <th style="width:250px">คงเหลือ</th>
                        <th style="width:40px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $item):?>
                    <tr class="">
                        <td>
                            <?=$item->product->Avatar()?>
                        </td>
                        <td class="fw-light align-bottom">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted mb-0 fs-13">
                                    คงเหลือ<span class="text-primary">
                                        83%</span>
                                </span>
                                <span class="text-muted mb-0 fs-13">MAX 20</span>
                            </div>

                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-" role="progressbar" aria-label="Progress"
                                    aria-valuenow="83" aria-valuemin="0" aria-valuemax="100" style="width: 83%;">
                                </div>
                            </div>
                        </td>
                        <td><?=Html::a('เลือก',['/me/store/create'],['class' => 'btn btn-sm btn-primary shadow rounded-pill'])?></td>
                        <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>




<?php yii\widgets\Pjax::end()?>