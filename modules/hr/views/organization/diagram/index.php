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
// สอดคล้องกับผัง: ระดับที่ 1 = กลุ่มงาน (lvl 2 ใน tree), ระดับที่ 2 = ประเภท (lvl 1 ใน tree)
$defaultLabels = [
    1 => 'หัวหน้างาน',
    2 => 'ผอ. หรือ หัวหน้า',
    3 => 'หัวหน้ากลุ่มงาน',
    4 => 'ระดับที่ 4',
    5 => 'ผอ./บนสุด',
];
?>
<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-body">
        <h6 class="fw-semibold text-body mb-3">ตั้งชื่อระดับในผังองค์กร</h6>
        <p class="text-muted small mb-2"><strong>ตามหลัก:</strong> เรียกจาก<strong>ระดับลึกสุด</strong> (แผนก/หน่วยของพนักงาน) ขึ้นมาก่อน แล้วค่อยขึ้นไประดับบน — ชื่อจะไปแสดงในตั้งค่าระดับการอนุมัติ (approve-v2)</p>
        <p class="text-muted small mb-2"><strong>ความสอดคล้องกับผัง:</strong> <strong>ระดับที่ 1</strong> = ระดับโหนด "กลุ่มงาน" (lvl 2 ใน tree), <strong>ระดับที่ 2</strong> = ระดับโหนด "ประเภท" (lvl 1 ใน tree) — <strong>ระดับที่ 2 ควรตั้งเป็น "ผอ." หรือ "หัวหน้า"</strong></p>
        <p class="text-muted small mb-3">ตัวอย่างลำดับการอนุมัติ: หัวหน้างาน (ระดับที่ 1) → ผอ. อนุมัติ (ระดับที่ 2) หรือกำหนดผู้อนุมัติแทนได้</p>
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
            <?php
            // สลับ key 1↔2 ให้สอดคล้องกับ approve-v2: ระดับที่ 1 แสดง/เก็บที่ key 2, ระดับที่ 2 ที่ key 1
            $storageKey = ($lvl === 1) ? '2' : (($lvl === 2) ? '1' : (string) $lvl);
            $value = $levelLabels[$storageKey] ?? '';
            ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted">ระดับที่ <?= $lvl ?><?= $lvl === 2 ? ' <span class="text-danger">*</span>' : '' ?></label>
                <input type="text" name="levelNames[<?= $storageKey ?>]" class="form-control" placeholder="<?= Html::encode($defaultLabels[$lvl] ?? '') ?>"
                    value="<?= Html::encode($value) ?>"<?= $lvl === 2 ? ' title="ระดับบนสุดในผัง (ประเภท) — ควรตั้งเป็น ผอ. หรือ หัวหน้า"' : '' ?>>
                <?php if ($lvl === 2): ?>
                <p class="form-text small text-muted mb-0 mt-1">ระดับบนสุด (ประเภท) — ควรเป็น ผอ. หรือ หัวหน้า</p>
                <?php elseif ($lvl === 1): ?>
                <p class="form-text small text-muted mb-0 mt-1">ระดับกลุ่มงาน (lvl 2 ในผัง)</p>
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

<div class="mb-3 text-end">
    <?= Html::button('<i class="bi bi-trash3 me-1"></i> ล้างรายการที่ถูกลบแล้วถาวร', [
        'id' => 'btn-purge-inactive',
        'class' => 'btn btn-outline-danger btn-sm rounded-3',
        'title' => 'ลบถาวรเฉพาะ node ที่ถูกปิดใช้งาน (active = 0) และไม่มีลูกอยู่ข้างใต้ ออกจากผัง',
    ]) ?>
</div>

<?php

echo TreeView::widget([
    'query' => Organization::find()->where(['tb_name' => 'diagram'])->addOrderBy('root, lft'), 
    'headingOptions' => ['label' => 'Store'],
    'rootOptions' => ['label'=>'<span class="text-primary">'.$this->title.'</span>'],
    'topRootAsHeading' => true, // this will override the headingOptions
    'fontAwesome' => true,
    'isAdmin' => false,
    'showInactive' => true,
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
$purgeUrl = \yii\helpers\Url::to(['/hr/organization/purge-inactive']);
$js = <<< JS

$('#btn-purge-inactive').on('click', function () {
    Swal.fire({
        title: 'ยืนยันการล้างข้อมูล?',
        text: 'จะลบถาวรเฉพาะ node ที่ถูกปิดใช้งานแล้วและไม่มีลูกอยู่ข้างใต้ ไม่สามารถกู้คืนได้',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ล้างข้อมูล',
        cancelButtonText: 'ยกเลิก'
    }).then(function (result) {
        if (!result.isConfirmed) {
            return;
        }
        \$.ajax({
            type: 'POST',
            url: '$purgeUrl',
            data: { tb_name: 'diagram', _csrf: yii.getCsrfToken() },
            dataType: 'json',
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'ล้างข้อมูลสำเร็จ',
                    text: 'ลบไปทั้งหมด ' + res.count + ' รายการ',
                    timer: 2500,
                    showConfirmButton: false
                }).then(function () {
                    \$.pjax.reload({container: '#hr-container'});
                });
            },
            error: function () {
                Swal.fire({icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถล้างข้อมูลได้ กรุณาลองใหม่'});
            }
        });
    });
});

JS;
$this->registerJS($js);
?>
<?php Pjax::end();?>
