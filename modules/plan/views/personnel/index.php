<?php

use yii\web\View;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var float $totalAmount */

$this->title = 'แผนคำขอบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'แผนงาน', 'url' => ['/plan/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-plus-icon lucide-user-plus">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <line x1="19" x2="19" y1="8" y2="14" />
            <line x1="22" x2="16" y1="11" y2="11" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'personnel']) ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="text-white my-1 d-flex flex-wrap align-items-center gap-2"><i class="bi bi-ui-checks"></i> ทะเบียน<?= $this->title ?>
                <span class="badge bg-body text-body"><?= number_format($dataProvider->getTotalCount()) ?> รายการ</span>
                <span class="badge bg-body text-body">รวม <?= number_format($totalAmount, 2) ?> บาท</span>
            </h6>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-light" id="planExpandAll" title="ขยายทุกกลุ่ม"><i class="bi bi-arrows-expand"></i></button>
                <button type="button" class="btn btn-sm btn-outline-light" id="planCollapseAll" title="ย่อทุกกลุ่ม"><i class="bi bi-arrows-collapse"></i></button>
                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างใหม่', [
                    'create',
                    'returnUrl' => Yii::$app->request->url,
                ], ['class' => 'btn btn-success']) ?>
            </div>

        </div>
    </div>
    <div class="card-body">

        <table class="table table-striped table-hover" id="plan-group-table">
            <thead>
                <tr>
                    <th class="text-center" style="width:30px">ลำดับ</th>
                    <th scope="col">ประเภท</th>
                    <th scope="col">หมวดหมู</th>
                    <th scope="col">วัตถุประสงค์</th>
                    <th scope="col" class="text-end">วงเงิน</th>
                    <th scope="col" class="text-center">แหล่งของเงิน</th>
                    <th scope="col">สถานะ</th>
                    <th class="fw-semibold text-center" scope="col" style="width: 100px;">จัดการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php $groups = \app\modules\plan\models\PlanOrder::groupByUnit($dataProvider->getModels()); ?>
                <?php if (empty($groups)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-3">ไม่พบข้อมูล</td></tr>
                <?php endif; ?>
                <?php $seq = 0; $gi = 0; ?>
                <?php foreach ($groups as $g): $gi++; $gid = 'g' . $gi; ?>
                    <tr class="plan-grp" data-grp="<?= $gid ?>">
                        <td colspan="8" class="fw-semibold">
                            <i class="fa-solid fa-chevron-down plan-grp-caret me-1"></i>
                            <?= Html::encode($g['name']) ?>
                            <?php if ($g['unit_type']): ?><span class="badge text-bg-light border ms-1"><?= Html::encode($g['unit_type']) ?></span><?php endif; ?>
                            <span class="badge bg-secondary ms-1"><?= count($g['models']) ?> รายการ</span>
                            <span class="text-muted small ms-2">รวม <?= number_format($g['total'], 2) ?> บาท</span>
                        </td>
                    </tr>
                    <?php foreach ($g['models'] as $item): $seq++; ?>
                        <tr class="plan-grp-row" data-grp="<?= $gid ?>">
                            <td class="text-center"><?= $seq ?></td>
                            <td><?= $item->planItem?->title ?? '-' ?></td>
                            <td><?= $item->planItem?->planCategory?->title ?? '-' ?></td>
                            <td><?= $item->description ?></td>
                            <td class="text-end"><?= number_format($item->order_price ?? 0, 2) ?></td>
                            <td class="text-center"><?= $item->budge?->title ?? '-' ?></td>
                            <td><?= $item->viewStatus()['view'] ?></td>
                            <td class="text-center action">
                                <?= $this->render('action', ['model' => $item]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->render('@app/modules/plan/views/_group_toggle') ?>

<?php
$js = <<< JS

$('.update-status').click(function (e) { 
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยัน?',
        text: "คุณแน่ใจหรือไม่ที่จะเปลี่ยนสถานะนี้",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, เปลี่ยนเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url:$(this).attr('href'),
                data: {
                    id:$(this).data('id'),
                    status:$(this).data('status'),
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: 'อัปเดตสถานะเรียบร้อยแล้ว',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload(); // โหลดใหม่ถ้าต้องการ
                        });
                    }
                       if (response.status === 'error') {
                          Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด!',
                        text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                    });
                    }
                },
                error: function () {
                  
                }
            });
        }
    });
});



$('.renew').click(function (e) { 
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยันการปรับแผน?',
        text: "คุณแน่ใจหรือไม่ที่จะเปลี่ยนสถานะนี้",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, เปลี่ยนเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url:$(this).attr('href'),
                data: {
                    id:$(this).data('id'),
                    status:$(this).data('status'),
                },
                dataType: "json",
                success: function (response) {
                   if (response.url) {
                        window.location.href = response.url;
                    } else {
                        location.reload();
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด!',
                        text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                    });
                }
            });
        }
    });
});



JS;
$this->registerJS($js, View::POS_END);
?>
