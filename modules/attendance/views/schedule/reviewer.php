<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;

/** @var Employees|null $fallback */
/** @var int $orphanCount */
$this->title = 'ผู้ยืนยันแทน';
$employees = Employees::find()->select(['id', 'fname', 'lname'])
    ->where(['status' => Employees::STATUS_WORKING])
    ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->asArray()->all();
$directorId = (int)(\app\components\SiteHelper::getInfo()['director_name'] ?? 0);
$options = [];
foreach ($employees as $e) {
    if ((int)$e['id'] === $directorId) continue; // ผอ. ไม่ต้องยืนยันการลงเวลา
    $options[$e['id']] = $e['fname'] . ' ' . $e['lname'];
}
?>
<?= $this->render('_header', ['tab' => $tab]) ?>
<section class="card border-0 shadow-sm">
    <div class="card-header bg-body py-3">
        <h2 class="h5 fw-semibold mb-1">ผู้ยืนยันการลงเวลาแทน</h2>
        <span class="text-body-secondary small">ใช้เมื่อไม่มีหัวหน้าในผังองค์กร หรือหัวหน้าถัดไปเป็นผู้อำนวยการ (เช่น หัวหน้ากลุ่มงาน)</span>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-7">
                <?= Html::beginForm(['reviewer'], 'post') ?>
                <div class="mb-3">
                    <?= Html::label('ผู้ยืนยันแทน', 'fallback-reviewer', ['class' => 'form-label fw-semibold']) ?>
                    <?= \kartik\select2\Select2::widget([
                        'name' => 'fallback_reviewer',
                        'value' => $fallback->id ?? null,
                        'data' => $options,
                        'options' => ['id' => 'fallback-reviewer', 'placeholder' => 'ไม่กำหนด — ให้ HR / เจ้าหน้าที่ลงเวลายืนยันจากคิวรวม'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                    <div class="form-text">ผู้ยืนยันแทนยืนยันรายการของตัวเองไม่ได้ รายการของผู้ยืนยันแทนจะไปที่ HR / เจ้าหน้าที่ลงเวลา</div>
                </div>
                <?php if ($orphanCount > 0): ?>
                <div class="form-check mb-3">
                    <?= Html::checkbox('reassign', true, ['class' => 'form-check-input', 'id' => 'reviewer-reassign', 'value' => 1]) ?>
                    <?= Html::label('โอนรายการที่รอยืนยันและยังไม่มีผู้ยืนยัน <strong>' . (int)$orphanCount . ' รายการ</strong> ให้ผู้ยืนยันแทนด้วย', 'reviewer-reassign', ['class' => 'form-check-label']) ?>
                </div>
                <?php endif; ?>
                <?= Html::submitButton('<i class="bi bi-check-lg me-1" aria-hidden="true"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
                <?= Html::endForm() ?>
            </div>
            <div class="col-lg-5">
                <div class="border rounded-3 p-3 bg-body-tertiary h-100">
                    <h3 class="h6 fw-semibold mb-2">ลำดับการหาผู้ยืนยัน</h3>
                    <ol class="small mb-2 ps-3">
                        <li>หัวหน้าหน่วยงาน</li>
                        <li>หัวหน้ากลุ่มงาน (ถ้าหน่วยไม่มีหัวหน้า หรือเจ้าตัวเป็นหัวหน้าเอง)</li>
                        <li><strong>ผู้ยืนยันแทน</strong> (ถ้ายังไม่พบ หรือหัวหน้าถัดไปเป็นผู้อำนวยการ)</li>
                        <li>HR / เจ้าหน้าที่ลงเวลา ยืนยันจากคิวรวม</li>
                    </ol>
                    <p class="small text-body-secondary mb-0">ผู้อำนวยการไม่ต้องยืนยันการลงเวลา · HR / admin / เจ้าหน้าที่ลงเวลา ยืนยันได้ทุกรายการเสมอ</p>
                </div>
            </div>
        </div>
    </div>
</section>
