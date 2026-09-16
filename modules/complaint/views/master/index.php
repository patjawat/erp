<?php

use app\modules\complaint\models\ComplaintMaster;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $group */
/** @var ComplaintMaster[] $items */

$this->title = 'ตัวเลือกกลาง (Master Data)';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ตั้งค่าตัวเลือกของระบบรับเรื่องร้องเรียน<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/complaint/menu', ['active' => 'registry']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $tone): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?>
            <div class="alert alert-<?= $tone ?> py-2 small"><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach (ComplaintMaster::GROUPS as $g => $label): ?>
            <a href="<?= Url::to(['index', 'group' => $g]) ?>" class="btn btn-sm rounded-pill <?= $group === $g ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= Html::encode($label) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><?= Html::encode(ComplaintMaster::GROUPS[$group]) ?></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th style="width:70px">ลำดับ</th><th>ชื่อรายการ</th><th style="width:120px">รหัส</th><th style="width:90px" class="text-center">ใช้งาน</th><th style="width:110px"></th></tr>
                        </thead>
                        <tbody>
                            <?php if (!$items): ?>
                                <tr><td colspan="5" class="text-center text-body-secondary py-3">— ยังไม่มีรายการ —</td></tr>
                            <?php endif; ?>
                            <?php foreach ($items as $it): ?>
                                <tr class="<?= $it->is_active ? '' : 'opacity-50' ?>">
                                    <td class="text-body-secondary"><?= (int) $it->sort ?></td>
                                    <td><?= Html::encode($it->name) ?></td>
                                    <td class="small text-body-secondary"><?= Html::encode($it->code ?: '—') ?></td>
                                    <td class="text-center">
                                        <?= Html::beginForm(['toggle', 'id' => $it->id], 'post', ['class' => 'd-inline']) ?>
                                            <button class="btn btn-sm border-0 <?= $it->is_active ? 'text-success' : 'text-secondary' ?>" title="สลับสถานะ"><i class="bi bi-<?= $it->is_active ? 'toggle-on' : 'toggle-off' ?> fs-5"></i></button>
                                        <?= Html::endForm() ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary border-0 js-edit"
                                            data-id="<?= $it->id ?>" data-name="<?= Html::encode($it->name) ?>" data-code="<?= Html::encode($it->code) ?>" data-sort="<?= (int) $it->sort ?>"><i class="bi bi-pencil"></i></button>
                                        <?= Html::beginForm(['delete', 'id' => $it->id], 'post', ['class' => 'd-inline', 'onsubmit' => 'return confirm("ลบรายการนี้?")']) ?>
                                            <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger border-0']) ?>
                                        <?= Html::endForm() ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><span id="form-title">เพิ่มรายการ</span></div>
                <div class="card-body">
                    <?= Html::beginForm(['save'], 'post', ['id' => 'master-form']) ?>
                        <?= Html::hiddenInput('group', $group) ?>
                        <?= Html::hiddenInput('id', '', ['id' => 'm-id']) ?>
                        <div class="mb-2">
                            <label class="form-label small">ชื่อรายการ <span class="text-danger">*</span></label>
                            <?= Html::textInput('name', '', ['class' => 'form-control form-control-sm', 'id' => 'm-name', 'required' => true]) ?>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">รหัส (ถ้ามี)</label>
                            <?= Html::textInput('code', '', ['class' => 'form-control form-control-sm', 'id' => 'm-code']) ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">ลำดับ</label>
                            <?= Html::input('number', 'sort', '', ['class' => 'form-control form-control-sm', 'id' => 'm-sort']) ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึก', ['class' => 'btn btn-primary btn-sm']) ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="m-reset">ล้างฟอร์ม</button>
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
  var f=document.getElementById('master-form');
  var setVal=function(id,v){ document.getElementById(id).value=v||''; };
  document.querySelectorAll('.js-edit').forEach(function(b){
    b.addEventListener('click', function(){
      setVal('m-id', b.dataset.id); setVal('m-name', b.dataset.name);
      setVal('m-code', b.dataset.code); setVal('m-sort', b.dataset.sort);
      document.getElementById('form-title').textContent='แก้ไขรายการ';
      document.getElementById('m-name').focus();
    });
  });
  document.getElementById('m-reset').addEventListener('click', function(){
    f.reset(); setVal('m-id',''); document.getElementById('form-title').textContent='เพิ่มรายการ';
  });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
