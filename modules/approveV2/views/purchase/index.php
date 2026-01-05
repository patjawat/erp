<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
$this->title = 'ขอซื้อ/ขอจ้าง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
$msg = 'ขอ';
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check">
            <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
            <path d="m9 14 2 2 4-4" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>


<?= $this->render('@app/modules/approveV2/tab_menu', [
    'menu' => 'purchase'
]) ?>


<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียนขอซื้อขอจ้าง <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?> </span> รายการ</h6>
            <?php echo $this->render('@app/modules/approveV2/views/default/_search', ['model' => $searchModel, 'emp_label' => 'ผู้ขอซื้อ']) ?>


        </div>
        <div class="table-responsive" style="max-height: 600px;max-height: 600px;min-height:300px; overflow: auto;">
            <table class="table table-striped table-hover mb-0">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <!-- Checkbox เลือกทั้งหมด -->
                        <th class="text-center" style="width:30px">
                            <input type="checkbox" id="check-all">
                        </th>
                        <th class="text-center" style="width:30px">ลำดับ</th>
                        <th class="text-start" style="width: 165px;">สถานะ</th>
                        <th style="width:300px">ผู้ขอ/วันเวลา</th>
                        <th style="width:180px">ประเภท</th>
                        <th>เลขที่สั่งซื้อ/ผู้ขาย</th>
                        <th style="width: 200px;">กรรมการตรวจรับ</th>
                        <th class="fw-semibold text-end" style="width:150px">มูลค่า/ประเภทเงิน</th>
                        <th class="fw-semibold text-cener" style="width:100px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <tr class="">
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="check-item"
                                    name="selected[]"
                                    value="<?= $item->id ?>"
                                    <?= ($item->status == 'Pending'  ? '' : 'disabled') ?>>
                            </td>
                            <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                            <td><?= $item->viewApproveStatus() ?></td>
                            <td class="fw-light"> <?= $item->purchase?->getUserReq()['avatar'] ?></td>
                            <td><?= isset($item->purchase?->data_json['order_type_name']) ? $item->purchase?->data_json['order_type_name'] : '' ?>
                            </td>

                            <td class="fw-light align-middle">
                                <div class=" d-flex flex-column">
                                    <span class="fw-semibold "><?= $item->purchase?->po_number ?></span>
                                    <?= isset($item->purchase?->data_json['vendor_name']) ? $item->purchase?->data_json['vendor_name'] : '' ?>
                                </div>
                            </td>
                            <td class="fw-light align-middle"><?= $item->purchase?->StackComittee() ?></td>
                            <td class="fw-light align-middle text-end">
                                <div class="d-felx flex-column">
                                    <div class="fw-semibold ">
                                        <?= number_format($item->purchase?->calculateVAT() ? $item->purchase?->calculateVAT()['priceAfterVAT'] : 0, 2) ?>
                                    </div>
                                    <div class="text-primary mb-0 fs-15">
                                        <?= isset($item->purchase?->data_json['pq_budget_type_name']) ? $item->purchase?->data_json['pq_budget_type_name'] : '' ?>
                                    </div>
                                </div>
                            </td>

                            <td class="fw-light">
                                <div class="d-flex gap-2 justify-content-center">
                                    <?php echo Html::a('<i class="fa-regular fa-circle-check"></i> ตรวจสอบ', ['/approve/purchase/update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary rounded-pill open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                    <?php // echo Html::a('<i class="fa-solid fa-pencil fa-2x"></i>',['/approve/purchase/view', 'id' => $item->purchase->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])
                                    ?>
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
</div>