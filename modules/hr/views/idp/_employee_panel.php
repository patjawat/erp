<?php
use yii\helpers\Html;
use yii\helpers\Url;

echo $this->render('_styles');
$isOwner = (int)(\app\components\UserHelper::GetEmployee()?->id ?? 0) === (int)$employee->id;
$canManage = Yii::$app->user->can('hr') || Yii::$app->user->can('admin');
$canEdit = $plan && $isOwner && $plan->canEdit();
$canProgress = $plan && $isOwner && in_array($plan->status, ['approved','in_progress','assessment'], true);
$stage = 1;
if ($plan) {
    if (in_array($plan->status, ['submitted','revision'], true)) $stage = 2;
    elseif (in_array($plan->status, ['approved','in_progress'], true)) $stage = 3;
    elseif (in_array($plan->status, ['assessment','completed'], true)) $stage = 4;
}
?>
<div class="idp-shell" id="idp-employee-panel">
    <div class="idp-head">
        <div><h1><?= $isSelfProfile ? 'IDP ของฉัน' : 'IDP: '.Html::encode($employee->fullname) ?></h1><p>เป้าหมาย กิจกรรมพัฒนา และความก้าวหน้าในรอบปัจจุบัน</p></div>
        <?php if($plan && $canEdit): ?><?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มเป้าหมาย', ['/hr/idp/goal','plan_id'=>$plan->id], ['class'=>'btn btn-primary open-modal','data-size'=>'modal-lg']) ?><?php endif ?>
    </div>
    <div class="idp-surface">
        <?php if(!$cycle): ?>
            <div class="idp-empty"><h2>ยังไม่มีรอบ IDP ที่เปิดใช้งาน</h2><p>HR จะกำหนดช่วงเวลาจัดทำและแจ้งให้ทราบเมื่อเปิดรอบใหม่</p></div>
        <?php elseif(!$plan): ?>
            <div class="idp-empty"><h2><?= Html::encode($cycle->title) ?></h2><p>เริ่มจัดทำแผนจากเป้าหมายที่สัมพันธ์กับ JD ผลการประเมิน หรือหน้าที่ที่ต้องเตรียมพร้อม</p>
                <?php if($isOwner): ?><?= Html::beginForm(['/hr/idp/start'],'post') ?><?= Html::submitButton('เริ่มจัดทำ IDP',['class'=>'btn btn-primary']) ?><?= Html::endForm() ?><?php endif ?>
            </div>
        <?php else: ?>
            <div class="idp-cycle"><div><h2><?= Html::encode($cycle->title) ?></h2><p><?= Yii::$app->formatter->asDate($cycle->start_date,'php:d M Y') ?> ถึง <?= Yii::$app->formatter->asDate($cycle->end_date,'php:d M Y') ?></p></div><span class="idp-status idp-status--<?= Html::encode($plan->status) ?>"><?= Html::encode($plan->statusLabel) ?></span></div>
            <div class="idp-steps" aria-label="ขั้นตอน IDP">
                <?php foreach(['กำหนดเป้าหมาย','หัวหน้าพิจารณา','ดำเนินการพัฒนา','ประเมินผล'] as $i=>$label): $n=$i+1; ?>
                <div class="idp-step <?= $n<$stage?'is-done':'' ?> <?= $n===$stage?'is-current':'' ?>"><span class="idp-step__num"><?= $n<$stage?'✓':$n ?></span><?= $label ?></div>
                <?php endforeach ?>
            </div>
            <?php if($plan->supervisor_comment): ?><div class="alert alert-warning m-3 mb-0"><strong>ความคิดเห็นจากหัวหน้า</strong><div><?= nl2br(Html::encode($plan->supervisor_comment)) ?></div></div><?php endif ?>
            <?php if($plan->goals): ?>
            <div class="idp-goals">
                <?php foreach($plan->goals as $index=>$goal): ?>
                <section class="idp-goal">
                    <div class="idp-goal__head">
                        <div class="idp-goal__title"><span class="idp-goal__num"><?= $index+1 ?></span><span><?= Html::encode($goal->title) ?><small class="d-block fw-normal text-muted mt-1"><?= Html::encode(\app\modules\hr\models\IdpGoal::sourceOptions()[$goal->source_type] ?? $goal->source_type) ?><?php if($goal->due_date): ?> · กำหนด <?= Yii::$app->formatter->asDate($goal->due_date,'php:d M Y') ?><?php endif ?></small></span></div>
                        <?php if($canEdit): ?><?= Html::a('แก้ไข', ['/hr/idp/goal','plan_id'=>$plan->id,'id'=>$goal->id], ['class'=>'btn btn-sm btn-light open-modal','data-size'=>'modal-lg']) ?><?php endif ?>
                    </div>
                    <?php if($goal->expected_outcome): ?><div class="idp-goal__copy"><?= nl2br(Html::encode($goal->expected_outcome)) ?></div><?php endif ?>
                    <?php foreach($goal->activities as $activity): ?>
                    <div class="idp-activity">
                        <div><div class="fw-semibold"><?= Html::encode($activity->title) ?></div><div class="text-muted"><?= Html::encode(\app\modules\hr\models\IdpActivity::methodOptions()[$activity->method_type] ?? $activity->method_type) ?></div></div>
                        <div><div class="idp-progress"><span style="width:<?= min(100,(float)$activity->progress_percent) ?>%"></span></div><small><?= (int)$activity->progress_percent ?>%</small></div>
                        <?php if($canEdit || $canProgress): ?><?= Html::a($canProgress?'บันทึกผล':'แก้ไข', ['/hr/idp/activity','goal_id'=>$goal->id,'id'=>$activity->id], ['class'=>'btn btn-sm btn-outline-primary open-modal','data-size'=>'modal-lg']) ?><?php endif ?>
                    </div>
                    <?php endforeach ?>
                    <?php if($canEdit): ?><div class="ms-md-5 mt-2"><?= Html::a('<i class="bi bi-plus-circle me-1"></i> เพิ่มกิจกรรมพัฒนา', ['/hr/idp/activity','goal_id'=>$goal->id], ['class'=>'btn btn-sm btn-link open-modal','data-size'=>'modal-lg']) ?></div><?php endif ?>
                </section>
                <?php endforeach ?>
            </div>
            <?php else: ?><div class="idp-empty"><h2>เพิ่มเป้าหมายแรกของคุณ</h2><p>เลือกเป้าหมายที่สำคัญและสามารถวัดผลได้ รอบหนึ่งควรมีประมาณ 1 ถึง 3 เป้าหมาย</p><?php if($canEdit): ?><?= Html::a('เพิ่มเป้าหมายการพัฒนา', ['/hr/idp/goal','plan_id'=>$plan->id], ['class'=>'btn btn-primary open-modal','data-size'=>'modal-lg']) ?><?php endif ?></div><?php endif ?>

            <?php if($isOwner && $canEdit): ?><div class="idp-review d-flex justify-content-end"><?= Html::beginForm(['/hr/idp/submit','id'=>$plan->id],'post') ?><?= Html::submitButton('ส่งให้หัวหน้าพิจารณา',['class'=>'btn btn-primary']) ?><?= Html::endForm() ?></div><?php endif ?>

            <?php if(($canManage || (int)(\app\components\UserHelper::GetEmployee()?->id ?? 0)===(int)$plan->supervisor_emp_id) && $plan->status==='submitted'): ?>
            <div class="idp-review"><h3 class="h6">พิจารณาแผน IDP</h3>
                <?= Html::beginForm(['/hr/idp/approve','id'=>$plan->id],'post',['class'=>'row g-2']) ?>
                <div class="col-12"><label class="form-label" for="supervisor-comment">ความคิดเห็นถึงพนักงาน</label><textarea id="supervisor-comment" name="supervisor_comment" class="form-control" rows="3"></textarea></div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <?= Html::submitButton('ส่งกลับให้ปรับปรุง',['class'=>'btn btn-outline-warning','formaction'=>Url::to(['/hr/idp/return','id'=>$plan->id])]) ?>
                    <?= Html::submitButton('อนุมัติแผน IDP',['class'=>'btn btn-primary']) ?>
                </div><?= Html::endForm() ?>
            </div>
            <?php endif ?>
        <?php endif ?>
    </div>
</div>
