<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $config */
/** @var bool $hasTemplate */
/** @var string|null $templateUrl */

$this->title = 'แบบฟอร์มใบลา';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-file-earmark-pdf"></i> <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/views/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 rounded-top-3">
        <h6 class="mb-0 small fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-upload"></i> เทมเพลต PDF
            <?php if ($hasTemplate): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">มีไฟล์แล้ว</span>
            <?php else: ?>
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">ยังไม่มีเทมเพลต</span>
            <?php endif; ?>
        </h6>
    </div>
    <div class="card-body p-4">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
        </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <form action="<?= Url::to(['/leave/setting/upload-template']) ?>" method="post" enctype="multipart/form-data">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                    <label class="form-label small text-muted">เลือกไฟล์ PDF (เทมเพลตใบลา)</label>
                    <div class="input-group mb-2">
                        <input type="file" name="template_pdf" accept=".pdf" class="form-control rounded-3" required>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-3 px-3">
                        <i class="bi bi-cloud-upload me-1"></i> อัปโหลดเทมเพลต
                    </button>
                </form>
            </div>
            <?php if ($hasTemplate): ?>
            <div class="col-12 col-lg-7">
                <p class="small text-muted mb-2">ตัวอย่างเทมเพลต (หน่วยตำแหน่งเป็น mm)</p>
                <div class="border rounded-3 overflow-hidden bg-secondary bg-opacity-10">
                    <iframe src="<?= Html::encode($templateUrl) ?>#toolbar=0" class="w-100" style="height: 420px;" title="เทมเพลต PDF"></iframe>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?= Html::a(
                        '<i class="bi bi-geo-alt me-1"></i> กำหนดตำแหน่งข้อมูลบน PDF',
                        ['/leave/setting/positions'],
                        ['class' => 'btn btn-outline-primary rounded-3']
                    ) ?>
                    <?= Html::a(
                        '<i class="bi bi-printer me-1"></i> ไปหน้ารายการขอลา / พิมพ์ใบลา',
                        ['/leave/default/index'],
                        ['class' => 'btn btn-outline-secondary rounded-3']
                    ) ?>
                </div>
                <p class="small text-muted mt-2 mb-0">ตำแหน่งที่กำหนดจะถูกใช้กับหน้ารูปแบบพิมพ์ใบลา — ไปที่ <strong>ขอลา / รายการของฉัน</strong> แล้วกดปุ่ม «พิมพ์ใบลา» ที่รายการที่ต้องการ</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
