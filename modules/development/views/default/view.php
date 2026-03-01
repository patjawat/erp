<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\development\models\Development $model */

$this->title = 'รายละเอียดที่บันทึก';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวมอบรม/ประชุม/ดูงาน', 'url' => ['/development/default/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รายการกิจกรรม', 'url' => ['/development/default/list', 'thai_year' => $model->thai_year]];
$this->params['breadcrumbs'][] = $this->title;

$dataJson = is_array($model->data_json) ? $model->data_json : (is_string($model->data_json) ? json_decode($model->data_json, true) : []);
$dataJson = is_array($dataJson) ? $dataJson : [];

$requesterName = $model->emp ? trim(($model->emp->fname ?? '') . ' ' . ($model->emp->lname ?? '')) : (string) $model->emp_id;
if ($requesterName === '') {
    $requesterName = 'ไม่ระบุ';
}
$members = $model->listMemberPrint();
$locationStr = trim(($dataJson['location'] ?? '') . ' ' . ($dataJson['province_name'] ?? ''));
if ($locationStr === '') {
    $locationStr = 'ไม่ระบุ';
}

$attachLabels = [];
if (!empty($dataJson['attach_invitation'])) {
    $attachLabels[] = 'หนังสือราชการ / บันทึกข้อความเชิญ';
}
if (!empty($dataJson['attach_class_change'])) {
    $attachLabels[] = 'แบบบันทึกการขอเปลี่ยนคาบสอน/สอนแทน';
}
if (!empty($dataJson['attach_vehicle'])) {
    $attachLabels[] = 'ขออนุญาตใช้รถยนต์โรงเรียน';
}
if (!empty($dataJson['attach_budget'])) {
    $attachLabels[] = 'ขอใช้งบประมาณ';
}
if (!empty($dataJson['attach_other_text'])) {
    $attachLabels[] = 'อื่นๆ: ' . $dataJson['attach_other_text'];
}

$approvalItems = [];
if (!empty($dataJson['claim_registration']) && isset($dataJson['registration_amount']) && $dataJson['registration_amount'] !== '') {
    $approvalItems[] = ['label' => 'ค่าลงทะเบียน: ' . number_format((float) $dataJson['registration_amount'], 0) . ' บ.', 'checked' => true];
}
if (!empty($dataJson['claim_per_diem'])) {
    $approvalItems[] = ['label' => 'ค่าเบี้ยเลี้ยง', 'checked' => true];
}
if (!empty($dataJson['claim_transport'])) {
    $approvalItems[] = ['label' => 'ค่าพาหนะ', 'checked' => true];
}
if (!empty($dataJson['claim_accommodation'])) {
    $approvalItems[] = ['label' => 'ค่าที่พัก', 'checked' => true];
}
if (!empty($dataJson['no_claim_org'])) {
    $approvalItems[] = ['label' => 'ไม่เบิกต้นสังกัด', 'checked' => true];
}
if (!empty($dataJson['use_official_vehicle'])) {
    $v = 'ขอใช้รถราชการ';
    if (!empty($dataJson['vehicle_plate']) || !empty($dataJson['driver_name'])) {
        $v .= ' (' . trim(($dataJson['vehicle_plate'] ?? '') . ' ' . ($dataJson['driver_name'] ?? '')) . ')';
    }
    $approvalItems[] = ['label' => $v, 'checked' => true];
}
if (!empty($dataJson['claim_travel'])) {
    $approvalItems[] = ['label' => 'ให้ข้าพเจ้าและคณะเดินทางไปราชการ ณ ' . $locationStr, 'checked' => true];
}
if (empty($approvalItems)) {
    $approvalItems[] = ['label' => 'ไม่มีการขออนุมัติเงิน/รถ', 'checked' => true];
}

$printUrl = Url::to(['/development/default/print-official', 'id' => $model->id]);
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
    <h4 class="fw-medium text-body mb-0 d-flex align-items-center gap-2">
        <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3"><i class="bi bi-clipboard-check"></i></span>
        <?= Html::encode($this->title) ?>
    </h4>
    <?= Html::a('<i class="bi bi-house-door me-1"></i> กลับหน้าหลัก', ['/development/default/dashboard'], ['class' => 'btn btn-outline-primary rounded-3']) ?>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('_menu', ['active' => null]) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3 development-view-layout">
    <div class="row g-4">
        <!-- ซ้าย: รายละเอียดที่บันทึก -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-body mb-4">รายละเอียดที่บันทึก</h5>

                    <div class="d-flex align-items-start gap-2 mb-4">
                        <span class="text-primary"><i class="bi bi-folder2 fs-4"></i></span>
                        <div>
                            <div class="fw-semibold text-body">บันทึกข้อความขอไปราชการ</div>
                            <div class="text-muted small">กลุ่มบริหารงานบุคคล</div>
                        </div>
                    </div>

                    <!-- วันที่เดินทาง -->
                    <div class="mb-4">
                        <div class="text-muted small mb-1">วันที่เดินทาง</div>
                        <div class="border border-secondary border-opacity-25 rounded-2 px-3 py-2">
                            <?= ThaiDateHelper::formatThaiDate($model->date_start, 'short') ?>
                            <?php if ($model->date_start !== $model->date_end): ?>
                                <br><span class="text-muted small">ถึง <?= ThaiDateHelper::formatThaiDate($model->date_end, 'short') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ทีมผู้ร่วม -->
                    <div class="mb-4">
                        <div class="text-muted small mb-2">ทีมผู้ร่วม</div>
                        <div class="mb-2">
                            <span class="text-muted small">ผู้ขอ:</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 ms-1">
                                <i class="bi bi-person me-1"></i><?= Html::encode($requesterName) ?>
                            </span>
                        </div>
                        <?php if (!empty($members)): ?>
                        <div>
                            <span class="text-muted small">ผู้ร่วมเดินทาง:</span>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <?php foreach ($members as $m): ?>
                                    <?php
                                    $name = $m->emp ? trim(($m->emp->fname ?? '') . ' ' . ($m->emp->lname ?? '')) : ($m->data_json['label'] ?? $m->emp_id ?? '');
                                    if ($name === '') {
                                        $name = 'ไม่ระบุ';
                                    }
                                    ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">
                                        <i class="bi bi-person me-1"></i><?= Html::encode($name) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- ผู้อนุมัติ -->
                    <?php
                    $approverRecord = $model->getApproverRecord();
                    if ($approverRecord): ?>
                    <div class="mb-4">
                        <div class="text-muted small mb-2">ผู้อนุมัติ</div>
                        <div class="border border-secondary border-opacity-25 rounded-2 px-3 py-2">
                            <?php
                            $approverName = $approverRecord->employee ? (method_exists($approverRecord->employee, 'fullname') ? $approverRecord->employee->fullname() : trim(($approverRecord->employee->fname ?? '') . ' ' . ($approverRecord->employee->lname ?? ''))) : 'ไม่ระบุ';
                            ?>
                            <div><i class="bi bi-person-check me-1"></i><?= Html::encode($approverName) ?></div>
                            <?php if ($model->approveDate()): ?>
                            <div class="text-muted small mt-1">วันที่อนุมัติ: <?= ThaiDateHelper::formatThaiDate($model->approveDate(), 'short') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- สถานที่/เหตุผล -->
                    <div class="mb-4">
                        <div class="text-muted small mb-1">สถานที่ / เหตุผล</div>
                        <div class="mb-2"><?= Html::encode($locationStr) ?></div>
                        <div class="border border-secondary border-opacity-25 rounded-2 px-3 py-2 text-body"><?= Html::encode($model->topic ?: 'ไม่ระบุ') ?></div>
                    </div>

                    <!-- สิ่งที่แนบมาด้วย -->
                    <?php if (!empty($attachLabels)): ?>
                    <div class="mb-4">
                        <div class="text-muted small mb-2">สิ่งที่แนบมาด้วย</div>
                        <ul class="list-unstyled mb-0 small">
                            <?php foreach ($attachLabels as $l): ?>
                                <li class="mb-1"><i class="bi bi-check2 text-success me-2"></i><?= Html::encode($l) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- การขออนุมัติ -->
                    <div class="mb-4">
                        <div class="text-muted small mb-2">การขออนุมัติ</div>
                        <ul class="list-unstyled mb-0 small">
                            <?php foreach ($approvalItems as $a): ?>
                                <li class="mb-1"><i class="bi bi-check2-circle-fill text-success me-2"></i><?= Html::encode($a['label']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- ข้อมูลเพิ่มเติมจาก view เก่า (ย่อ) -->
                    <div class="border-top pt-3 mt-3 small text-muted">
                        <div class="mb-1">เลขที่เอกสาร: <?= Html::encode($model->document_id ?? '-') ?></div>
                        <div class="mb-1">ประเภท: <?= Html::encode($model->developmentType ? $model->developmentType->title : '-') ?></div>
                        <div class="mb-1">สถานะ: <?php echo $model->getStatusHtml(); ?></div>
                        <?php if ($model->assignedTo): ?>
                        <div>ผู้รับมอบหมาย: <?= Html::encode(trim(($model->assignedTo->fname ?? '') . ' ' . ($model->assignedTo->lname ?? ''))) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- ปุ่มดำเนินการ (สไตล์ตัวอย่าง: ปุ่มหลักส้ม/เขียว, ปุ่มรองขาวเทา) -->
                    <div class="d-flex flex-column gap-2 mt-4">
                        <?= Html::a('<i class="bi bi-printer me-2"></i> ฝากปริ้นท์ (ห้องบุคคล)', $printUrl, [
                            'class' => 'btn btn-warning rounded-3',
                            'target' => '_blank',
                            'title' => 'เปิดหน้าพิมพ์',
                        ]) ?>
                        <?= Html::a('<i class="bi bi-download me-2"></i> ดาวน์โหลด PDF', $printUrl, [
                            'class' => 'btn btn-outline-secondary rounded-3',
                            'target' => '_blank',
                            'title' => 'เปิดหน้าพิมพ์เพื่อบันทึกเป็น PDF',
                        ]) ?>
                        <?= Html::a('<i class="bi bi-pencil-square me-2"></i> แก้ไข', ['/development/default/update', 'id' => $model->id, 'title' => 'แบบฟอร์มบันทึกข้อมูลการพัฒนาบุคลากร'], [
                            'class' => 'btn btn-outline-primary rounded-3 open-modal-x',
                            'data' => ['size' => 'modal-xl'],
                            'title' => 'แก้ไขรายการ',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ขวา: ตัวอย่างเอกสาร -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">ตัวอย่างเอกสาร</span>
                    <?= Html::a('<i class="bi bi-fullscreen"></i>', $printUrl, [
                        'class' => 'btn btn-sm btn-link text-body',
                        'target' => '_blank',
                        'title' => 'เปิดเต็มจอ',
                    ]) ?>
                </div>
                <div class="card-body p-0 position-relative" style="min-height: 640px;">
                    <iframe
                        src="<?= Html::encode($printUrl) ?>"
                        title="ตัวอย่างใบขอไปราชการ"
                        class="w-100 border-0 rounded-bottom-3"
                        style="height: 75vh; min-height: 560px; background: #f8f9fa;"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
