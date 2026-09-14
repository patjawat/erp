<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmActivity $activity */
/** @var bool $canManage */

$this->title = $activity->title;
$statusTone = ['draft' => 'secondary', 'published' => 'success'];
$time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '') . ($activity->end_time ? ' - ' . substr($activity->end_time, 0, 5) : ''));
?>
<?php $this->beginBlock('page-title'); ?>รายละเอียดกิจกรรม<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลังกิจกรรม KM<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/km/menu', ['active' => 'activity']) ?></div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash('success')) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับทะเบียน', ['index', 'fy' => $activity->fiscal_year], ['class' => 'btn btn-sm btn-outline-secondary mb-2']) ?>
            <h1 class="h4 fw-semibold mb-1"><?= Html::encode($activity->title) ?></h1>
            <div class="d-flex gap-1 flex-wrap">
                <?php if ($activity->category): ?><span class="badge text-bg-light border"><?= Html::encode($activity->category->name) ?></span><?php endif; ?>
                <span class="badge text-bg-<?= $statusTone[$activity->status] ?? 'secondary' ?>"><?= Html::encode($activity->statusLabel()) ?></span>
                <span class="badge text-bg-light border">ปีงบ <?= $activity->fiscal_year ?></span>
            </div>
        </div>
        <?php if ($canManage): ?>
            <div class="d-flex gap-2">
                <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $activity->id], ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::beginForm(['delete', 'id' => $activity->id], 'post', ['onsubmit' => "return confirm('ยืนยันลบกิจกรรมนี้?');", 'class' => 'd-inline']) ?>
                    <?= Html::submitButton('<i class="bi bi-trash me-1"></i> ลบ', ['class' => 'btn btn-sm btn-outline-danger']) ?>
                <?= Html::endForm() ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border shadow-sm mb-3">
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-3 text-body-secondary">หน่วยงานเจ้าภาพ</dt>
                        <dd class="col-sm-9"><?= $activity->ownerUnit ? Html::encode($activity->ownerUnit->name) : '—' ?></dd>
                        <dt class="col-sm-3 text-body-secondary">วันที่จัด</dt>
                        <dd class="col-sm-9"><?= $activity->activity_date ? AppHelper::convertToThai($activity->activity_date) : '—' ?><?= $time ? ' &nbsp; เวลา ' . Html::encode($time) . ' น.' : '' ?></dd>
                        <dt class="col-sm-3 text-body-secondary">สถานที่</dt>
                        <dd class="col-sm-9"><?= $activity->location ? Html::encode($activity->location) : '—' ?></dd>
                    </dl>
                </div>
            </div>

            <?php if ($activity->summary || $activity->objective || $activity->detail): ?>
            <div class="card border shadow-sm mb-3">
                <div class="card-body">
                    <?php if ($activity->summary): ?>
                        <h2 class="h6 fw-semibold">สรุปย่อ</h2>
                        <p class="text-body-secondary"><?= nl2br(Html::encode($activity->summary)) ?></p>
                    <?php endif; ?>
                    <?php if ($activity->objective): ?>
                        <h2 class="h6 fw-semibold">วัตถุประสงค์</h2>
                        <p class="text-body-secondary"><?= nl2br(Html::encode($activity->objective)) ?></p>
                    <?php endif; ?>
                    <?php if ($activity->detail): ?>
                        <h2 class="h6 fw-semibold">รายละเอียด / ถอดบทเรียน</h2>
                        <p class="text-body-secondary mb-0"><?= nl2br(Html::encode($activity->detail)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- คลังภาพ -->
            <div class="card border shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-semibold mb-0"><i class="bi bi-images me-1"></i> คลังภาพ <span class="text-body-secondary fw-normal">(<?= count($activity->photos) ?>)</span></h2>
                    </div>

                    <?php if ($canManage): ?>
                        <?= Html::beginForm(['upload-photos', 'id' => $activity->id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'mb-3']) ?>
                            <div class="input-group input-group-sm">
                                <?= Html::fileInput('photos[]', null, ['class' => 'form-control', 'multiple' => true, 'accept' => 'image/*', 'required' => true]) ?>
                                <?= Html::submitButton('<i class="bi bi-upload me-1"></i> อัปโหลด', ['class' => 'btn btn-primary']) ?>
                            </div>
                            <div class="form-text">เลือกได้หลายรูป · ไฟล์ละไม่เกิน 8 MB · jpg/png/webp/gif</div>
                        <?= Html::endForm() ?>
                    <?php endif; ?>

                    <?php if (!$activity->photos): ?>
                        <div class="text-body-secondary small">ยังไม่มีรูปในกิจกรรมนี้</div>
                    <?php else: ?>
                        <div class="row g-2">
                            <?php foreach ($activity->photos as $p): ?>
                                <?php $isCover = ((int) $activity->cover_photo_id === (int) $p->id); ?>
                                <div class="col-6 col-md-4">
                                    <div class="position-relative border rounded overflow-hidden" style="aspect-ratio:1/1;background:#eef1f6">
                                        <a href="<?= Url::to(['photo', 'id' => $p->id]) ?>" target="_blank" rel="noopener">
                                            <img src="<?= Url::to(['photo', 'id' => $p->id, 'thumb' => 1]) ?>" alt="<?= Html::encode($p->caption ?: $p->file_name) ?>" style="width:100%;height:100%;object-fit:cover">
                                        </a>
                                        <?php if ($isCover): ?>
                                            <span class="badge text-bg-warning position-absolute top-0 start-0 m-1"><i class="bi bi-star-fill me-1"></i>ปก</span>
                                        <?php endif; ?>
                                        <?php if ($canManage): ?>
                                            <div class="position-absolute bottom-0 end-0 m-1 d-flex gap-1">
                                                <?php if (!$isCover): ?>
                                                    <?= Html::beginForm(['set-cover', 'id' => $p->id], 'post', ['class' => 'd-inline']) ?>
                                                        <?= Html::submitButton('<i class="bi bi-star"></i>', ['class' => 'btn btn-sm btn-light border', 'title' => 'ตั้งเป็นรูปปก']) ?>
                                                    <?= Html::endForm() ?>
                                                <?php endif; ?>
                                                <?= Html::beginForm(['delete-photo', 'id' => $p->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ลบรูปนี้?');"]) ?>
                                                    <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-light border text-danger', 'title' => 'ลบรูป']) ?>
                                                <?= Html::endForm() ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- ลิงก์หลักฐาน -->
            <div class="card border shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold"><i class="bi bi-link-45deg me-1"></i> หลักฐานที่ผูก <span class="text-body-secondary fw-normal">(<?= count($activity->links) ?>)</span></h2>

                    <?php if (!$activity->links): ?>
                        <div class="text-body-secondary small mb-2">ยังไม่ได้ผูกหลักฐาน</div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush mb-2">
                            <?php foreach ($activity->links as $lk): ?>
                                <?php $out = \app\modules\km\services\KmLinkService::outUrl($lk->item_type, $lk->ref_id); ?>
                                <li class="list-group-item px-0 d-flex align-items-start gap-2">
                                    <i class="bi <?= \app\modules\km\services\KmLinkService::icon($lk->item_type) ?> mt-1 text-body-secondary"></i>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="small text-body-secondary"><?= Html::encode($lk->typeLabel()) ?></div>
                                        <div>
                                            <?php if ($out): ?>
                                                <?= Html::a(Html::encode($lk->ref_label ?: ('#' . $lk->ref_id)), $out, ['target' => '_blank', 'rel' => 'noopener']) ?>
                                            <?php else: ?>
                                                <?= Html::encode($lk->ref_label ?: ('#' . $lk->ref_id)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($lk->note): ?><div class="small text-body-secondary"><?= Html::encode($lk->note) ?></div><?php endif; ?>
                                    </div>
                                    <?php if ($canManage): ?>
                                        <?= Html::beginForm(['delete-link', 'id' => $lk->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ยกเลิกการผูกรายการนี้?');"]) ?>
                                            <?= Html::submitButton('<i class="bi bi-x-lg"></i>', ['class' => 'btn btn-sm btn-link text-danger p-0', 'title' => 'ยกเลิกการผูก']) ?>
                                        <?= Html::endForm() ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if ($canManage): ?>
                        <hr class="my-2">
                        <?= Html::beginForm(['add-link', 'id' => $activity->id], 'post', ['id' => 'km-link-form']) ?>
                            <div class="mb-2">
                                <label class="form-label small mb-1">ประเภทหลักฐาน</label>
                                <?php
                                $typeOpts = [];
                                foreach (\app\modules\km\services\KmLinkService::enabledTypes() as $t) {
                                    $typeOpts[$t] = \app\modules\km\models\KmActivityLink::typeLabels()[$t];
                                }
                                ?>
                                <?= Html::dropDownList('item_type', null, $typeOpts, ['class' => 'form-select form-select-sm', 'id' => 'km-link-type']) ?>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small mb-1">ค้นหารายการ</label>
                                <input type="text" class="form-control form-control-sm" id="km-link-search" placeholder="พิมพ์เพื่อค้นหา...">
                            </div>
                            <div class="mb-2">
                                <?= Html::dropDownList('ref_id', null, [], ['class' => 'form-select form-select-sm', 'id' => 'km-link-target', 'size' => 6]) ?>
                            </div>
                            <div class="mb-2">
                                <?= Html::textInput('note', '', ['class' => 'form-control form-control-sm', 'placeholder' => 'หมายเหตุ (ถ้ามี)', 'maxlength' => 255]) ?>
                            </div>
                            <?= Html::submitButton('<i class="bi bi-link-45deg me-1"></i> ผูกหลักฐาน', ['class' => 'btn btn-sm btn-primary w-100', 'id' => 'km-link-submit']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($canManage): ?>
<?php
$optUrl = Url::to(['link-options']);
$js = <<<JS
(function(){
    var typeEl = document.getElementById('km-link-type');
    var searchEl = document.getElementById('km-link-search');
    var targetEl = document.getElementById('km-link-target');
    if (!typeEl || !targetEl) return;
    var timer = null;
    function load(){
        var url = '$optUrl' + (('$optUrl'.indexOf('?')>=0)?'&':'?') + 'type=' + encodeURIComponent(typeEl.value) + '&q=' + encodeURIComponent(searchEl.value || '');
        targetEl.innerHTML = '<option>กำลังโหลด...</option>';
        fetch(url, {headers: {'X-Requested-With':'XMLHttpRequest'}})
            .then(function(r){ return r.json(); })
            .then(function(d){
                targetEl.innerHTML = '';
                var items = (d && d.items) || [];
                if (!items.length){ targetEl.innerHTML = '<option value="">— ไม่พบรายการ —</option>'; return; }
                items.forEach(function(it){
                    var o = document.createElement('option');
                    o.value = it.id; o.textContent = it.label;
                    targetEl.appendChild(o);
                });
            })
            .catch(function(){ targetEl.innerHTML = '<option value="">— โหลดไม่สำเร็จ —</option>'; });
    }
    typeEl.addEventListener('change', function(){ searchEl.value=''; load(); });
    searchEl.addEventListener('input', function(){ clearTimeout(timer); timer = setTimeout(load, 300); });
    load();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
<?php endif; ?>
