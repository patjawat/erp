<?php

/**
 * ฟอร์มสรุปผลประชุม/อบรม (เปิดเป็น modal จากทะเบียนอบรม/ประชุม/ดูงาน)
 *
 * @var yii\web\View $this
 * @var app\modules\hr\models\Development $model
 * @var app\modules\hr\models\DevelopmentSummary|null $summary
 * @var bool $canEdit เจ้าของใบ/คณะเดินทาง แก้ได้ คนอื่นดูอย่างเดียว
 * @var app\modules\hr\models\Employees|null $me
 */

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\DevelopmentSummary;
use app\modules\filemanager\components\FileManagerHelper;

$state = $model->summaryState();
$acknowledgers = $summary ? $summary->getAcknowledgers() : [];
$myRow = null;
foreach ($acknowledgers as $row) {
    if ($me && (string) $row->emp_id === (string) $me->id) {
        $myRow = $row;
    }
}
$canAcknowledge = $myRow && $myRow->status !== 'Pass'
    && $summary && $summary->status !== DevelopmentSummary::STATUS_DRAFT;

// เติมรายชื่อผู้รับทราบเดิมกลับเข้า Select2
$acknowledgerInit = [];
foreach ($acknowledgers as $row) {
    $emp = $row->employee ?? null;
    $acknowledgerInit[(string) $row->emp_id] = $emp ? $emp->fullname() : (string) $row->emp_id;
}

$resultsJs = <<<JS
function (data, params) {
    params.page = params.page || 1;
    return {
        results: data.results,
        pagination: { more: (params.page * 30) < data.total_count }
    };
}
JS;
?>

<div class="dev-summary">
    <!-- หัวเรื่อง: อ้างว่าสรุปของใบไหน -->
    <div class="dev-summary__head mb-3">
        <p class="mb-1 fw-semibold text-body"><?= Html::encode($model->topic) ?></p>
        <div class="d-flex flex-wrap gap-3 small text-body-secondary">
            <span><i class="bi bi-calendar3 me-1"></i><?= $model->showDateRange() ?></span>
            <?php if (!empty($model->data_json['location'])): ?>
                <span><i class="bi bi-geo-alt me-1"></i><?= Html::encode($model->data_json['location']) ?></span>
            <?php endif; ?>
            <span class="badge rounded-pill text-bg-<?= $state['color'] ?>">
                <i class="bi <?= $state['icon'] ?> me-1"></i><?= $state['label'] ?>
            </span>
        </div>
    </div>

    <?php if ($summary && $summary->isEditedAfterSubmit()): ?>
        <div class="alert alert-warning py-2 px-3 small d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-pencil-square"></i>
            มีการแก้ไขเนื้อหาหลังส่งให้รับทราบ (แก้ล่าสุด <?= Yii::$app->formatter->asDatetime($summary->updated_at, 'php:d/m/Y H:i') ?>)
        </div>
    <?php endif; ?>

    <?php if (!$summary): ?>
        <div class="alert alert-secondary py-2 px-3 small mb-0">
            <i class="bi bi-info-circle me-1"></i> ยังไม่มีการบันทึกสรุปผลสำหรับรายการนี้
        </div>
    <?php else: ?>
        <?php $form = ActiveForm::begin(['id' => 'form-development-summary']); ?>

        <?= $form->field($summary, 'content')->textArea([
            'rows' => 5,
            'readonly' => !$canEdit,
            'placeholder' => 'สรุปสาระสำคัญที่ได้รับจากการประชุม/อบรม/ดูงานครั้งนี้...',
        ])->label('สรุปเนื้อหา/สาระสำคัญที่ได้รับ <span class="text-danger">*</span>') ?>

        <?= $form->field($summary, 'benefit')->textArea([
            'rows' => 3,
            'readonly' => !$canEdit,
            'placeholder' => 'สิ่งที่จะนำมาปรับใช้ในงาน หรือขยายผลต่อ...',
        ])->label('การนำไปใช้ประโยชน์ต่อหน่วยงาน') ?>

        <?= $form->field($summary, 'suggestion')->textArea([
            'rows' => 3,
            'readonly' => !$canEdit,
            'placeholder' => 'ข้อเสนอแนะเพิ่มเติมถึงผู้บริหาร...',
        ])->label('ข้อเสนอแนะต่อหน่วยงาน') ?>

        <div class="mb-3">
            <label class="form-label fw-medium">ไฟล์แนบ (เอกสาร/ภาพ/เกียรติบัตร)</label>
            <?= FileManagerHelper::FileUpload($summary->ref, 'development_summary', !$canEdit) ?>
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium" for="summary-acknowledgers">ส่งให้รับทราบ</label>
            <?= Select2::widget([
                'name' => 'acknowledgers',
                'value' => array_keys($acknowledgerInit),
                'data' => $acknowledgerInit,
                'options' => [
                    'placeholder' => 'พิมพ์ชื่อเพื่อค้นหาบุคลากร...',
                    'multiple' => true,
                    'id' => 'summary-acknowledgers',
                    'disabled' => !$canEdit,
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 1,
                    'dropdownParent' => '#main-modal',
                    'ajax' => [
                        'url' => Url::to(['/depdrop/employee-by-id']),
                        'dataType' => 'json',
                        'delay' => 250,
                        'data' => new JsExpression("function(params) { return { q: params.term || '', page: params.page }; }"),
                        'processResults' => new JsExpression($resultsJs),
                        'cache' => true,
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateSelection' => new JsExpression('function (item) { return item.fullname || item.text; }'),
                ],
            ]) ?>
            <div class="form-text">เลือก ผอ. หรือผู้ที่ต้องการให้อ่านรับทราบ เลือกได้มากกว่า 1 คน</div>
        </div>

        <?php if (!empty($acknowledgers)): ?>
            <div class="dev-summary__ack mb-3">
                <div class="small fw-medium text-body-secondary mb-2">สถานะการรับทราบ</div>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                    <?php foreach ($acknowledgers as $row): ?>
                        <?php $emp = $row->employee ?? null; ?>
                        <li class="d-flex align-items-center justify-content-between gap-2">
                            <span class="text-body">
                                <i class="bi bi-person-circle me-1 text-body-secondary"></i>
                                <?= Html::encode($emp ? $emp->fullname() : $row->emp_id) ?>
                                <?php if (!empty($row->comment)): ?>
                                    <span class="text-body-secondary small">— <?= Html::encode($row->comment) ?></span>
                                <?php endif; ?>
                            </span>
                            <?php if ($row->status === 'Pass'): ?>
                                <span class="badge rounded-pill text-bg-success">
                                    <i class="bi bi-check2 me-1"></i>รับทราบแล้ว
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-pill text-bg-secondary">รอรับทราบ</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?= Html::hiddenInput('do', 'save', ['id' => 'summary-do']) ?>

        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
            <?php if ($canEdit): ?>
                <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกร่าง', [
                    'class' => 'btn btn-outline-primary rounded-pill',
                    'data-do' => 'save',
                ]) ?>
                <?= Html::submitButton('<i class="bi bi-send me-1"></i> ส่งให้รับทราบ', [
                    'class' => 'btn btn-primary rounded-pill shadow-sm',
                    'data-do' => 'submit',
                ]) ?>
            <?php endif; ?>
            <?php if ($canAcknowledge): ?>
                <button type="button" class="btn btn-success rounded-pill shadow-sm" id="btn-summary-acknowledge">
                    <i class="bi bi-check2-circle me-1"></i> รับทราบ
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-1"></i> ปิด
            </button>
        </div>

        <?php ActiveForm::end(); ?>
    <?php endif; ?>
</div>

<?php
$acknowledgeUrl = Url::to(['/hr/development/summary-acknowledge', 'id' => $model->id]);

$js = <<<JS
// ปิด modal แล้วยืนยันผลด้วย Swal ตัวเดียว
// (ห้ามใช้ closeModal() ที่นี่ เพราะมันเด้ง Swal "บันทึกสำเร็จ" ของตัวเองมาทับ
//  จนผู้ใช้เห็นแค่จอกระพริบแล้ว reload — ดูเหมือนบันทึกไม่ผ่านทั้งที่บันทึกแล้ว)
function summaryDone(message) {
    erpHideModal('#main-modal');
    Swal.fire({
        title: 'สำเร็จ!',
        text: message,
        icon: 'success',
        confirmButtonText: 'ตกลง'
    }).then(function () { window.location.reload(); });
}

// จำไว้ว่ากดปุ่มไหน เพื่อแยก "บันทึกร่าง" กับ "ส่งให้รับทราบ"
\$('#form-development-summary').on('click', '[data-do]', function () {
    \$('#summary-do').val(\$(this).data('do'));
});

\$('#form-development-summary').on('beforeSubmit', function () {
    var form = \$(this);
    var isSubmit = \$('#summary-do').val() === 'submit';

    Swal.fire({
        title: 'ยืนยัน?',
        text: isSubmit ? 'ส่งสรุปผลให้ผู้รับทราบ' : 'บันทึกสรุปผลเป็นฉบับร่าง',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'ยกเลิก',
        confirmButtonText: 'ใช่, ยืนยัน!'
    }).then(function (result) {
        if (!result.isConfirmed) { return; }
        \$.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    summaryDone(response.message);
                } else if (typeof warning === 'function') {
                    warning(response.message);
                }
            },
            error: function (xhr) {
                if (typeof warning === 'function') {
                    warning('บันทึกไม่สำเร็จ (' + xhr.status + ') กรุณาลองใหม่อีกครั้ง');
                }
            }
        });
    });
    return false;
});

\$('#btn-summary-acknowledge').on('click', function () {
    Swal.fire({
        title: 'รับทราบสรุปผล',
        input: 'textarea',
        inputLabel: 'ความเห็นเพิ่มเติม (ถ้ามี)',
        inputPlaceholder: 'ไม่บังคับ...',
        showCancelButton: true,
        cancelButtonText: 'ยกเลิก',
        confirmButtonText: 'รับทราบ'
    }).then(function (result) {
        if (!result.isConfirmed) { return; }
        \$.ajax({
            url: '$acknowledgeUrl',
            type: 'post',
            data: { comment: result.value || '', _csrf: yii.getCsrfToken() },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    summaryDone(response.message);
                } else if (typeof warning === 'function') {
                    warning(response.message);
                }
            },
            error: function (xhr) {
                if (typeof warning === 'function') {
                    warning('บันทึกการรับทราบไม่สำเร็จ (' + xhr.status + ') กรุณาลองใหม่อีกครั้ง');
                }
            }
        });
    });
});
JS;
$this->registerJs($js, View::POS_END);

$css = <<<CSS
.dev-summary__head {
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--bs-border-color-translucent);
}

.dev-summary__ack {
    background-color: var(--bs-tertiary-bg);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
}
CSS;
$this->registerCss($css);
?>
