<?php

use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'อาคาร/สิ่งปลูกสร้าง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;

$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-1">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 text-primary-gradient">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 12h4"></path>
            <path d="M10 8h4"></path>
            <path d="M14 21v-3a2 2 0 0 0-4 0v3"></path>
            <path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2"></path>
            <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"></path>
        </svg>
        ทะเบียน<?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/views/asset/_action_menu',['model' => $model]) ?>
<?php $this->endBlock(); ?>


<?= $this->render('@app/modules/am/views/asset/_title', ['model' => $model]) ?>

<?php
$structureGroupName = is_array($model->data_json) ? ($model->data_json['structure_group_name'] ?? '') : '';
$structureTypeName  = is_array($model->data_json) ? ($model->data_json['structure_type_name']  ?? '') : '';
$structureOther     = is_array($model->data_json) ? ($model->data_json['structure_type_other'] ?? '') : '';
?>
<?php if ($structureGroupName !== '' || $structureTypeName !== ''): ?>
<div class="card mt-4 border-0 shadow-sm rounded-2">
    <div class="card-body py-3 px-4 d-flex flex-wrap align-items-center gap-3" style="font-size: 0.9rem;">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="layers" class="text-secondary"></i>
            <span class="text-secondary">ประเภทสิ่งปลูกสร้าง</span>
        </div>
        <?php if ($structureGroupName !== ''): ?>
            <span class="badge rounded-2 fw-medium border" style="background-color: rgba(13,110,253,0.08); color: #0a58ca; border-color: rgba(13,110,253,0.22); padding: 4px 10px;">
                <?= \yii\helpers\Html::encode($structureGroupName) ?>
            </span>
        <?php endif; ?>
        <?php if ($structureTypeName !== ''): ?>
            <span class="text-dark fw-semibold"><?= \yii\helpers\Html::encode($structureTypeName) ?></span>
        <?php endif; ?>
        <?php if ($structureOther !== ''): ?>
            <span class="text-muted">· <?= \yii\helpers\Html::encode($structureOther) ?></span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card mt-4">
    <div class="card-header">
        <?= $this->render('@app/modules/am/views/asset/_view_menu', ['model' => $model, 'menu' => 'detail']) ?>
    </div>
    <div class="card-body">
        <?= $this->render('@app/modules/am/views/asset/_details', ['model' => $model]) ?>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <?= $model->listShowImage() ?>
    </div>
</div>


<?php
$js = <<< JS


$('.delete-asset').click(function (e) { 
    e.preventDefault();
    let url = $(this).attr('href');

    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลนี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire({
                            title: 'ลบข้อมูลสำเร็จ!',
                            text: 'รายการถูกลบเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1000, // ตั้งค่าให้ Swal ปิดอัตโนมัติหลัง 1 วินาที
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '/am/asset'; // Redirect หลังจาก timer หมด
                        });
                    } else {
                        Swal.fire(
                            'เกิดข้อผิดพลาด!',
                            res.message || 'ไม่สามารถลบข้อมูลได้',
                            'error'
                        );
                    }
                },
                error: function () {
                    Swal.fire(
                        'เกิดข้อผิดพลาด!',
                        'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        'error'
                    );
                }
            });
        }
    });
});

JS;
$this->registerJS($js, \yii\web\View::POS_END);

?>