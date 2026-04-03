<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\models\HelpdeskSlaSetting;

/** @var yii\web\View $this */

$this->title = 'ตั้งค่า SLA';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานซ่อม', 'url' => ['/helpdesk/service/index']];
$this->params['breadcrumbs'][] = $this->title;

$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$slaRecord = HelpdeskSlaSetting::getRecord();
$slaConfig = $slaRecord->getConfig();
$hoursByUrgency = is_array($slaConfig) && isset($slaConfig['urgency_hours']) && is_array($slaConfig['urgency_hours'])
    ? $slaConfig['urgency_hours']
    : [];
$urgencies = Helpdesk::listUrgency();

$saveSlaUrl = Url::to(['/helpdesk/setting/save-sla-settings']);
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="fa-solid fa-gauge-high"></i>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/helpdesk2/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show mb-3">
    <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
    <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('warning')): ?>
<div class="alert alert-warning alert-dismissible fade show mb-3">
    <?= Html::encode(Yii::$app->session->getFlash('warning')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="container-fluid px-2 px-md-3 px-lg-4">
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 rounded-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-gauge-high"></i>
                <h6 class="mb-0 small fw-semibold">ตั้งค่า SLA</h6>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 align-items-start">
                <div class="col-12 col-lg-8">
                    <div class="small text-muted mb-3">
                        กำหนดเวลาการตอบสนองตาม “ความเร่งด่วน” (ใช้คำนวณสถานะ SLA: ภายใน/ใกล้ครบกำหนด/เกิน SLA)
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-5">ความเร่งด่วน</th>
                                    <th class="col-3">ชั่วโมง SLA</th>
                                    <th class="col-4">หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle table-group-divider">
                                <?php foreach ($urgencies as $urgencyCode => $urgencyLabel): ?>
                                    <?php
                                    $hours = $hoursByUrgency[$urgencyCode] ?? $hoursByUrgency[(string) $urgencyCode] ?? '';
                                    $hoursVal = is_numeric($hours) ? (int) $hours : '';
                                    ?>
                                    <tr>
                                        <td><?= Html::encode($urgencyLabel) ?></td>
                                        <td style="min-width: 180px;">
                                            <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                class="form-control"
                                                name="urgency_hours[<?= Html::encode($urgencyCode) ?>]"
                                                value="<?= Html::encode($hoursVal) ?>">
                                        </td>
                                        <td class="text-muted small">
                                            ค่าที่ใช้คำนวณ deadline
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <?= Html::beginForm($saveSlaUrl, 'post') ?>
                    <?= Html::hiddenInput($csrfParam, $csrfToken) ?>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i>บันทึกการตั้งค่า SLA
                        </button>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= Url::to(['/helpdesk/setting/index']) ?>">
                            รีเฟรชค่าเริ่มต้น
                        </a>
                    </div>
                    <?= Html::endForm() ?>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        “ใกล้ครบกำหนด” = 1 ชั่วโมงสุดท้าย (ค่าคงที่ในระบบ)
                    </div>

                    <div class="alert alert-secondary mt-2 mb-0">
                        <i class="fa-solid fa-file-pdf me-1"></i>
                        การตั้งค่า PDF Template ให้ไปที่
                        <a href="<?= Url::to(['/pdf-template/template']) ?>">`/pdf-template/template`</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

