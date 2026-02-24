<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายละเอียดการลงเวลา';
$this->params['breadcrumbs'][] = ['label' => 'อนุมัติลงเวลา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> รายการ', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal">ข้อมูลการลงเวลา</h6>
    </div>
    <div class="card-body p-4">
        <table class="table table-borderless align-middle mb-0">
            <tbody class="table-group-divider">
                <tr><th class="text-muted" style="width: 180px;">พนักงาน</th><td><?= $model->employee ? Html::encode($model->employee->fname . ' ' . $model->employee->lname) : '-' ?></td></tr>
                <tr><th class="text-muted">วันเวลา</th><td><?= Yii::$app->formatter->asDatetime($model->checkin_at, 'php:d/m/Y H:i:s') ?></td></tr>
                <tr><th class="text-muted">วิธีลงเวลา</th><td><?= Html::encode($model->getMethodLabel()) ?></td></tr>
                <tr><th class="text-muted">อยู่ในบริเวณ</th><td><?= $model->is_in_location ? 'ใช่' : 'ไม่' ?></td></tr>
                <?php if (!$model->is_in_location && $model->out_of_location_reason): ?>
                <tr><th class="text-muted">เหตุผลนอกบริเวณ</th><td><?= nl2br(Html::encode($model->out_of_location_reason)) ?></td></tr>
                <?php endif; ?>
                <?php if ($model->lat !== null && $model->lng !== null): ?>
                <tr><th class="text-muted">พิกัด</th><td><?= Html::encode($model->lat . ', ' . $model->lng) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <p class="fw-semibold mb-2">ดำเนินการ</p>
        <button type="button" class="btn btn-success btn-approve" data-id="<?= $approve->id ?>" data-status="Pass">อนุมัติ</button>
        <button type="button" class="btn btn-outline-danger btn-approve" data-id="<?= $approve->id ?>" data-status="Reject">ไม่อนุมัติ</button>
    </div>
</div>

<?php
$updateUrl = Url::to(['/approve-v2/checkin/update']);
$this->registerJs(<<<JS
$('.btn-approve').on('click', function() {
    var id = $(this).data('id');
    var status = $(this).data('status');
    var comment = status === 'Reject' ? (prompt('เหตุผล (ถ้ามี):') || '') : '';
    var \$btn = $(this);
    \$btn.prop('disabled', true);
    $.post('$updateUrl', { id: id, status: status, comment: comment }).then(function(r) {
        if (r.status === 'success') window.location.href = window.location.href.replace(/\/view.*/, '/index');
        else alert(r.message || 'เกิดข้อผิดพลาด');
    }).always(function() { \$btn.prop('disabled', false); });
});
JS
);
?>
