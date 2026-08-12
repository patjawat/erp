<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\AppHelper;
use app\modules\purchase\models\Bond;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Bond $model */

$this->title = 'บันทึกการคืน/การยึดหลักประกัน';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนหลักประกัน', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'บันทึกการคืน';

$date = function ($value) {
    return $value ? AppHelper::convertToThai($value) : '—';
};
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-box-arrow-up"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?= Html::encode($model->doc_no ?: '—') ?> · <?= Html::encode($model->title) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับทะเบียนหลักประกัน', ['index'], [
    'class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3',
]) ?>
<?php $this->endBlock(); ?>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i>
                    การคืนหลักประกันต้องมีหลักฐานกำกับ ระบบจึงบันทึกวันที่คืนและเลขที่หนังสือคืนไว้ในทะเบียน
                    ไม่ใช่แค่เปลี่ยนสถานะ — ทะเบียนที่พิมพ์ออกไปต้องตอบผู้ตรวจสอบได้ว่าคืนเมื่อไรด้วยหนังสือฉบับใด
                </div>

                <?php $form = ActiveForm::begin(['id' => 'bond-return-form']); ?>

                <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <?= $form->field($model, 'status')->dropDownList([
                            Bond::STATUS_RETURNED => Bond::statusList()[Bond::STATUS_RETURNED],
                            Bond::STATUS_SEIZED => Bond::statusList()[Bond::STATUS_SEIZED],
                        ])->label('ผลการดำเนินการ') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'return_date')->input('date')
                            ->label('วันที่คืน/วันที่ยึด') ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'return_doc_no')->textInput([
                            'maxlength' => true,
                            'placeholder' => 'เช่น ลย 0032.301/1234 ลว. 12 ส.ค. 2569',
                        ]) ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'return_note')->textarea(['rows' => 3])
                            ->hint('เช่น คืนให้ผู้แทนผู้ขายรับไปด้วยตนเอง หรือเหตุผลที่ยึดเป็นรายได้แผ่นดิน') ?>
                    </div>
                </div>

                <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">หลักประกันที่กำลังปิดเรื่อง</h6></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">ประเภท</dt>
                    <dd class="col-7"><?= Html::encode($model->typeName()) ?></dd>

                    <dt class="col-5">รูปแบบ</dt>
                    <dd class="col-7"><?= Html::encode($model->bondFormName()) ?></dd>

                    <dt class="col-5">เลขที่หนังสือ</dt>
                    <dd class="col-7"><?= Html::encode($model->doc_ref ?: '—') ?></dd>

                    <dt class="col-5">ธนาคาร/ผู้ออก</dt>
                    <dd class="col-7"><?= Html::encode($model->issuer ?: '—') ?></dd>

                    <dt class="col-5">วงเงิน</dt>
                    <dd class="col-7 fw-semibold"><?= number_format((float) $model->amount, 2) ?> บาท</dd>

                    <dt class="col-5">ผู้วางหลักประกัน</dt>
                    <dd class="col-7"><?= Html::encode($model->partyName()) ?></dd>

                    <dt class="col-5">วางเมื่อ</dt>
                    <dd class="col-7"><?= $date($model->place_date) ?></dd>

                    <dt class="col-5">สิ้นอายุ</dt>
                    <dd class="col-7">
                        <?= $date($model->expiry_date) ?>
                        <?php if ($model->isExpired()): ?>
                            <span class="badge text-bg-danger ms-1">สิ้นอายุแล้ว</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-5">ผูกกับเอกสาร</dt>
                    <dd class="col-7">
                        <?php if ($url = $model->sourceUrl()): ?>
                            <?= Html::a(Html::encode($model->sourceLabel()), $url) ?>
                        <?php else: ?>
                            <?= Html::encode($model->sourceLabel()) ?>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
