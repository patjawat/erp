<?php

use app\modules\sm\models\Order;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ระบบขอซื้อ';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'purchase']); ?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex gap-3 justify-content-start">
            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> ขอซื้อ/ขอจ้าง', ['/purchase/pr-order/create', 'name' => 'pr', 'title' => '<i class="bi bi-plus-circle"></i> เพิ่มใบขอซื้อ-ขอจ้าง'], ['class' => 'btn btn-light open-modal','data' =>['size' => 'modal-md']]) ?>
        </div>
        <div class="d-flex gap-2">
            <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
            <?= Html::a('<i class="fa-solid fa-gear"></i>', ['#', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-md']]) ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive" style="height:800px">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th class="fw-semibold" style="width:350px">ผู้ขอซื้อ</th>
                        <?php if($name == 'pr'):?>
                        <th class="fw-semibold">เลขที่ขอ(PR)</th>
                        <?php endif;?>
                        <?php if($name == 'pq'):?>
                        <th class="fw-semibold">เลขทะเบียนคุม(PQ)</th>
                        <?php endif;?>
                        <?php if($name == 'po'):?>
                        <th class="fw-semibold">เลขที่สั่งซื้อ(PO)</th>
                        <?php endif;?>
                        <th class="fw-semibold">ความคืบหน้า</th>
                        <th class="fw-semibold">หมายเหตุ</th>
                        <th class="fw-semibold text-center" style="width:176px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="">
                        <td class="fw-light"> <?= $model->getUserReq()['avatar'] ?></td>
                        <?php if($model->status == '' || $model->status == 2):?>
                        <td class="fw-light align-middle">
                            <?= Html::a($model->pr_number, ['/purchase/pr-order/view', 'id' => $model->id], ['class' => 'fw-bolder']) ?>
                        </td>
                        <?php endif;?>
                        <?php if($model->status == 2):?>
                        <td class="fw-light align-middle">
                            <?= Html::a($model->pq_number, ['/purchase/pq-order/view', 'id' => $model->id], ['class' => 'fw-bolder']) ?>
                        </td>
                        <?php endif;?>
                        <?php if($model->status == 3):?>
                        <td class="fw-light align-middle">
                            <?= Html::a($model->po_number, ['/purchase/po-order/view', 'id' => $model->id], ['class' => 'fw-bolder']) ?>
                        </td>
                        <?php endif;?>
                        <td class="fw-light align-middle">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" role="progressbar" aria-label="Progress" style="width: 50%;"
                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <?=$model->viewStatus()?>
                        </td>
                        <td class="fw-light align-middle"><?php //  $model->data_json['comment'] ?></td>
                        <td class="fw-light">
                            <div class="btn-group">
                                <?= Html::a('<i class="bi bi-clock"></i> ดำเนินการ', ['/purchase/'.$name.'-order/view', 'id' => $model->id], ['class' => 'btn btn-light w-100']) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                <?php if ($model->status == 3): ?>
                                    <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> ลงทะเบีนยคุม', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> ลงทะเบีนยคุม'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                    <?php endif;?>
                                    <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/sm/order/document', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> สร้างใบสั่งซื้อ', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                    </li>
                                </ul>
                            </div>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<?php Pjax::end(); ?>