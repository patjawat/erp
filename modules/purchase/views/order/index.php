<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\components\ApproveHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนประวัติ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบขอซื้อ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
$totalPrice = 0;
$betwenTitle = '';
if ($searchModel->date_between == 'pr_create_date') {
    $betwenTitle = 'วันที่ขอซื้อ';
} elseif ($searchModel->date_between == 'po_date') {
    $betwenTitle = 'วันที่สั่งซื้อ';
} elseif ($searchModel->date_between == 'gr_date') {
    $betwenTitle = 'วันที่ตรวจรับ';
} else {
    $betwenTitle = '';
}


?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-list-ul me-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'order']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'purchase-container','timeout' => 88888888]); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between  align-items-center">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนขอซื้อขอจ้าง
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <h6 class="text-white">มูลค่า <?= number_format($sumTotal ?? 0, 2) ?> บาท</h6>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 600px;max-height: 600px;min-height: 300px; overflow: auto;">
            <table class="table table-striped table-hover mb-0">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th class="text-center" style="width:30px">ลำดับ</th>
                        <th style="min-width:200px">ผู้ขอ/วันเวลา</th>
                        <th style="min-width:100px">เลขทะเบียนคุม</th>
                        <th style="min-width:180px">ประเภทจัดซื้อ</th>
                        <th class="text-center" style="min-width:95px">วันที่สั่งซื้อ</th>
                        <th style="min-width:280px">ผู้ขาย/เลขที่สั่งซื้อ</th>
                        <th style="min-width:110px" class="text-center">ประเภทเงิน</th>
                        <th style="min-width:100px" class="text-center">วิธีการจัดซื้อ</th>
                        <th style="min-width:95px">วันที่ตรวจรับ</th>
                        <th style="min-width:95px">การตรวจสอบ</th>
                        <th style="min-width:180px">สถานะ</th>
                        <th style="min-width:120px"class="text-end">มูลค่า</th>
                        <th class="text-cener" style="width:100px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <?php
                        $totalPrice += $item->calculateVAT()['priceAfterVAT'];
                        ?>
                        <tr>
                            <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                            <td class="fw-light"> <?= $item->getUserReq()['avatar'] ?></td>
                            <td class="text-center">
                                <?php if($item->pq_number == ''):?>
                                <i class="fa-regular fa-circle-question text-warning"></i>
                            <?php else:?>
                              <p class="fw-medium text-dark mb-0"><i class="fa-regular fa-circle-check text-primary"></i> <?= $item->pq_number ?></p>
                              <?php endif?>
                            </td>
                            <td>
                                
                              <p class="mb-0 text-truncate">
                                  <span class="badge text-bg-primary fs-12 p-1"><?= $item->viewRequestType() ?></span> <?= $item->assetType->title ?? '-' ?>
                              </p> 
                            </td class="text-center">
     <td>
                                <?= isset($item->data_json['po_date']) ?  AppHelper::convertToThai($item->data_json['po_date'] ?? null) : ''; ?>
                            </td>
                            <td class="fw-light align-middle text-truncate">
                                    <h6 class="text-dark"><?= $item->vendor?->title ?? '-' ?></h6>
                                    <span class=""><?= $item->po_number ?> </span>
                            </td>
                       
                            <td class="text-center"><?= $item->budgetTypeName() ?></td>
                            <td><?= $item->data_json['pq_purchase_type_name'] ?? '-' ?></td>
                            <td><?= isset($item->data_json['gr_date']) ? AppHelper::convertToThai($item->data_json['gr_date'] ?? null) : ''; ?></td>
                            <td class="fw-light align-middle">
                                <?php echo $item->StackApprove() ?>
                            </td>

                            <td class="fw-light align-middle">
                                <?php if ($item->deleted_at == null): ?>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted mb-0 fs-13">
                                            <span
                                                class="badge rounded-pill text-bg-<?= $item->viewStatus()['color'] ?> fs-13"><?= $item->viewStatus()['status_name'] ?> </span>
                                                <span class="text-muted ms-2" style="font-size: 0.75rem;"><?= $item->viewUpdated() ?>ที่แล้ว</span>
                                        <?php echo ApproveHelper::viewStep('purchase',$item->id); ?>
                                    </div>

                                <?php else: ?>

                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted mb-0 fs-13">
                                            <i class="fa-regular fa-circle-stop text-danger"></i> ยกเลิกรายการ<span
                                                class="text-primary">
                                                <?= $item->viewStatus()['progress'] ?>%</span>
                                        </span>
                                        <span class="text-muted mb-0 fs-13"><?= $item->viewUpdated() ?>ที่แล้ว</span>
                                    </div>

                                <?php endif; ?>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-<?= $item->viewStatus()['color'] ?>" role="progressbar"
                                        aria-label="Progress" aria-valuenow="<?= $item->viewStatus()['progress'] ?>"
                                        aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </td>
                            <td class="fw-light align-middle text-end">
                                <div class="d-felx flex-column">
                                    <p class="fw-medium text-dark">
                                        <?= number_format($item->calculateVAT()['priceAfterVAT'], 2) ?>
                                    </p>
                                </div>
                            </td>
                            <td class="fw-light">
                                <div class="btn-group">
                                    <?= Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>', ['/purchase/order/view', 'id' => $item->id], ['class' => 'btn btn-light w-100']) ?>
                                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                        <i class="bi bi-caret-down-fill"></i>
                                    </button>

                                    <?php if ($item->status !== 7): ?>
                                        <ul class="dropdown-menu">
                                            <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> คำขอซื้อ', ['/purchase/pr-order/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> คำขอซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                                <?php if ($item->status >= 2): ?>
                                            <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> ทะเบียนคุม', ['/purchase/pr-order/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> ทะเบียนคุม'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                            <?php endif; ?>

                                            <?php if ($item->status >= 3): ?>
                                            <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> คำสั่งซื้อ', ['/purchase/po-order/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> คำสั่งซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                            <?php endif; ?>

                                            <?php if ($item->status >= 4): ?>
                                            <li><?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> ใบตรวจรับ', ['/purchase/gr-order/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-print"></i> ใบตรวจรับ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                                            <?php endif; ?>

                                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/purchase/order/document', 'id' => $item->id, 'title' => '<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                            </li>
                                        </ul>
                                    <?php endif; ?>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
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










<?php
$url = Url::to(array_merge(
    ['//purchase/order/index', 'export' => 1],
    Yii::$app->request->queryParams
));
$js = <<< JS
        $("body").on("click", "#download-button", function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'ยืนยันการดาวน์โหลด?',
                text: 'คุณต้องการดาวน์โหลดรายงาน สขร. ใช่หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ดาวน์โหลด',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // แสดง loading ระหว่างรอดาวน์โหลด
                    Swal.fire({
                        title: 'กำลังดาวน์โหลด...',
                        text: 'กรุณารอสักครู่ ระบบกำลังประมวลผลไฟล์',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '$url', // URL ไปยัง action export Excel
                        method: 'GET',
                        xhrFields: {
                            responseType: 'blob' // สำหรับรับ binary data
                        },
                        success: function (data) {
                            const monthName = $('#stockeventsearch-receive_month').find(':selected').text();
                            const filename = 'รายงาน สขร.' + '.xlsx';
                            const blob = new Blob([data], {
                                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                            });

                            const link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = filename;
                            link.click();

                            // ปิด loading + แสดง success
                            Swal.fire({
                                icon: 'success',
                                title: 'ดาวน์โหลดสำเร็จ',
                                text: 'ไฟล์ถูกดาวน์โหลดเรียบร้อยแล้ว',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: 'ไม่สามารถดาวน์โหลดไฟล์ได้',
                                confirmButtonText: 'ตกลง'
                            });
                        }
                    });
                }
            });
        });

    JS;
$this->registerJS($js, View::POS_END);
?>

<?php Pjax::end(); ?>