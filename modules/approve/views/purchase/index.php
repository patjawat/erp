<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'ขอซื้อ/ขอจ้าง';
$msg = 'ขอ';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-calendar-day"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'approve']) ?>
<?php $this->endBlock(); ?>


<?php if($dataProvider->getTotalCount() > 0):?>


<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนขอซื้อขอจ้าง <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>


        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th class="fw-semibold" style="width:110px">เลขที่ขอ</th>
                    <th class="fw-semibold" style="width:300px">ผู้ขอ/วันเวลา</th>
                    <th class="fw-semibold" style="width:180px">ประเภท</th>
                    <th class="fw-semibold">เลขที่สั่งซื้อ/ผู้ขาย</th>
                    <th class="fw-semibold" style="width: 200px;">กรรมการตรวจรับ</th>
                    <th class="fw-semibold text-end" style="width:150px">มูลค่า/ประเภทเงิน</th>
                    <th class="fw-semibold text-cener" style="width:100px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $item): ?>
                <tr>
                    <td>
                        <span class="fw-semibold "><?=$item->purchase?->pr_number?></span></td>
                    <td class="fw-light"> <?= $item->purchase?->getUserReq()['avatar'] ?></td>
                    <td><?=isset( $item->purchase?->data_json['order_type_name']) ? $item->purchase?->data_json['order_type_name'] : ''?>
                    </td>

                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <span class="fw-semibold "><?= $item->purchase?->po_number?></span>
                            <?= isset( $item->purchase?->data_json['vendor_name']) ? $item->purchase?->data_json['vendor_name'] : '' ?>
                        </div>
                    </td>
                    <td class="fw-light align-middle"><?= $item->purchase?->StackComittee() ?></td>
                    <td class="fw-light align-middle text-end">
                        <div class="d-felx flex-column">
                            <div class="fw-semibold ">
                                <?= number_format($item->purchase?->calculateVAT() ? $item->purchase?->calculateVAT()['priceAfterVAT'] : 0, 2) ?>
                            </div>
                            <div class="text-primary mb-0 fs-15">
                                <?=isset( $item->purchase?->data_json['pq_budget_type_name']) ? $item->purchase?->data_json['pq_budget_type_name'] : ''?>
                            </div>
                        </div>
                    </td>
                    
                    <td class="fw-light">
                    <div class="d-flex gap-2 justify-content-center">
                    <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/approve/purchase/update', 'id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
                            <?php // echo Html::a('<i class="fa-solid fa-pencil fa-2x"></i>',['/approve/purchase/view', 'id' => $item->purchase->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
                            </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'class' => 'pagination pagination-sm',
        ],
    ]); ?>
        </div>
    </div>
</div>
<?php else:?>
    <h5 class="text-center">ไม่มีรายการ</h5>
<?php endif?>
<?php // Pjax::end(); ?>