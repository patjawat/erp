<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ประเภทเวร';
$this->params['breadcrumbs'][] = ['label' => 'ตารางเวร', 'url' => ['/roster/period/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-tags"></i> <?= Html::encode($this->title) ?>
    </h4>
    <div class="text-body-secondary small">ความหมายของเวรที่ใช้ร่วมกันทั้งโรงพยาบาล — เวลาเข้า-ออกจริงตั้งแยกรายหน่วยงาน</div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/roster/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'roster-setting']); ?>
<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <h6 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-tags"></i> รายการประเภทเวร
            <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">
                <?= number_format($dataProvider->getTotalCount()) ?>
            </span>
        </h6>
        <?= Html::a('<i class="bi bi-plus-lg"></i> เพิ่มประเภทเวร', ['shift-type-form'], [
            'class' => 'btn btn-sm btn-primary open-modal',
            'data' => ['size' => 'modal-lg'],
        ]) ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th style="width:90px">ย่อ</th>
                        <th>ชื่อเวร</th>
                        <th style="width:90px">รหัส</th>
                        <th class="text-center" style="width:110px">เวรดึก</th>
                        <th class="text-center" style="width:110px">เวรเสริม</th>
                        <th class="text-center" style="width:100px">สถานะ</th>
                        <th class="text-end" style="width:110px"></th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $type): ?>
                        <tr class="<?= $type->active ? '' : 'opacity-50' ?>">
                            <td>
                                <span class="badge rounded-pill fs-6 px-3 <?= $type->cellClass() ?>">
                                    <?= Html::encode($type->short_name) ?>
                                </span>
                            </td>
                            <td class="fw-semibold"><?= Html::encode($type->title) ?></td>
                            <td><code><?= Html::encode($type->code) ?></code></td>
                            <td class="text-center">
                                <?= $type->is_night
                                    ? '<i class="bi bi-moon-stars text-primary" title="เวรดึก"></i>'
                                    : '<span class="text-body-secondary">–</span>' ?>
                            </td>
                            <td class="text-center">
                                <?= $type->is_extra
                                    ? '<i class="bi bi-check-lg text-success"></i>'
                                    : '<span class="text-body-secondary">–</span>' ?>
                            </td>
                            <td class="text-center">
                                <?= $type->active
                                    ? '<span class="badge bg-success-subtle text-success-emphasis">ใช้งาน</span>'
                                    : '<span class="badge bg-secondary-subtle text-secondary-emphasis">ปิดใช้</span>' ?>
                            </td>
                            <td class="text-end">
                                <?= Html::a('<i class="bi bi-pencil"></i>', ['shift-type-form', 'id' => $type->id], [
                                    'class' => 'btn btn-sm btn-outline-secondary open-modal',
                                    'data' => ['size' => 'modal-lg'],
                                    'title' => 'แก้ไข',
                                ]) ?>
                                <?php if ($type->active): ?>
                                    <?= Html::a('<i class="bi bi-slash-circle"></i>', ['shift-type-delete', 'id' => $type->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'title' => 'ปิดใช้งาน',
                                        'data' => [
                                            'confirm-delete' => 'ปิดใช้งานประเภทเวรนี้? ตารางเวรเดิมที่อ้างถึงจะยังอยู่ครบ',
                                        ],
                                    ]) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-body-tertiary text-body-secondary small">
        <i class="bi bi-info-circle"></i>
        การนับ <strong>OT</strong> กำหนดแยกในหน้า “เวรของหน่วยงาน” เพื่อไม่ให้ทุกเวรในหมวดเดียวกันถูกนับ OT อัตโนมัติ
    </div>
</div>
<?php Pjax::end(); ?>

<?php
$deleteJs = <<<'JS'
$('body').off('click.rosterDisable').on('click.rosterDisable', 'a[data-confirm-delete]', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var message = $(this).data('confirm-delete');
    if (!window.confirm(message)) { return; }
    $.get(url, function (res) {
        if (res && res.status === 'success') {
            if (typeof success === 'function') { success('ปิดใช้งานแล้ว'); }
            if (typeof erpReloadPjax === 'function') { erpReloadPjax(res.container); }
            else { location.reload(); }
        }
    });
});
JS;
$this->registerJs($deleteJs);
?>
