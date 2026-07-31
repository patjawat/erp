<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $pattern */
/** @var app\modules\hr\models\Organization[] $orgs */
/** @var array $codes */
/** @var string $sample */

$this->title = 'ตั้งค่า';
$this->beginBlock('page-title'); ?>แผนงาน/โครงการ<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'settings']) ?><?php $this->endBlock();
?>
<div class="pm-settings container-fluid">

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('warning')): ?>
        <div class="alert alert-warning"><?= Yii::$app->session->getFlash('warning') ?></div>
    <?php endif; ?>

    <?= Html::beginForm(['index'], 'post') ?>

    <!-- รูปแบบรหัสโครงการ -->
    <div class="card mb-3">
        <div class="card-header fw-semibold">รูปแบบรหัสโครงการอัตโนมัติ</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">รูปแบบ (Pattern)</label>
                    <input type="text" name="code_pattern" id="code_pattern" class="form-control" value="<?= Html::encode($pattern) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ตัวอย่าง</label>
                    <div class="form-control-plaintext"><code class="fs-6" id="code_sample"><?= Html::encode($sample) ?></code></div>
                </div>
            </div>
            <div class="mt-2 small text-muted">
                ตัวแปรที่ใช้ได้:
                <code>{org}</code> รหัสย่อหน่วยงาน ·
                <code>{year}</code> ปี พ.ศ. เต็ม (2569) ·
                <code>{yy}</code> ปี พ.ศ. 2 หลัก (69) ·
                <code>{sequence}</code> ลำดับรัน 4 หลัก (ต่อหน่วยงาน+ปี)
                <br>ตัวอักษรอื่น (เช่น <code>P-</code>) จะแสดงตามที่พิมพ์ &nbsp;|&nbsp; ตัวอย่าง <code>P-{org}-{yy}{sequence}</code> → <code>P-MAN-690001</code>
            </div>
        </div>
    </div>

    <!-- รหัสย่อหน่วยงาน -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">รหัสย่อหน่วยงาน</span>
            <input type="text" id="org-filter" class="form-control form-control-sm" style="max-width:240px" placeholder="ค้นหาหน่วยงาน...">
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 small mb-3">
                รหัสย่อนี้เป็น<strong>รหัสหน่วยงานกลาง</strong> (ใช้ร่วมกับระบบคลัง SOP/WI) — ใช้อักษรอังกฤษพิมพ์ใหญ่/ตัวเลข/ขีด เช่น <code>MAN</code>, <code>FMC</code>
            </div>
            <div class="table-responsive" style="max-height:520px;overflow-y:auto">
                <table class="table table-sm align-middle">
                    <thead class="table-light" style="position:sticky;top:0;z-index:1">
                        <tr>
                            <th>หน่วยงาน</th>
                            <th style="width:160px">รหัสย่อ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orgs as $org): ?>
                            <tr data-org-name="<?= Html::encode(mb_strtolower($org->name)) ?>">
                                <td>
                                    <?= str_repeat('<span class="text-muted">— </span>', max(0, (int) $org->lvl - 1)) ?>
                                    <?= Html::encode($org->name) ?>
                                </td>
                                <td>
                                    <input type="text"
                                           name="org_code[<?= $org->id ?>]"
                                           value="<?= Html::encode($codes[$org->id] ?? '') ?>"
                                           class="form-control form-control-sm text-uppercase"
                                           maxlength="20"
                                           placeholder="—">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึกการตั้งค่า', ['class' => 'btn btn-success']) ?>
    </div>

    <?= Html::endForm() ?>
</div>

<?php
$js = <<<'JS'
// ค้นหาหน่วยงาน
document.getElementById('org-filter').addEventListener('input', function(){
    var q = this.value.trim().toLowerCase();
    document.querySelectorAll('tr[data-org-name]').forEach(function(tr){
        tr.style.display = tr.getAttribute('data-org-name').indexOf(q) !== -1 ? '' : 'none';
    });
});
// พรีวิวรหัสแบบสด (แทน {sequence} ด้วย 0001, {org} คงเดิมจากเซิร์ฟเวอร์ถ้าไม่รู้)
(function(){
    var input = document.getElementById('code_pattern');
    var sample = document.getElementById('code_sample');
    var y = String(new Date().getFullYear() + 543 + (new Date().getMonth() >= 9 ? 1 : 0));
    input.addEventListener('input', function(){
        var s = input.value
            .replace(/\{org\}/g, 'MAN')
            .replace(/\{year\}/g, y)
            .replace(/\{yy\}/g, y.slice(-2))
            .replace(/\{sequence\}/g, '0001');
        sample.textContent = s;
    });
})();
JS;
$this->registerJs($js);
?>
