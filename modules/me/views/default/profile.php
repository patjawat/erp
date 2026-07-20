<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
use app\components\ThaiDateHelper;
use app\components\AppHelper;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $model */

$this->title = 'โปรไฟล์ของฉัน';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;

$joinDateDisplay = '';
$workLifeDisplay = '';
$workLifeText = '';
try {
    $joinDateRaw = $model->joinDate();
    if (!empty($joinDateRaw)) {
        $joinDateDisplay = ThaiDateHelper::formatThaiDate(AppHelper::DateToDb($joinDateRaw) ?: $joinDateRaw, 'medium');
        $workLifeArr = $model->workLife();
        $workLifeDisplay = is_array($workLifeArr) ? ($workLifeArr['full'] ?? $workLifeArr['ym'] ?? '') : $workLifeArr;
        $workLifeText = $workLifeDisplay;
    }
} catch (\Throwable $e) {
}
$birthdayDisplay = !empty($model->birthday) ? ThaiDateHelper::formatThaiDate(AppHelper::DateToDb($model->birthday) ?: $model->birthday, 'medium') : '';
$labelWidth = 'col-12 col-md-4 col-lg-3 text-muted fw-medium';
$valueWidth = 'col-12 col-md-8 col-lg-9';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-person-circle"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'profile']) ?>
<?php $this->endBlock(); ?>

<div class="row g-4">
    <div class="col-12 col-lg-4 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body text-center py-4">
                <div class="d-inline-block rounded-4 overflow-hidden border border-2 border-light shadow-sm mb-3">
                    <?= $model->getAvatar(false) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= Html::encode($model->fullname()) ?></h5>
                <p class="text-primary fw-medium small mb-0"><?= Html::encode($model->positionName() ?: $model->position_name ?? '-') ?></p>
                <p class="text-muted small mb-0"><?= Html::encode($model->departmentName()) ?></p>
                <?php if ($workLifeText !== ''): ?>
                <p class="mt-2 mb-0">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill fw-medium px-2 py-1">อายุงาน <?= Html::encode($workLifeText) ?></span>
                </p>
                <?php endif; ?>
                <hr class="my-3">
                <a href="<?= Url::to(['/hr/employees/view', 'id' => $model->id]) ?>" class="btn btn-outline-primary rounded-pill w-100">
                    <i class="bi bi-box-arrow-up-right me-1"></i> ดูทะเบียนบุคลากร
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8 col-xl-9">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-primary bg-opacity-10 border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-person-vcard me-2"></i>ข้อมูลส่วนตัว</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 g-md-3">
                    <div class="<?= $labelWidth ?>">ชื่อ-นามสกุล</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($model->fullname()) ?></div>

                    <?php if (!empty($model->cid)): ?>
                    <div class="<?= $labelWidth ?>">เลขบัตรประชาชน</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode(preg_replace('/^(.+)(.{4})$/', '*******$2', $model->cid)) ?></div>
                    <?php endif; ?>

                    <?php if ($birthdayDisplay !== ''): ?>
                    <div class="<?= $labelWidth ?>">วันเกิด</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($birthdayDisplay) ?><?= isset($model->age) ? ' <span class="text-muted">(อายุ ' . (int)$model->age . ' ปี)</span>' : '' ?></div>
                    <?php endif; ?>

                    <?php if (!empty($model->gender)): ?>
                    <div class="<?= $labelWidth ?>">เพศ</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($model->gender) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($model->phone)): ?>
                    <div class="<?= $labelWidth ?>">เบอร์โทรศัพท์</div>
                    <div class="<?= $valueWidth ?>"><a href="tel:<?= Html::encode($model->phone) ?>" class="text-decoration-none"><?= Html::encode($model->phone) ?></a></div>
                    <?php endif; ?>

                    <?php if (!empty($model->email)): ?>
                    <div class="<?= $labelWidth ?>">อีเมล</div>
                    <div class="<?= $valueWidth ?>"><a href="mailto:<?= Html::encode($model->email) ?>" class="text-decoration-none"><?= Html::encode($model->email) ?></a></div>
                    <?php endif; ?>

                    <?php if (!empty($model->fulladdress) || !empty($model->address)): ?>
                    <div class="<?= $labelWidth ?>">ที่อยู่</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($model->fulladdress ?? $model->address) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-info bg-opacity-10 border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold text-info"><i class="bi bi-briefcase me-2"></i>ข้อมูลการทำงาน</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 g-md-3">
                    <div class="<?= $labelWidth ?>">รหัสบุคลากร</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($model->id) ?></div>

                    <div class="<?= $labelWidth ?>">ตำแหน่ง</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($model->positionName() ?: $model->position_name ?? '-') ?></div>

                    <?php if ($model->positionTypeName()): ?>
                    <div class="<?= $labelWidth ?>">ประเภทตำแหน่ง</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($model->positionTypeName()) ?></div>
                    <?php endif; ?>

                    <div class="<?= $labelWidth ?>">หน่วยงาน</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($model->departmentName()) ?></div>

                    <?php if ($joinDateDisplay !== ''): ?>
                    <div class="<?= $labelWidth ?>">วันที่เริ่มงาน</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($joinDateDisplay) ?></div>
                    <?php endif; ?>

                    <?php if ($workLifeText !== ''): ?>
                    <div class="<?= $labelWidth ?>">อายุงาน</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($workLifeText) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($model->status)): ?>
                    <div class="<?= $labelWidth ?>">สถานะ</div>
                    <div class="<?= $valueWidth ?>"><?= Html::encode($model->statusName()) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-danger bg-opacity-10 border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-heart-pulse me-2"></i>ข้อมูลประวัติการตรวจสุขภาพ</h6>
            </div>
            <div class="card-body">
                <?php
                $healthData = $model->healthData();
                $hasHealth = !empty($healthData['result']);
                ?>
                <?php if ($hasHealth): ?>
                <p class="text-muted small mb-2">ผลการตรวจสุขภาพล่าสุด</p>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <?php
                    $bmiLabel = is_array($healthData['result']) ? ($healthData['result']['label'] ?? null) : null;
                    $bmiColor = (is_array($healthData['result']) && !empty($healthData['result']['color'])) ? trim(explode(' ', $healthData['result']['color'])[0]) : 'primary';
                    if ($bmiLabel):
                    ?>
                    <span class="badge bg-<?= $bmiColor ?> bg-opacity-10 text-<?= $bmiColor ?> border border-<?= $bmiColor ?>-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($bmiLabel) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($healthData['id'])): ?>
                    <a href="<?= Url::to(['/me/health/view', 'id' => $healthData['id']]) ?>" class="btn btn-sm btn-outline-danger rounded-pill open-modal" data-size="modal-xl">
                        <i class="bi bi-eye me-1"></i> ดูผลตรวจ
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= Url::to(['/me/health/index']) ?>" class="btn btn-danger btn-sm rounded-pill">
                        <i class="bi bi-list-ul me-1"></i> ประวัติการตรวจสุขภาพทั้งหมด
                    </a>
                    <a href="<?= Url::to(['/me/health/create', 'name' => 'health', 'title' => 'แบบคัดกรองสุขภาพ']) ?>" class="btn btn-outline-danger btn-sm rounded-pill open-modal" data-size="modal-xl" data-pjax="0">
                        <i class="bi bi-plus-lg me-1"></i> บันทึก/ทำแบบคัดกรองใหม่
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-success bg-opacity-10 border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold text-success"><i class="bi bi-graph-up-arrow me-2"></i>KPI / ตัวชี้วัดผลงาน</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">ดูกราฟและตัวชี้วัดผลงาน (คะแนนประเมิน) ในทะเบียนบุคลากร</p>
                <a href="<?= Url::to(['/hr/employees/view', 'id' => $model->id]) ?>" class="btn btn-success btn-sm rounded-pill">
                    <i class="bi bi-bar-chart-line me-1"></i> ดู KPI และตัวชี้วัดผลงาน
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-secondary bg-opacity-10 border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-file-text me-2"></i>คำอธิบายงาน (Job Description)</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">ดูหรือแก้ไขคำอธิบายงานตามตำแหน่ง (JD)</p>
                <a href="<?= Url::to(['/hr/employees/view', 'id' => $model->id, 'name' => 'job_description_current']) ?>" class="btn btn-secondary btn-sm rounded-pill">
                    <i class="bi bi-journal-text me-1"></i> ดูคำอธิบายงาน (JD)
                </a>
            </div>
        </div>

        <?php if (!Yii::$app->user->isGuest): ?>
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-warning bg-opacity-10 border-0 py-3 rounded-top-4">
                <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-gear me-2"></i>การตั้งค่าบัญชีเข้าใช้งานระบบ</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 g-md-3 align-items-center">
                    <div class="<?= $labelWidth ?>">ชื่อเข้าใช้งานระบบ</div>
                    <div class="<?= $valueWidth ?> d-flex flex-wrap align-items-center gap-2">
                        <code class="bg-light px-2 py-1 rounded"><?= Html::encode(Yii::$app->user->identity->username ?? '-') ?></code>
                        <a href="<?= Url::to(['/me/account']) ?>" class="btn btn-warning btn-sm rounded-pill">
                            <i class="bi bi-pencil-square me-1"></i> เปลี่ยนรหัสผ่าน / แก้ไขชื่อเข้าใช้
                        </a>
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    ไปยังหน้าตั้งค่าบัญชีเพื่อเปลี่ยนชื่อเข้าใช้งาน (username) หรือรหัสผ่าน
                </p>
                <hr class="my-3">
                <a href="<?= Url::to(['/site/forgot-password']) ?>" class="text-muted small text-decoration-none">
                    <i class="bi bi-link-45deg me-1"></i>ลืมรหัสผ่าน? ขอรีเซ็ตรหัสผ่านทางอีเมล
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
