<?php
use app\models\Tree;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\tree\TreeView;


use kartik\tree\TreeViewInput;

// use muhsamsul\treeimage\TreeImage;
use app\widgets\orgchart\TreeImage;
use app\modules\hr\models\Organization;
$this->title = "ผังโครงสร้างองค์กร";

$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-diagram-3"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/employees/menu',['active' => 'employees'])?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/views/employees/menu') ?>
<?php $this->endBlock(); ?>
<style>
    .kv-tree-root.kv-root-heading {
    font-weight: 300;
}
.kv-detail-crumbs .kv-crumb-active {
    font-weight: 300;
}
.kv-detail-container {
    border-radius: 9px;
    background-color: white;
}
</style>
<?php Pjax::begin(['id' => 'hr-container','enablePushState' => true,'timeout' => 50000 ]); ?>
<?php // $this->render('../menu')?>

<?php
/** @var app\models\Categorise $levelNamesModel */
$levelLabels = is_array($levelNamesModel->data_json) ? $levelNamesModel->data_json : [];
$defaultLabels = [
    1 => 'ผอ. หรือ หัวหน้า',
    2 => 'หัวหน้างาน',
    3 => 'หัวหน้ากลุ่มงาน',
    4 => 'ระดับที่ 4',
    5 => 'ผอ./บนสุด',
];
?>
<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-body">
        <h6 class="fw-semibold text-body mb-3">ตั้งชื่อระดับในผังองค์กร</h6>
        <p class="text-muted small mb-2"><strong>ตามหลัก:</strong> เรียกจาก<strong>ระดับลึกสุด</strong> (แผนก/หน่วยของพนักงาน) ขึ้นมาก่อน แล้วค่อยขึ้นไประดับบน — ชื่อจะไปแสดงในตั้งค่าระดับการอนุมัติ (approve-v3)</p>
        <p class="text-muted small mb-2"><strong>ระดับที่ 1 ต้องตั้งค่าเป็น "ผอ." หรือ "หัวหน้า"</strong> (แล้วแต่โครงสร้างหน่วยงาน)</p>
        <p class="text-muted small mb-3">ตัวอย่างลำดับ: หัวหน้างาน → หัวหน้างาน → หัวหน้ากลุ่มงาน → ตรวจสอบ → ผอ.อนุมัติ (หรือกำหนดผู้อนุมัติแทนก็ได้)</p>
        <?php $f = \yii\widgets\ActiveForm::begin([
            'action' => \yii\helpers\Url::to(['/hr/organization/save-diagram-level-names']),
            'method' => 'post',
            'options' => ['class' => 'row g-3'],
            'fieldConfig' => [
                'labelOptions' => ['class' => 'form-label small mb-0'],
                'inputOptions' => ['class' => 'form-control'],
            ],
        ]); ?>
        <div class="row g-2 g-md-3">
            <?php for ($lvl = 1; $lvl <= 5; $lvl++): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted">ระดับที่ <?= $lvl ?><?= $lvl === 1 ? ' <span class="text-danger">*</span>' : '' ?></label>
                <input type="text" name="levelNames[<?= $lvl ?>]" class="form-control" placeholder="<?= Html::encode($defaultLabels[$lvl] ?? '') ?>"
                    value="<?= Html::encode($levelLabels[(string)$lvl] ?? '') ?>"<?= $lvl === 1 ? ' title="ต้องตั้งเป็น ผอ. หรือ หัวหน้า"' : '' ?>>
                <?php if ($lvl === 1): ?>
                <p class="form-text small text-muted mb-0 mt-1">ต้องเป็น ผอ. หรือ หัวหน้า</p>
                <?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
        <div class="mt-3">
            <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึกชื่อระดับ', ['class' => 'btn btn-primary rounded-3']) ?>
        </div>
        <?php \yii\widgets\ActiveForm::end(); ?>
    </div>
</div>

<?php

echo TreeView::widget([
    'query' => Organization::find()->where(['tb_name' => 'diagram'])->addOrderBy('root, lft'), 
    'headingOptions' => ['label' => 'Store'],
    'rootOptions' => ['label'=>'<span class="text-primary">'.$this->title.'</span>'],
    'topRootAsHeading' => true, // this will override the headingOptions
    'fontAwesome' => true,
    'isAdmin' => false,
    'showIDAttribute' => false,
    'showNameAttribute' => false,
    'softDelete' => true,  
    'displayValue' => '0',
    'nodeAddlViews' => [kartik\tree\Module::VIEW_PART_1 => '@app/modules/hr/views/organization/diagram/_form'],   
    // 'nodeView' => '@app/modules/hr/views/categorise/_form',['model' => $model],
    'iconEditSettings'=> [
        'show' => 'list',
        'show' => 'none',
        'listData' => [
            'folder' => 'Folder',
            'file' => 'File',
            'mobile' => 'Phone',
            'bell' => 'Bell',
        ]
    ],
    

    'softDelete' => true,
    'cacheSettings' => ['enableCache' => true],
    'mainTemplate'=>'<div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                            {wrapper}
                        </div>
                        <div class="col-xl-9 col-lg-9 col-md-6 col-sm-12">
                            {detail}
                        </div>
                     </div>',


]);
$js = <<< JS


JS;
$this->registerJS($js);
?>
<?php Pjax::end();?>
