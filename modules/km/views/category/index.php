<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmCategory[] $categories */

$this->title = 'หมวดหมู่กิจกรรม KM';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลังกิจกรรม KM<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/km/menu', ['active' => 'category']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $tone): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($key)) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3">รายการหมวด</h2>
                    <?php if (!$categories): ?>
                        <div class="text-body-secondary small">ยังไม่มีหมวด — เพิ่มได้จากแบบฟอร์มด้านขวา</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr>
                                    <th style="width:60px">ลำดับ</th><th>ชื่อหมวด</th><th style="width:90px">สถานะ</th><th style="width:110px"></th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($categories as $c): ?>
                                    <tr>
                                        <td><?= (int) $c->sort ?></td>
                                        <td>
                                            <?php if ($c->icon): ?><i class="bi <?= Html::encode($c->icon) ?> me-1"></i><?php endif; ?>
                                            <?= Html::encode($c->name) ?>
                                        </td>
                                        <td>
                                            <?= $c->is_active
                                                ? '<span class="badge text-bg-success">เปิด</span>'
                                                : '<span class="badge text-bg-secondary">ปิด</span>' ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary km-cat-edit"
                                                data-id="<?= $c->id ?>" data-name="<?= Html::encode($c->name) ?>"
                                                data-icon="<?= Html::encode($c->icon) ?>" data-color="<?= Html::encode($c->color) ?>"
                                                data-sort="<?= (int) $c->sort ?>" data-active="<?= (int) $c->is_active ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?= Html::beginForm(['delete', 'id' => $c->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ลบหมวดนี้? กิจกรรมที่อยู่ในหมวดจะกลายเป็นไม่มีหมวด');"]) ?>
                                                <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger']) ?>
                                            <?= Html::endForm() ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3" id="km-cat-form-title">เพิ่มหมวดใหม่</h2>
                    <?= Html::beginForm(['save'], 'post', ['id' => 'km-cat-form']) ?>
                        <?= Html::hiddenInput('id', '', ['id' => 'km-cat-id']) ?>
                        <div class="mb-2">
                            <label class="form-label">ชื่อหมวด <span class="text-danger">*</span></label>
                            <?= Html::textInput('name', '', ['class' => 'form-control', 'id' => 'km-cat-name', 'required' => true, 'maxlength' => 255]) ?>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">ไอคอน <span class="text-body-secondary small">(เช่น bi-people)</span></label>
                            <?= Html::textInput('icon', '', ['class' => 'form-control', 'id' => 'km-cat-icon', 'placeholder' => 'bi-...']) ?>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">สี</label>
                                <?= Html::textInput('color', '', ['class' => 'form-control', 'id' => 'km-cat-color', 'placeholder' => '#0d6efd']) ?>
                            </div>
                            <div class="col-6">
                                <label class="form-label">ลำดับ</label>
                                <?= Html::input('number', 'sort', '0', ['class' => 'form-control', 'id' => 'km-cat-sort']) ?>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <?= Html::checkbox('is_active', true, ['class' => 'form-check-input', 'id' => 'km-cat-active']) ?>
                            <label class="form-check-label" for="km-cat-active">เปิดใช้งาน</label>
                        </div>
                        <div class="d-flex gap-2">
                            <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึก', ['class' => 'btn btn-primary btn-sm']) ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="km-cat-reset">ล้างฟอร์ม</button>
                        </div>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
(function(){
    var f = document.getElementById('km-cat-form');
    if (!f) return;
    function setVal(id, v){ var el = document.getElementById(id); if (el) el.value = v; }
    document.querySelectorAll('.km-cat-edit').forEach(function(btn){
        btn.addEventListener('click', function(){
            var d = btn.dataset;
            setVal('km-cat-id', d.id); setVal('km-cat-name', d.name);
            setVal('km-cat-icon', d.icon || ''); setVal('km-cat-color', d.color || '');
            setVal('km-cat-sort', d.sort || '0');
            document.getElementById('km-cat-active').checked = (d.active === '1');
            document.getElementById('km-cat-form-title').textContent = 'แก้ไขหมวด: ' + d.name;
            f.scrollIntoView({behavior:'smooth', block:'center'});
        });
    });
    var reset = document.getElementById('km-cat-reset');
    if (reset) reset.addEventListener('click', function(){
        f.reset(); setVal('km-cat-id','');
        document.getElementById('km-cat-active').checked = true;
        document.getElementById('km-cat-form-title').textContent = 'เพิ่มหมวดใหม่';
    });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
