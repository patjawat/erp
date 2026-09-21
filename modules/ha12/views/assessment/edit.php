<?php

use app\modules\ha12\models\Ha12Assessment;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\ha12\models\Ha12Round $round */
/** @var app\modules\ha12\models\Ha12Activity $activity */
/** @var Ha12Assessment $assessment */
/** @var app\modules\ha12\models\Ha12Criteria[] $criteria */
/** @var array<int,array{type:string,id:int,rev:?int,label:string}> $available */

$this->title = 'HA12-PCT · ประเมิน';
$selectedLevels = $assessment->levelArray();
$csrf = Yii::$app->request->csrfToken;

// options ของหลักฐานที่เลือกได้ (value = type:id)
$srcOptions = [];
foreach ($available as $s) {
    $srcOptions[$s['type'] . ':' . $s['id']] = $s['label'];
}
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ประเมิน · <?= Html::encode($activity->no . '. ' . $activity->name) ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'round']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-semibold mb-0"><i class="bi bi-pencil-square me-1"></i> <?= $activity->no ?>. <?= Html::encode($activity->name) ?></h1>
            <div class="text-body-secondary small">
                <?= Html::encode($round->displayTitle()) ?>
                · <?php if ($assessment->isPublished()): ?><span class="badge bg-success-subtle text-success-emphasis">เผยแพร่แล้ว</span><?php else: ?><span class="badge bg-warning-subtle text-warning-emphasis">ร่าง</span><?php endif; ?>
            </div>
        </div>
        <?= Html::a('<i class="bi bi-arrow-left"></i> กลับรอบ', ['/ha12/round/view', 'id' => $round->id], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
    </div>

    <?php $form = Html::beginForm(['save', 'id' => $assessment->id], 'post'); ?>
    <div class="row g-3">
        <div class="col-lg-5">
            <!-- เกณฑ์ 5 ระดับ -->
            <div class="card border shadow-sm mb-3">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-ui-checks me-1"></i> เกณฑ์ระดับการพัฒนา (เลือกได้หลายระดับ)</div>
                <div class="card-body">
                    <?php foreach ($criteria as $c): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="levels[]" value="<?= $c->level ?>" id="lv<?= $c->level ?>" <?= in_array((int) $c->level, $selectedLevels, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="lv<?= $c->level ?>">
                                <span class="fw-semibold">ระดับ <?= $c->level ?><?= $c->title ? ' · ' . Html::encode($c->title) : '' ?></span>
                                <?php if ($c->description): ?><div class="small text-body-secondary"><?= Html::encode($c->description) ?></div><?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border shadow-sm mb-3">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-chat-left-text me-1"></i> เหตุผล / สรุป</div>
                <div class="card-body">
                    <label class="form-label small fw-semibold">เหตุผลประกอบการเลือกระดับ</label>
                    <?= Html::textarea('reason', (string) $assessment->reason, ['class' => 'form-control mb-3', 'rows' => 3]) ?>
                    <label class="form-label small fw-semibold">สรุป / ข้อเสนอแนะ</label>
                    <?= Html::textarea('summary_text', (string) $assessment->summary_text, ['class' => 'form-control', 'rows' => 3]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางสรุป -->
    <div class="card border shadow-sm">
        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-table me-1"></i> ตารางสรุป</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-row-btn"><i class="bi bi-plus-lg"></i> เพิ่มแถว</button>
        </div>
        <div class="card-body">
            <?php if (!$assessment->rows): ?>
                <p class="text-body-secondary small mb-0">ยังไม่มีแถวสรุป — กด “เพิ่มแถว” แล้วกรอกข้อมูล จากนั้นผูกหลักฐานจากการทบทวนในรอบ</p>
            <?php else: ?>
                <?php foreach ($assessment->rows as $row): ?>
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">หน่วยงาน</label>
                                <?= Html::input('text', "row[{$row->id}][unit_name]", $row->unit_name, ['class' => 'form-control form-control-sm']) ?>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold mb-1">เรื่อง/โรค</label>
                                <?= Html::input('text', "row[{$row->id}][topic]", $row->topic, ['class' => 'form-control form-control-sm']) ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold mb-1">ผลการปรับปรุง</label>
                                <?= Html::textarea("row[{$row->id}][improvement]", (string) $row->improvement, ['class' => 'form-control form-control-sm', 'rows' => 2]) ?>
                            </div>
                        </div>
                        <!-- หลักฐานของแถว -->
                        <div class="mt-2">
                            <div class="small fw-semibold text-body-secondary mb-1">หลักฐาน (จากการทบทวนในรอบ)</div>
                            <?php if ($row->sources): ?>
                                <ul class="list-group list-group-flush mb-2">
                                    <?php foreach ($row->sources as $src): ?>
                                        <li class="list-group-item px-0 py-1 d-flex justify-content-between align-items-center">
                                            <span class="small"><i class="bi bi-paperclip"></i> <?= Html::encode($src->label) ?><?= $src->source_rev ? ' <span class="text-body-tertiary">(รุ่น ' . $src->source_rev . ')</span>' : '' ?></span>
                                            <?= Html::a('<i class="bi bi-x"></i>', ['delete-source', 'id' => $src->id], ['class' => 'btn btn-sm btn-outline-danger py-0', 'aria-label' => 'ลบหลักฐาน', 'data' => ['method' => 'post', 'confirm' => 'ยกเลิกหลักฐานนี้?']]) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <?php if ($srcOptions): ?>
                                    <select class="form-select form-select-sm src-select" data-row="<?= $row->id ?>" style="max-width:32rem;">
                                        <option value="">— เลือกหลักฐาน —</option>
                                        <?php foreach ($srcOptions as $val => $label): ?><option value="<?= Html::encode($val) ?>"><?= Html::encode($label) ?></option><?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-src-btn" data-row="<?= $row->id ?>"><i class="bi bi-plus"></i> ผูก</button>
                                <?php else: ?>
                                    <span class="small text-body-tertiary">ไม่มีการทบทวนในช่วงรอบนี้ให้เลือก</span>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-auto del-row-btn" data-row="<?= $row->id ?>"><i class="bi bi-trash"></i> ลบแถว</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between gap-2 mt-3">
        <?= Html::submitButton('<i class="bi bi-save"></i> บันทึกร่าง', ['class' => 'btn btn-primary']) ?>
        <div class="d-flex gap-2">
            <?php if ($assessment->isPublished()): ?>
                <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i> ยกเลิกเผยแพร่', ['unpublish', 'id' => $assessment->id], ['class' => 'btn btn-outline-secondary', 'data' => ['method' => 'post']]) ?>
            <?php else: ?>
                <?= Html::a('<i class="bi bi-send"></i> เผยแพร่', ['publish', 'id' => $assessment->id], ['class' => 'btn btn-success', 'data' => ['method' => 'post', 'confirm' => 'เผยแพร่ผลประเมินกิจกรรมนี้?']]) ?>
            <?php endif; ?>
        </div>
    </div>
    <?= Html::endForm(); ?>
</div>

<?php
$addRowUrl = Url::to(['add-row', 'id' => $assessment->id]);
$delRowUrl = Url::to(['delete-row']);
$addSrcUrl = Url::to(['add-source']);
$this->registerJs(<<<JS
function ha12PostForm(action, params){
    var f=document.createElement('form'); f.method='post'; f.action=action;
    var c=document.createElement('input'); c.type='hidden'; c.name='_csrf'; c.value='{$csrf}'; f.appendChild(c);
    Object.keys(params).forEach(function(k){ var i=document.createElement('input'); i.type='hidden'; i.name=k; i.value=params[k]; f.appendChild(i); });
    document.body.appendChild(f); f.submit();
}
document.getElementById('add-row-btn').addEventListener('click', function(){ ha12PostForm('{$addRowUrl}', {}); });
document.querySelectorAll('.del-row-btn').forEach(function(b){ b.addEventListener('click', function(){ if(confirm('ลบแถวสรุปนี้?')) ha12PostForm('{$delRowUrl}?id='+this.getAttribute('data-row'), {}); }); });
document.querySelectorAll('.add-src-btn').forEach(function(b){ b.addEventListener('click', function(){
    var rid=this.getAttribute('data-row');
    var sel=document.querySelector('.src-select[data-row="'+rid+'"]');
    if(!sel || !sel.value){ return; }
    var parts=sel.value.split(':');
    ha12PostForm('{$addSrcUrl}?id='+rid, {source_type:parts[0], source_id:parts[1]});
}); });
JS); ?>
