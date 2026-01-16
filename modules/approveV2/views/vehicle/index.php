<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
$msg = 'ขอ';
$this->title = 'อนุมัติขอใช้รถยนต์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check">
        <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
        <path d="m9 14 2 2 4-4" />
    </svg>
    <?= $this->title ?>
</h4>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/approveV2/tab_menu', [
    'menu' => 'vehicle'
]) ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>เห็นชอบการลา <?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ</h6>

            <?php echo $this->render(
                '@app/modules/approveV2/views/default/_search',
                [
                    'model' => $searchModel,
                    'emp_label' => 'ผู้ขอใช้รถยนต์',
                    'approveAllUrl' => Url::to(['/approve-v2/leave/approve-all'])
                ]
            ) ?>

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
                        <th>ผู้ขอใช้รถยนต์</th>
                        <th>วันที่ขอใช้</th>
                        <th>จุดหมาย</th>
                        <th>ประเภทรถ</th>
                        <th class="text-center">จัดการ</th>
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
                            <td>
                                <?= $item->viewApproveStatus() ?>
                            </td>
                            <td>
                               <?= $item->vehicle->employee->getAvatar(false) ?>
                            </td>
                            <td>
                                <p class="mb-0"><?php echo $item->vehicle->viewGoType() ?></p>
                                <p class="mb-0"><?php echo $item->vehicle->showDateRange() ?></p>
                            </td>
                            <td><?php echo $item->vehicle->locationOrg?->title ?? '-' ?></td>
                            <td><?php echo $item->vehicle->carType?->title ?? '-' ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <?php echo Html::a('<i class="fa-regular fa-circle-check"></i> ตรวจสอบ', ['/approve-v2/vehicle/update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary rounded-pill  open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer text-muted d-flex justify-content-center mt-4">
        <?= yii\bootstrap5\LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'firstPageLabel' => 'หน้าแรก',
            'lastPageLabel' => 'หน้าสุดท้าย',
            'options' => [
                'listOptions' => 'pagination pagination-sm',
                'class' => 'pagination-sm',
            ],
        ]); ?>
    </div>
</div>