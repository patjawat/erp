<?php

use app\modules\qms\models\RequirementLink;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\qms\models\Requirement $req */
/** @var app\modules\qms\models\Standard[] $others */
/** @var array $current  standard_id => relation */

$this->title = 'เชื่อมโยงข้อกำหนดข้ามมาตรฐาน';
$relOptions = ['' => 'ไม่เกี่ยวข้อง'] + RequirementLink::relationLabels();
?>
<?php $this->beginBlock('page-title'); ?>เชื่อมโยงข้อกำหนด<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($req->standard->name ?? '') ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับรายการข้อกำหนด', ['requirements', 'standard_id' => $req->standard_id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm">
                <div class="card-header bg-body-tertiary">
                    <div class="fw-semibold"><i class="bi bi-diagram-3 me-1"></i> เชื่อมโยงกับมาตรฐานอื่น</div>
                    <div class="small text-body-secondary mt-1">
                        <?php if ($req->code): ?><span class="badge text-bg-light border me-1"><?= Html::encode($req->code) ?></span><?php endif; ?>
                        <?= Html::encode($req->title) ?>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-body-secondary small">ระบุว่าข้อกำหนดนี้ไปสนองมาตรฐานอื่นใดบ้าง (เชื่อมโยงโดยตรง = ตรงประเด็น, บางส่วน = เกี่ยวข้องบางประเด็น)</p>
                    <?php if (empty($others)): ?>
                        <div class="text-body-secondary">ยังไม่มีมาตรฐานอื่นในระบบ</div>
                    <?php else: ?>
                        <?= Html::beginForm(['requirement-links', 'id' => $req->id], 'post') ?>
                            <div class="list-group mb-3">
                                <?php foreach ($others as $std): ?>
                                    <div class="list-group-item d-flex align-items-center gap-3">
                                        <div class="flex-grow-1">
                                            <span class="badge me-1" style="background: <?= Html::encode($std->color ?: '#1a508e') ?>1a; color: <?= Html::encode($std->color ?: '#1a508e') ?>;"><?= Html::encode($std->short_name ?: $std->code) ?></span>
                                            <?= Html::encode($std->name) ?>
                                        </div>
                                        <div style="width: 180px;">
                                            <?= Html::dropDownList("rel[{$std->id}]", $current[(int) $std->id] ?? '', $relOptions, ['class' => 'form-select form-select-sm']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึกการเชื่อมโยง', ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('ยกเลิก', ['requirements', 'standard_id' => $req->standard_id], ['class' => 'btn btn-outline-secondary']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
