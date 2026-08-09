<?php
use yii\helpers\Html;
use app\components\RichText;
app\assets\RichTextAsset::register($this);
$this->title = $model->name;
$canManage = Yii::$app->user->can('pmStrategyManage');
$editable = $canManage && $model->isEditable();
$this->beginBlock('page-title'); ?>แผนยุทธศาสตร์<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'strategy']) ?><?php $this->endBlock();
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
<div><div class="d-flex align-items-center gap-2 mb-2"><span class="badge <?= $model->status==='published'?'bg-success-subtle text-success':'bg-secondary-subtle text-secondary' ?>"><?= Html::encode($model::statusList()[$model->status]) ?></span><span class="small text-muted"><?= Html::encode($model->code) ?> · รุ่น <?= (int)$model->version ?></span></div><h1 class="h3 mb-1"><?= Html::encode($model->name) ?></h1><p class="text-muted mb-0">พ.ศ. <?= (int)$model->start_year ?>–<?= (int)$model->end_year ?></p></div>
<?php if ($canManage): ?><div class="d-flex flex-wrap gap-2"><?php if ($editable): ?><?= Html::a('ดาวน์โหลด Template',['/pm/strategy-import/template'],['class'=>'btn btn-outline-secondary','data-pjax'=>0]) ?><?= Html::a('นำเข้าจาก Excel',['/pm/strategy-import/upload','planId'=>$model->id],['class'=>'btn btn-outline-secondary']) ?><?= Html::a('แก้ไขข้อมูลหลัก',['update','id'=>$model->id],['class'=>'btn btn-outline-primary']) ?><?= Html::a('ประกาศใช้',['publish','id'=>$model->id],['class'=>'btn btn-primary','data-method'=>'post','data-confirm'=>'เมื่อประกาศใช้แล้ว ข้อมูลชุดนี้จะถูกล็อก ยืนยันหรือไม่?']) ?><?php else: ?><?= Html::a('สร้างรุ่นใหม่',['clone','id'=>$model->id],['class'=>'btn btn-primary','data-method'=>'post','data-confirm'=>'สร้างฉบับร่างรุ่นใหม่จากชุดแผนนี้?']) ?><?php endif; ?></div><?php endif; ?>
</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body p-4"><div class="small text-muted fw-semibold mb-2">วิสัยทัศน์</div><div class="fs-5 erp-richtext"><?= $model->vision ? RichText::render($model->vision) : 'ยังไม่ได้ระบุ' ?></div></div></div>
<div class="d-flex flex-wrap gap-2 mb-4"><?= Html::a('ทะเบียนตัวชี้วัด',['/pm/strategy-catalog/index','type'=>'indicator','planId'=>$model->id],['class'=>'btn btn-outline-primary']) ?><?= Html::a('ปัจจัยความสำเร็จ/RCA',['/pm/strategy-catalog/index','type'=>'factor','planId'=>$model->id],['class'=>'btn btn-outline-secondary']) ?><?= Html::a('มาตรการ',['/pm/strategy-catalog/index','type'=>'measure','planId'=>$model->id],['class'=>'btn btn-outline-secondary']) ?><?= Html::a('แผนงาน/โครงการ',['/pm/projects/index'],['class'=>'btn btn-outline-secondary']) ?></div>
<div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h5 mb-1">โครงสร้างยุทธศาสตร์</h2><p class="small text-muted mb-0">พันธกิจ → ประเด็นยุทธศาสตร์ → เป้าประสงค์ → ตัวชี้วัด → กลยุทธ์ → มาตรการ/โครงการ</p></div><?php if ($editable): ?><?= Html::a('<i data-lucide="plus" class="me-1"></i> เพิ่มพันธกิจ',['/pm/strategy-structure/create','type'=>'mission','parentId'=>$model->id],['class'=>'btn btn-primary']) ?><?php endif; ?></div>
<?php if (!$model->missions): ?><div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><div class="text-muted mb-3">ยังไม่มีพันธกิจในชุดแผนนี้</div><?php if ($editable): ?><?= Html::a('เพิ่มพันธกิจแรก',['/pm/strategy-structure/create','type'=>'mission','parentId'=>$model->id],['class'=>'btn btn-outline-primary']) ?><?php endif; ?></div></div><?php endif; ?>
<div class="d-grid gap-3"><?php foreach ($model->missions as $mission): ?><section class="card border-0 shadow-sm"><div class="card-header bg-body-tertiary d-flex justify-content-between align-items-start gap-3 p-3"><div><span class="badge bg-primary me-2"><?= Html::encode($mission->code) ?></span><span class="fw-semibold"><?= Html::encode($mission->name) ?></span></div><?php if ($editable): ?><div class="d-flex gap-1"><?= Html::a('เพิ่มประเด็น',['/pm/strategy-structure/create','type'=>'issue','parentId'=>$mission->id],['class'=>'btn btn-sm btn-outline-primary']) ?><?= Html::a('แก้ไข',['/pm/strategy-structure/update','type'=>'mission','id'=>$mission->id],['class'=>'btn btn-sm btn-outline-secondary']) ?></div><?php endif; ?></div><div class="card-body p-3">
<?php if (!$mission->issues): ?><div class="small text-muted">ยังไม่มีประเด็นยุทธศาสตร์</div><?php endif; ?>
<?php foreach ($mission->issues as $issue): ?><div class="border-start border-primary border-3 ps-3 py-2 mb-3"><div class="d-flex justify-content-between gap-2"><div><span class="fw-semibold"><?= Html::encode($issue->code) ?></span> <?= Html::encode($issue->name) ?></div><?php if ($editable): ?><div class="flex-shrink-0"><?= Html::a('เพิ่มเป้าประสงค์',['/pm/strategy-structure/create','type'=>'goal','parentId'=>$issue->id],['class'=>'btn btn-sm btn-outline-primary']) ?> <?= Html::a('แก้ไข',['/pm/strategy-structure/update','type'=>'issue','id'=>$issue->id],['class'=>'btn btn-sm btn-outline-secondary']) ?></div><?php endif; ?></div>
<ul class="mb-0 mt-2">
<?php foreach ($issue->goals as $goal): ?>
    <li class="mb-4">
        <span class="text-muted"><?= Html::encode($goal->code) ?></span> <?= Html::encode($goal->name) ?>
        <?php if ($editable): ?>
            <span class="d-inline-flex flex-wrap gap-2 ms-2">
                <?= Html::a('แก้ไข', ['/pm/strategy-structure/update', 'type' => 'goal', 'id' => $goal->id], ['class' => 'small']) ?>
                <?= Html::a('+ ตัวชี้วัด', ['/pm/strategy-structure/create', 'type' => 'indicator', 'parentId' => $goal->id], ['class' => 'small']) ?>
                <?= Html::a('+ ปัจจัย/RCA', ['/pm/strategy-catalog/create', 'type' => 'factor', 'parentId' => $goal->id], ['class' => 'small']) ?>
            </span>
        <?php endif; ?>

        <?php /* 4. ตัวชี้วัดหลัก → ตัวชี้วัดรอง → กลยุทธ์ → มาตรการ/โครงการ */ ?>
        <?php $primaries = array_filter($goal->indicators, fn($ind) => !$ind->parent_id); ?>
        <?php if ($primaries): ?>
            <ul class="mt-2 mb-0 list-unstyled ps-3 border-start">
            <?php foreach ($primaries as $indicator): ?>
                <li class="mb-2">
                    <span class="badge bg-info-subtle text-info-emphasis me-1">ตัวชี้วัด</span>
                    <span class="text-muted"><?= Html::encode($indicator->code) ?></span> <?= Html::encode($indicator->name) ?>
                    <?php if ($editable): ?>
                        <span class="d-inline-flex flex-wrap gap-2 ms-2">
                            <?= Html::a('แก้ไข', ['/pm/strategy-structure/update', 'type' => 'indicator', 'id' => $indicator->id], ['class' => 'small']) ?>
                            <?= Html::a('+ ตัวชี้วัดรอง', ['/pm/strategy-structure/create', 'type' => 'sub-indicator', 'parentId' => $indicator->id], ['class' => 'small']) ?>
                            <?= Html::a('+ กลยุทธ์', ['/pm/strategy-structure/create', 'type' => 'tactic', 'parentId' => $indicator->id], ['class' => 'small']) ?>
                            <?= Html::a('ลบ', ['/pm/strategy-structure/delete', 'type' => 'indicator', 'id' => $indicator->id], ['class' => 'small text-danger', 'data-method' => 'post', 'data-confirm' => 'ลบตัวชี้วัดนี้? ตัวชี้วัดรอง กลยุทธ์ และข้อมูลรายปีจะถูกลบตามไปด้วย']) ?>
                        </span>
                    <?php endif; ?>
                    <?= $this->render('_tactics', ['owner' => $indicator, 'editable' => $editable]) ?>
                    <?php if ($indicator->children): ?>
                        <ul class="mt-1 mb-0 list-unstyled ps-3 border-start">
                        <?php foreach ($indicator->children as $child): ?>
                            <li class="mb-2">
                                <span class="badge bg-info-subtle text-info-emphasis me-1">ตัวชี้วัดรอง</span>
                                <span class="text-muted"><?= Html::encode($child->code) ?></span> <?= Html::encode($child->name) ?>
                                <?php if ($editable): ?>
                                    <span class="d-inline-flex flex-wrap gap-2 ms-2">
                                        <?= Html::a('แก้ไข', ['/pm/strategy-structure/update', 'type' => 'sub-indicator', 'id' => $child->id], ['class' => 'small']) ?>
                                        <?= Html::a('+ กลยุทธ์', ['/pm/strategy-structure/create', 'type' => 'tactic', 'parentId' => $child->id], ['class' => 'small']) ?>
                                        <?= Html::a('ลบ', ['/pm/strategy-structure/delete', 'type' => 'sub-indicator', 'id' => $child->id], ['class' => 'small text-danger', 'data-method' => 'post', 'data-confirm' => 'ลบตัวชี้วัดรองนี้? กลยุทธ์ที่ผูกอยู่จะถูกลบตามไปด้วย']) ?>
                                    </span>
                                <?php endif; ?>
                                <?= $this->render('_tactics', ['owner' => $child, 'editable' => $editable]) ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php /* 5. ปัจจัยความสำเร็จ/RCA */ ?>
        <?php if ($goal->factors): ?>
            <ul class="mt-2 mb-0 list-unstyled ps-3 border-start">
            <?php foreach ($goal->factors as $factor): ?>
                <li class="mb-1">
                    <span class="badge bg-warning-subtle text-warning-emphasis me-1"><?= $factor->factor_type === 'rca' ? 'RCA' : 'ปัจจัย' ?></span>
                    <span class="erp-richtext d-inline"><?= RichText::render($factor->name) ?></span>
                    <?php if ($editable): ?><?= Html::a('แก้ไข', ['/pm/strategy-catalog/update', 'type' => 'factor', 'id' => $factor->id], ['class' => 'small ms-2']) ?><?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!$primaries && $editable): ?>
            <div class="small text-muted mt-1 ps-3">ยังไม่มีตัวชี้วัดภายใต้เป้าประสงค์นี้ — กลยุทธ์และโครงการต้องผูกกับตัวชี้วัด</div>
        <?php endif; ?>

        <?php /* กลยุทธ์เก่าที่ยังไม่ได้ผูกตัวชี้วัด — แสดงไว้ให้ย้ายหรือลบ ไม่ปล่อยให้หายไปเงียบ ๆ */ ?>
        <?php if ($goal->orphanTactics): ?>
            <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
                <div class="fw-semibold mb-1">กลยุทธ์ที่ยังไม่ได้ผูกตัวชี้วัด</div>
                <ul class="mb-0 ps-3">
                <?php foreach ($goal->orphanTactics as $orphan): ?>
                    <li>
                        <?= Html::encode($orphan->label()) ?>
                        <?php if ($editable): ?>
                            <?= Html::a('ย้ายไปใต้ตัวชี้วัด', ['/pm/strategy-structure/update', 'type' => 'tactic', 'id' => $orphan->id], ['class' => 'ms-2']) ?>
                            <?= Html::a('ลบ', ['/pm/strategy-structure/delete', 'type' => 'tactic', 'id' => $orphan->id], ['class' => 'ms-2 text-danger', 'data-method' => 'post', 'data-confirm' => 'ลบกลยุทธ์นี้?']) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul></div><?php endforeach; ?>
</div></section><?php endforeach; ?></div>
