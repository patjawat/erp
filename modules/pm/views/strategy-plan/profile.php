<?php

use yii\helpers\Html;
use app\components\RichText;

app\assets\RichTextAsset::register($this);

/** @var yii\web\View $this */
/** @var app\modules\pm\models\StrategyPlan $model */
/** @var array $site  ข้อมูลองค์กรจาก settings/company (Categorise 'site' data_json) */
/** @var string|null $logo */
/** @var bool $editing @var bool $canManage */

$this->title = 'ข้อมูลโรงพยาบาล';
$editing = $editing ?? false;
$canManage = $canManage ?? false;
$planEditable = $model->isEditable();

$v = static fn ($key, $default = '-') => (isset($site[$key]) && trim((string) $site[$key]) !== '') ? $site[$key] : $default;
$raw = static fn ($key) => isset($site[$key]) ? (string) $site[$key] : '';

$this->beginBlock('page-title'); ?>แผนยุทธศาสตร์<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'strategy']) ?><?php $this->endBlock();
?>

<?php foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $cls): ?>
    <?php if (Yii::$app->session->hasFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($key)) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($editing): ?><?= Html::beginForm(['profile', 'id' => $model->id], 'post') ?><?php endif; ?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <?php if ($logo): ?>
            <?= Html::img($logo, ['class' => 'rounded shadow-sm object-fit-cover', 'style' => 'width:64px;height:64px']) ?>
        <?php endif; ?>
        <div>
            <h1 class="h3 mb-1"><?= Html::encode($v('company_name', 'ข้อมูลโรงพยาบาล')) ?></h1>
            <p class="text-body-secondary mb-0">Hospital Profile · <?= Html::encode($model->name) ?></p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if ($editing): ?>
            <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ยกเลิก', ['profile', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        <?php else: ?>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="bi bi-pencil-square me-1"></i> แก้ไข', ['profile', 'id' => $model->id, 'edit' => 1], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับสู่แผนยุทธศาสตร์', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php /* ข้อมูลทั่วไป */ ?>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
            <h5 class="fw-bold mb-3">ข้อมูลทั่วไป</h5>
            <?php if ($editing): ?>
                <div class="mb-2"><label class="form-label small mb-1">ที่อยู่</label><?= Html::textarea('Site[address]', $raw('address'), ['class' => 'form-control form-control-sm', 'rows' => 2]) ?></div>
                <div class="mb-2"><label class="form-label small mb-1">จังหวัด</label><?= Html::textInput('Site[province]', $raw('province'), ['class' => 'form-control form-control-sm']) ?></div>
                <div class="mb-2"><label class="form-label small mb-1">โทรศัพท์</label><?= Html::textInput('Site[phone]', $raw('phone'), ['class' => 'form-control form-control-sm']) ?></div>
                <div class="mb-2"><label class="form-label small mb-1">Email</label><?= Html::textInput('Site[email]', $raw('email'), ['class' => 'form-control form-control-sm']) ?></div>
                <div class="mb-0"><label class="form-label small mb-1">เว็บไซต์</label><?= Html::textInput('Site[website]', $raw('website'), ['class' => 'form-control form-control-sm']) ?></div>
            <?php else: ?>
                <div class="mb-2"><span class="fw-semibold">ที่อยู่:</span> <?= nl2br(Html::encode($v('address'))) ?></div>
                <div class="mb-2"><span class="fw-semibold">จังหวัด:</span> <?= Html::encode($v('province')) ?></div>
                <div class="mb-2"><span class="fw-semibold">โทรศัพท์:</span> <?= Html::encode($v('phone')) ?></div>
                <div class="mb-2"><span class="fw-semibold">Email:</span> <?= Html::encode($v('email')) ?></div>
                <div class="mb-0"><span class="fw-semibold">เว็บไซต์:</span> <?= Html::encode($v('website')) ?></div>
            <?php endif; ?>
        </div></div>
    </div>

    <?php /* สถิติ */ ?>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
            <h5 class="fw-bold mb-3">สถิติ</h5>
            <?php if ($editing): ?>
                <div class="mb-2"><label class="form-label small mb-1">จำนวนเตียง</label><?= Html::input('number', 'Site[beds]', $raw('beds'), ['class' => 'form-control form-control-sm']) ?></div>
                <div class="mb-2"><label class="form-label small mb-1">ปีก่อตั้ง (พ.ศ.)</label><?= Html::input('number', 'Site[established_year]', $raw('established_year'), ['class' => 'form-control form-control-sm']) ?></div>
                <div class="mb-0"><label class="form-label small mb-1">การรับรองคุณภาพ</label><?= Html::textInput('Site[ha_level]', $raw('ha_level'), ['class' => 'form-control form-control-sm', 'placeholder' => 'เช่น HA Reaccreditation ขั้นที่ 3']) ?></div>
            <?php else: ?>
                <div class="mb-2"><span class="fw-semibold">จำนวนเตียง:</span> <?= Html::encode($v('beds')) ?><?= $v('beds') !== '-' ? ' เตียง' : '' ?></div>
                <div class="mb-2"><span class="fw-semibold">ก่อตั้ง:</span> <?= $v('established_year') !== '-' ? 'พ.ศ. ' . Html::encode($v('established_year')) : '-' ?></div>
                <div class="mb-0"><span class="fw-semibold">การรับรองคุณภาพ:</span> <?= Html::encode($v('ha_level')) ?></div>
            <?php endif; ?>
        </div></div>
    </div>

    <?php /* ค่านิยมองค์กร */ ?>
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
            <h5 class="fw-bold mb-3">ค่านิยมองค์กร</h5>
            <?php if ($editing): ?>
                <?= Html::textarea('Site[core_values]', $raw('core_values'), ['class' => 'form-control form-control-sm', 'rows' => 6, 'placeholder' => "หนึ่งค่านิยมต่อบรรทัด เช่น\nN - Nice (เป็นคนดี มีจิตบริการ)"]) ?>
                <div class="form-text">พิมพ์หนึ่งค่านิยมต่อบรรทัด รูปแบบ "อักษรย่อ - คำอธิบาย"</div>
            <?php else: ?>
                <?php
                $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw('core_values'))), static fn ($l) => $l !== ''));
                ?>
                <?php if ($lines): ?>
                    <?php foreach ($lines as $line): $parts = preg_split('/\s[-:]\s/', $line, 2); ?>
                        <div class="mb-2">
                            <?php if (count($parts) === 2): ?>
                                <span class="fw-bold"><?= Html::encode($parts[0]) ?> -</span> <?= Html::encode($parts[1]) ?>
                            <?php else: ?><?= Html::encode($line) ?><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-body-secondary">ยังไม่ได้ระบุ</div>
                <?php endif; ?>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <?php /* วิสัยทัศน์ — เขียนกลับแผน */ ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100 border-top border-4 border-primary"><div class="card-body p-4">
            <h4 class="fw-bold mb-3 text-primary"><i class="bi bi-eye me-2"></i>วิสัยทัศน์ (Vision)</h4>
            <?php if ($editing && $planEditable): ?>
                <?= Html::textarea('vision', $model->vision, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'ระบุวิสัยทัศน์...']) ?>
            <?php else: ?>
                <div class="fs-5 erp-richtext"><?= $model->vision ? RichText::render($model->vision) : '<span class="text-body-secondary">ยังไม่ได้ระบุ</span>' ?></div>
                <?php if ($editing && !$planEditable): ?><div class="form-text text-warning">แผนถูกประกาศใช้แล้ว แก้วิสัยทัศน์ไม่ได้</div><?php endif; ?>
            <?php endif; ?>
        </div></div>
    </div>

    <?php /* พันธกิจ — เขียนกลับแผน */ ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100 border-top border-4 border-danger"><div class="card-body p-4">
            <h4 class="fw-bold mb-3 text-danger"><i class="bi bi-bullseye me-2"></i>พันธกิจ (Mission)</h4>
            <?php if ($editing && $planEditable): ?>
                <div id="mission-rows" class="d-grid gap-2 mb-2">
                    <?php $i = 0; foreach ($model->missions as $mission): ?>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><?= Html::encode($mission->code) ?></span>
                            <?= Html::hiddenInput("Missions[$i][id]", $mission->id) ?>
                            <?= Html::textInput("Missions[$i][name]", $mission->name, ['class' => 'form-control', 'placeholder' => 'ข้อความพันธกิจ (เว้นว่าง = ลบ ถ้าไม่มีโครงสร้างใต้)']) ?>
                        </div>
                    <?php $i++; endforeach; ?>
                </div>
                <template id="mission-tpl">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-plus"></i></span>
                        <input type="text" name="Missions[__i__][name]" class="form-control" placeholder="พันธกิจใหม่">
                    </div>
                </template>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="mission-add"><i class="bi bi-plus-lg"></i> เพิ่มพันธกิจ</button>
                <div class="form-text">ลบพันธกิจได้เฉพาะที่ยังไม่มีประเด็นยุทธศาสตร์ใต้มัน (เว้นข้อความว่างเพื่อลบ)</div>
            <?php else: ?>
                <?php if ($model->missions): ?>
                    <ol class="ps-3 mb-0">
                        <?php foreach ($model->missions as $mission): ?><li class="mb-2"><?= Html::encode($mission->name) ?></li><?php endforeach; ?>
                    </ol>
                <?php else: ?><div class="text-body-secondary">ยังไม่มีพันธกิจในชุดแผนนี้</div><?php endif; ?>
                <?php if ($editing && !$planEditable): ?><div class="form-text text-warning">แผนถูกประกาศใช้แล้ว แก้พันธกิจไม่ได้</div><?php endif; ?>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<?php if ($editing): ?>
    <div class="d-flex gap-2 mt-4">
        <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึกข้อมูลโรงพยาบาล', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ยกเลิก', ['profile', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?= Html::endForm() ?>
    <?php
    $startIndex = count($model->missions);
    $this->registerJs(<<<JS
(function(){
    var next = $startIndex;
    var btn = document.getElementById('mission-add');
    var tpl = document.getElementById('mission-tpl');
    var box = document.getElementById('mission-rows');
    if(btn && tpl && box){
        btn.addEventListener('click', function(){
            var html = tpl.innerHTML.replace(/__i__/g, next++);
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            box.appendChild(wrap.firstChild);
        });
    }
})();
JS);
    ?>
<?php endif; ?>
