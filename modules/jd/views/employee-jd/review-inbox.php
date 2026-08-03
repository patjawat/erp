<?php

use app\modules\hr\models\Employees;
use app\modules\jd\models\JdChangeRequest;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var JdChangeRequest[] $requests */

$this->title = 'คำขอทบทวน JD';
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['/hr/employees/index']];
$this->params['breadcrumbs'][] = $this->title;
$labels = JdChangeRequest::statusLabels();
?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info', 'warning' => 'warning'] as $flash => $tone): ?>
    <?php if (Yii::$app->session->hasFlash($flash)): ?>
        <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($flash)) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="mb-1 fw-semibold">คำขอทบทวน JD</h5>
        <div class="text-body-secondary small">คำขอจากเจ้าของ JD ที่รอ HR พิจารณา รับ (สร้างฉบับร่างใหม่) หรือไม่รับ</div>
    </div>
    <span class="badge bg-warning-subtle text-warning-emphasis">รอดำเนินการ <?= count($requests) ?></span>
</div>

<?php if (!$requests): ?>
    <div class="card bg-body border"><div class="card-body text-center py-5">
        <i class="bi bi-inbox fs-1 text-body-secondary d-block mb-2"></i>
        <h6 class="fw-semibold">ไม่มีคำขอทบทวนที่รอดำเนินการ</h6>
    </div></div>
<?php else: ?>
    <?php foreach ($requests as $r): ?>
        <?php $emp = Employees::findOne($r->emp_id); ?>
        <div class="card bg-body border mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                    <div class="min-w-0">
                        <div class="fw-semibold"><?= Html::encode($emp ? $emp->fullname() : ('รหัส ' . (int) $r->emp_id)) ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-normal ms-1"><?= Html::encode($labels[$r->status] ?? $r->status) ?></span>
                        </div>
                        <div class="text-body-secondary small">
                            <?= Html::encode($emp ? strip_tags((string) $emp->positionName()) : '') ?>
                            · ส่งเมื่อ <?= Yii::$app->formatter->asDatetime($r->submitted_at, 'php:d/m/Y H:i') ?> น.
                            <?php if ($r->section_title): ?> · หัวข้อ: <?= Html::encode($r->section_title) ?><?php endif; ?>
                        </div>
                    </div>
                    <?= Html::a('<i class="bi bi-eye me-1"></i>ดู JD', ['view', 'emp_id' => $r->emp_id, 'id' => $r->jd_employee_id], ['class' => 'btn btn-sm btn-outline-primary text-nowrap']) ?>
                </div>

                <?php if (trim((string) $r->reason) !== ''): ?>
                    <div class="mb-1"><span class="text-body-secondary small">เหตุผล:</span> <?= nl2br(Html::encode($r->reason)) ?></div>
                <?php endif; ?>
                <?php if (trim((string) $r->proposed_change) !== ''): ?>
                    <div class="mb-2"><span class="text-body-secondary small">ข้อเสนอแก้ไข:</span> <?= nl2br(Html::encode($r->proposed_change)) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= Url::to(['resolve-review', 'id' => $r->id]) ?>" class="border-top pt-3 mt-2">
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md">
                            <label class="form-label small text-body-secondary mb-1">หมายเหตุ (ถ้ามี)</label>
                            <input type="text" name="resolution_note" class="form-control form-control-sm" placeholder="ระบุเหตุผลการรับ/ไม่รับคำขอ">
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <button type="submit" name="decision" value="accept" class="btn btn-sm btn-success" data-confirm="รับคำขอ และสร้าง JD ฉบับร่างใหม่ (คัดลอกจากฉบับปัจจุบัน) ให้แก้ไข?"><i class="bi bi-check2-circle me-1"></i>รับคำขอ (สร้างฉบับร่าง)</button>
                            <button type="submit" name="decision" value="reject" class="btn btn-sm btn-outline-danger" data-confirm="ไม่รับคำขอทบทวนนี้? เจ้าของจะกลับมาลงนามรับทราบ JD ฉบับเดิม"><i class="bi bi-x-circle me-1"></i>ไม่รับ</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
