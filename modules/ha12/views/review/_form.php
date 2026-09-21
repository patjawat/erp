<?php

use app\components\AppHelper;
use app\modules\ha12\models\Ha12Review;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Ha12Review $model */
/** @var app\modules\ha12\models\Ha12Activity $activity */
/** @var array $fields */
/** @var array<int,array{id:int,name:string}> $units */

$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
$dateId = 'ha12-review-date';
?>
<?php $form = ActiveForm::begin([
    'id' => 'ha12-review-form',
    'action' => $model->isNewRecord
        ? ['create', 'activity_id' => $activity->id, 'fy' => $model->fiscal_year]
        : ['update', 'id' => $model->id],
    'options' => ['data-list-url' => \yii\helpers\Url::to(['index', 'activity_id' => $activity->id, 'fy' => $model->fiscal_year])],
]); ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold">หน่วยงานเจ้าของ</label>
            <?= Html::activeDropDownList($model, 'owner_unit_id', $unitOptions, [
                'class' => 'form-select',
                'prompt' => '— เลือกหน่วยงาน —',
            ]) ?>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">วันที่ทบทวน <span class="text-danger">*</span></label>
            <?= DatepickerThai::widget([
                'name' => 'review_date_thai',
                'value' => $model->review_date ? AppHelper::convertToThai($model->review_date) : AppHelper::convertToThai(date('Y-m-d')),
                'options' => ['id' => $dateId, 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.', 'class' => 'form-control'],
            ]) ?>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">ปีงบ</label>
            <?= Html::activeTextInput($model, 'fiscal_year', ['class' => 'form-control', 'readonly' => true]) ?>
        </div>

        <?php foreach ($fields as $f): ?>
            <?php
            $key = $f['key'];
            $type = $f['type'] ?? 'text';
            $val = $model->fields[$key] ?? null;
            $colClass = $type === 'textarea' ? 'col-12' : 'col-md-6';
            ?>
            <div class="<?= $colClass ?>">
                <label class="form-label small fw-semibold" for="ha12f-<?= $key ?>">
                    <?= Html::encode($f['label']) ?>
                    <?php if (!empty($f['primary'])): ?><span class="text-danger">*</span><?php endif; ?>
                </label>
                <?php if ($type === 'textarea'): ?>
                    <?= Html::textarea("Ha12Review[fields][$key]", (string) $val, [
                        'id' => "ha12f-$key", 'class' => 'form-control', 'rows' => 2,
                    ]) ?>
                <?php else: ?>
                    <?= Html::input($type === 'number' ? 'number' : 'text', "Ha12Review[fields][$key]", (string) $val, [
                        'id' => "ha12f-$key", 'class' => 'form-control',
                        'min' => $type === 'number' ? '0' : null,
                    ]) ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="col-md-6">
            <label class="form-label small fw-semibold">ผู้ทบทวน</label>
            <?= Html::activeTextInput($model, 'reviewer_name', ['class' => 'form-control', 'maxlength' => true]) ?>
        </div>
    </div>

    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
        <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data' => ['bs-dismiss' => 'modal']]) ?>
    </div>

<?php ActiveForm::end();

$this->registerJs(<<<JS
if (typeof thaiDatepicker === 'function') { thaiDatepicker('#{$dateId}'); }
handleFormSubmit('#ha12-review-form', null, function (r) {
    var c = r && r.container;
    if (c && document.querySelector(c) && typeof erpReloadPjax === 'function' && erpReloadPjax(c)) return;
    var url = document.querySelector('#ha12-review-form').getAttribute('data-list-url');
    url ? window.location.href = url : location.reload();
});
JS); ?>
