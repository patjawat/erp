<?php

use app\modules\medsop\models\DocumentAssignment;
use yii\helpers\Html;

?>
<section id="document-acknowledgement" class="medsop-acknowledgement mt-4 mb-4" aria-labelledby="acknowledgement-title">
    <div class="medsop-acknowledgement__main">
        <div class="medsop-acknowledgement__head">
            <span class="medsop-acknowledgement__icon"><i class="bi bi-clipboard-check" aria-hidden="true"></i></span>
            <div><h2 id="acknowledgement-title" class="h5 fw-semibold mb-1">ลงชื่อรับทราบขั้นตอนปฏิบัติ</h2><p class="small text-body-secondary mb-0">ยืนยันหลังจากอ่านและเข้าใจขั้นตอนทั้งหมดแล้ว</p></div>
        </div>
        <dl class="medsop-acknowledgement__identity">
            <div><dt>ชื่อผู้รับเอกสาร</dt><dd><?= Html::encode($assignmentEmployee->fullname()) ?></dd></div>
            <div><dt>ตำแหน่งงาน</dt><dd><?= Html::encode($assignmentEmployee->positionName() ?: '-') ?></dd></div>
            <div><dt>สังกัดหน่วยงาน</dt><dd><?= Html::encode($assignmentEmployee->empDepartment ? $assignmentEmployee->empDepartment->name : '-') ?></dd></div>
        </dl>
        <?php if ($assignment->status === DocumentAssignment::STATUS_ACKNOWLEDGED): ?>
            <div class="medsop-acknowledgement__done"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span><strong>รับทราบแล้ว</strong><small><?= Yii::$app->formatter->asDatetime($assignment->acknowledged_at, 'medium') ?></small></span></div>
        <?php else: ?>
            <?= Html::beginForm(['acknowledge', 'id' => $model->id], 'post') ?>
            <?= Html::submitButton('<i class="bi bi-person-check me-2" aria-hidden="true"></i>ลงชื่อและบันทึกรับทราบ', [
                'class' => 'btn btn-primary btn-block medsop-acknowledgement__submit',
                'data-medsop-confirm' => true,
                'data-confirm-title' => 'ยืนยันการรับทราบ',
                'data-confirm-text' => 'คุณได้อ่านและเข้าใจขั้นตอนทั้งหมดในเอกสารฉบับนี้แล้ว',
                'data-confirm-label' => 'ยืนยันรับทราบ',
            ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
    <aside class="medsop-acknowledgement__history" aria-label="ประวัติการอ่านและรับทราบ">
        <div class="d-flex align-items-center justify-content-between gap-2"><h2 class="h6 fw-semibold mb-0">ประวัติของคุณ</h2><span class="count-pill"><?= number_format((int) $assignment->open_count) ?> ครั้ง</span></div>
        <div class="medsop-acknowledgement__event"><span class="medsop-acknowledgement__event-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><div><strong>เปิดอ่านล่าสุด</strong><small><?= Yii::$app->formatter->asDatetime($assignment->last_opened_at, 'medium') ?></small></div></div>
        <?php if ($assignment->acknowledged_at): ?><div class="medsop-acknowledgement__event is-success"><span class="medsop-acknowledgement__event-icon"><i class="bi bi-check2" aria-hidden="true"></i></span><div><strong>ลงชื่อรับทราบ</strong><small><?= Yii::$app->formatter->asDatetime($assignment->acknowledged_at, 'medium') ?></small></div></div><?php endif; ?>
    </aside>
</section>
