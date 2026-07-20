<?php

use yii\helpers\Html;
use app\components\UserHelper;
use app\modules\jd\models\JdChangeRequest;

$jd = $model->getCurrentJd();
$canManage = Yii::$app->user->can('hr') || Yii::$app->user->can('admin');
$me = UserHelper::GetEmployee();
$isOwner = $me && (int) $me->id === (int) $model->id;
?>
<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h5 class="mb-1 fw-semibold">คำอธิบายงานฉบับปัจจุบัน</h5>
        <?php if ($jd): ?>
            <div class="text-muted small">
                Revision <?= (int) $jd->revision_no ?> · <?= Html::encode($jd->position_title ?: $model->positionName()) ?>
                <?php if ($jd->effective_from): ?> · มีผลตั้งแต่ <?= Yii::$app->formatter->asDate($jd->effective_from, 'php:d/m/Y') ?><?php endif; ?>
            </div>
            <?php if ($jd->approved_at): ?>
                <div class="text-muted small mt-1">ประกาศใช้โดย <?= Html::encode($jd->publisherEmployee?->fullname ?: 'HR ผู้ใช้งาน #' . $jd->approved_by) ?> · <?= Yii::$app->formatter->asDatetime($jd->approved_at, 'php:d/m/Y H:i') ?> น.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?= Html::a('<i class="bi bi-clock-history me-1"></i>ดูประวัติ', ['/hr/employees/view', 'id' => $model->id, 'name' => 'job_description_history'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
</div>

<?php if ($jd): ?>
    <?= $this->render('@app/modules/jd/views/employee-jd/_document', ['jd' => $jd]) ?>
    <?php if ($isOwner): $ack = $jd->acknowledgement; $review = $jd->openChangeRequest; ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">การรับทราบคำอธิบายงาน</h6>
                <?php if ($ack): ?>
                    <div class="d-flex align-items-start gap-2 text-success">
                        <i class="bi bi-check-circle-fill mt-1"></i>
                        <div><strong>รับทราบแล้ว</strong><div class="small text-muted"><?= Html::encode($ack->employee_name) ?> · <?= Yii::$app->formatter->asDatetime($ack->acknowledged_at, 'php:d/m/Y H:i') ?> น.</div></div>
                    </div>
                <?php elseif ($review): ?>
                    <?php $reviewLabels = JdChangeRequest::statusLabels(); ?>
                    <div class="alert alert-warning mb-0"><strong><?= Html::encode($reviewLabels[$review->status] ?? $review->status) ?></strong><div class="small mt-1">ส่งคำขอทบทวนเมื่อ <?= Yii::$app->formatter->asDatetime($review->submitted_at, 'php:d/m/Y H:i') ?> น. ระบบพักการลงนามไว้จนกว่า HR จะดำเนินการ</div></div>
                <?php else: ?>
                    <p class="text-muted mb-3">กรุณาตรวจสอบหน้าที่ ความรับผิดชอบ และ KPI เป้าหมายทุกหัวข้อก่อนดำเนินการ</p>
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <?= Html::a('<i class="bi bi-chat-left-text me-1"></i>ขอทบทวน JD', ['/jd/employee-jd/request-review', 'id' => $jd->id], ['class' => 'btn btn-outline-secondary']) ?>
                        <?= Html::beginForm(['/jd/employee-jd/acknowledge', 'id' => $jd->id], 'post', ['class' => 'd-inline']) ?>
                        <?= Html::submitButton('<i class="bi bi-pen me-1"></i>ลงนามรับทราบ', ['class' => 'btn btn-primary', 'data-confirm' => 'ยืนยันว่าคุณได้อ่านและรับทราบ JD Revision นี้แล้วหรือไม่?']) ?>
                        <?= Html::endForm() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <h6 class="fw-semibold mb-2">ยังไม่มีคำอธิบายงานฉบับปัจจุบัน</h6>
            <p class="text-muted mb-3">สร้างฉบับร่างจาก Template ตามตำแหน่ง แล้วตรวจสอบก่อนประกาศใช้</p>
            <?php if ($canManage): ?>
                <?= Html::a('<i class="bi bi-file-earmark-plus me-1"></i>สร้าง JD ฉบับร่าง', ['/jd/employee-jd/create-draft', 'emp_id' => $model->id], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
