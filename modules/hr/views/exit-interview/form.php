<?php
use yii\helpers\Html;
$this->title = 'แบบสัมภาษณ์: ' . $model->employee_name_snapshot;
$this->beginBlock('page-title'); echo Html::encode('Exit Interview'); $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/hr/menu', ['active' => 'exit']); $this->endBlock();
?>
<div class="container-fluid px-0"><div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4"><div><?= Html::a('← กลับรายการสัมภาษณ์', ['registry'], ['class' => 'link-secondary text-decoration-none']) ?><h1 class="h3 mt-2 mb-1"><?= Html::encode($model->employee_name_snapshot) ?></h1><p class="text-body-secondary mb-0"><?= Html::encode(($model->position_name_snapshot ?: 'ไม่ระบุตำแหน่ง') . ' · ' . ($model->department_name_snapshot ?: 'ไม่ระบุหน่วยงาน')) ?></p></div><span class="badge bg-secondary-subtle text-secondary-emphasis align-self-start"><?= Html::encode($model->statusLabel) ?></span></div><?php if (!$canEdit): ?><div class="alert alert-info" role="status">คุณมีสิทธิ์ดูข้อมูลเท่านั้น จึงไม่สามารถแก้ไขหรือส่งแบบสัมภาษณ์นี้ได้</div><?php endif ?><?= $this->render('_questionnaire', compact('model', 'sections', 'answers', 'publicMode', 'canEdit')) ?></div>
