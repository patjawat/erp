<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'อนุมัติลงเวลาเข้างาน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/approve-v2']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clock-history fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/approveV2/tab_menu', ['menu' => 'checkin']) ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="p-3 border-bottom">
            <h6 class="mb-0">รออนุมัติ <?= $dataProvider->getTotalCount() ?> รายการ</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">ลำดับ</th>
                        <th>พนักงาน</th>
                        <th>วันเวลาที่ลงเวลา</th>
                        <th>วิธีลงเวลา</th>
                        <th>บริเวณ</th>
                        <th class="text-center" style="width: 140px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider align-middle">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <?php $record = $item->checkinRecord; if (!$record) continue; ?>
                    <tr>
                        <td class="text-center"><?= ($dataProvider->pagination->offset + $key + 1) ?></td>
                        <td><?= $record->employee ? Html::encode($record->employee->fname . ' ' . $record->employee->lname) : '-' ?></td>
                        <td><?= Yii::$app->formatter->asDatetime($record->checkin_at, 'php:d/m/Y H:i') ?></td>
                        <td><?= Html::encode($record->getMethodLabel()) ?></td>
                        <td><?= $record->is_in_location ? 'อยู่ในบริเวณ' : 'นอกบริเวณ' ?></td>
                        <td class="text-center">
                            <a href="<?= Url::to(['view', 'id' => $item->id]) ?>" class="btn btn-outline-primary btn-sm me-1">ดู</a>
                            <button type="button" class="btn btn-success btn-sm btn-approve" data-id="<?= $item->id ?>" data-status="Pass">อนุมัติ</button>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-approve" data-id="<?= $item->id ?>" data-status="Reject">ไม่อนุมัติ</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="p-4 text-center text-muted">ไม่มีรายการรออนุมัติ</div>
        <?php endif; ?>
    </div>
</div>

<?php
$updateUrl = Url::to(['/approve-v2/checkin/update']);
$this->registerJs(<<<JS
$('.btn-approve').on('click', function() {
    var id = $(this).data('id');
    var status = $(this).data('status');
    var comment = '';
    if (status === 'Reject') {
        comment = prompt('เหตุผล (ถ้ามี):') || '';
    }
    var \$btn = $(this);
    \$btn.prop('disabled', true);
    $.post('$updateUrl', { id: id, status: status, comment: comment }).then(function(r) {
        if (r.status === 'success') location.reload();
        else alert(r.message || 'เกิดข้อผิดพลาด');
    }).always(function() { \$btn.prop('disabled', false); });
});
JS
);
?>
