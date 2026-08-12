<?php
use yii\helpers\Html;

$this->title = 'การตั้งค่าทะเบียนทรัพย์สิน';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<?php $this->beginBlock('action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <i class="bi bi-gear"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
หมวดหมู่ทรัพย์สิน
<?php $this->endBlock(); ?>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-between align-items-lg-center gap-3">
        <div>
            <div class="text-muted small">หมวดที่กำลังดู</div>
            <h5 class="mb-1"><?= Html::encode((($title ?? '') ?: 'หมวดหมู่ทรัพย์สิน')) ?></h5>
            <span class="badge rounded-pill bg-primary-subtle text-primary">
                <?= Html::encode((($code ?? '') ?: '-')) ?>
            </span>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <?= app\components\AppHelper::Btn([
                'title' => '<i class="bi bi-plus-circle"></i> สร้างกลุ่มครุภัณฑ์',
                'url' => ['/sm/asset-type/create', 'name' => 'asset_type', 'category_id' => $code],
                'modal' => true,
                'size' => 'lg',
            ]) ?>
            <?= Html::a('<i class="bi bi-gear me-1"></i> ตั้งค่ากลุ่มทรัพย์สิน', ['/sm/asset-type'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary-gradient text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 text-white"><i class="bi bi-layers me-1"></i> รายการหมวดหมู่ทรัพย์สิน</h6>
        <span class="small text-white-50">คลิกชื่อหมวดเพื่อเข้าไปดูรายการครุภัณฑ์</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php $groups = $dataProvider->getModels(); ?>
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $model): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-shrink-0">
                                <?php if ($model->code == Yii::$app->request->get('code')): ?>
                                    <i class="bi bi-folder2-open fs-2 text-primary"></i>
                                <?php else: ?>
                                    <i class="bi bi-folder-check fs-2"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?= Html::a($model->title, ['/sm/asset-item', 'code' => $model->code, 'name' => 'asset_type', 'title' => $model->title], ['class' => 'fw-semibold text-decoration-none']) ?>
                                <div class="d-flex gap-2 flex-wrap mt-2">
                                    <span class="badge rounded-pill bg-success-subtle text-success">
                                        ประเภท <?= number_format((int) $model->CountTypeOnGroup()) ?>
                                    </span>
                                    <span class="badge rounded-pill bg-danger-subtle text-danger">
                                        รายการ <?= number_format((int) $model->CountItemOnType()) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <?= Html::a('<i class="bi bi-folder2-open me-1"></i> เปิด', ['/sm/asset-item', 'code' => $model->code, 'name' => 'asset_type', 'title' => $model->title], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-group-item text-center text-muted py-4">
                    ไม่พบข้อมูลหมวดหมู่ทรัพย์สิน
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
