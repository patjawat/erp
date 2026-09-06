<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\qms\models\Standard $standard */
/** @var array $templates  key => name */
/** @var app\modules\qms\models\Standard[] $sourceStandards */

$this->title = 'นำเข้าข้อกำหนด: ' . $standard->name;
$sid = (int) $standard->id;
?>
<?php $this->beginBlock('page-title'); ?>นำเข้าข้อกำหนด<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($standard->name) ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับรายการข้อกำหนด', ['requirements', 'standard_id' => $sid], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="row g-3 justify-content-center">
        <!-- แม่แบบสำเร็จรูป -->
        <div class="col-12 col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-collection me-1"></i> แม่แบบสำเร็จรูป</div>
                <div class="card-body">
                    <p class="text-body-secondary small">เลือกโครงข้อกำหนดสำเร็จรูปมาตั้งต้น แล้วปรับแก้/เพิ่มต่อได้</p>
                    <?= Html::beginForm(['requirement-import', 'standard_id' => $sid], 'post') ?>
                        <?= Html::hiddenInput('mode', 'template') ?>
                        <div class="mb-3">
                            <?= Html::dropDownList('template_key', array_key_first($templates), $templates, ['class' => 'form-select']) ?>
                        </div>
                        <?= Html::submitButton('<i class="bi bi-download me-1"></i>นำเข้าจากแม่แบบ', [
                            'class' => 'btn btn-primary',
                            'data' => ['confirm' => 'นำเข้าข้อกำหนดจากแม่แบบนี้? (ข้อที่รหัสซ้ำจะถูกข้าม)'],
                        ]) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>

        <!-- คัดลอกจากมาตรฐานอื่น -->
        <div class="col-12 col-lg-6">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-files me-1"></i> คัดลอกจากมาตรฐานอื่น</div>
                <div class="card-body">
                    <p class="text-body-secondary small">ยกโครงข้อกำหนดจากมาตรฐานที่ทำไว้แล้วมาใช้เป็นต้นแบบ</p>
                    <?php if (empty($sourceStandards)): ?>
                        <div class="text-body-secondary"><i class="bi bi-info-circle me-1"></i>ยังไม่มีมาตรฐานอื่นที่มีข้อกำหนดให้คัดลอก</div>
                    <?php else: ?>
                        <?= Html::beginForm(['requirement-import', 'standard_id' => $sid], 'post') ?>
                            <?= Html::hiddenInput('mode', 'clone') ?>
                            <div class="mb-3">
                                <?= Html::dropDownList('source_id', null, ArrayHelper::map($sourceStandards, 'id', fn ($s) => ($s->short_name ?: $s->code) . ' — ' . $s->name), ['class' => 'form-select']) ?>
                            </div>
                            <?= Html::submitButton('<i class="bi bi-copy me-1"></i>คัดลอกข้อกำหนด', [
                                'class' => 'btn btn-outline-primary',
                                'data' => ['confirm' => 'คัดลอกข้อกำหนดจากมาตรฐานนี้? (ข้อที่รหัสซ้ำจะถูกข้าม)'],
                            ]) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <p class="small text-body-secondary mt-3"><i class="bi bi-info-circle me-1"></i>การนำเข้าเป็นแบบเพิ่มเติม (ข้อที่มีรหัสซ้ำในมาตรฐานนี้จะถูกข้าม) หลังนำเข้าปรับแก้ได้ตามปกติ</p>
</div>
