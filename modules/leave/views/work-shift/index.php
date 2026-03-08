<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'กำหนดเวร 8';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><i class="bi bi-gear"></i> <?= $this->title ?></h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/views/menu_admin', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'leave']); ?>
<div class="card">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-bottom">
        <h6 class="mb-0"><i class="bi bi-search me-1"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('@app/modules/hr/views/work-shift/_search', ['model' => $searchModel]) ?>
    </div>
</div>
<div class="card">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-bottom">
        <h6 class="mb-0">
            <i class="bi bi-ui-checks"></i> <?= $this->title ?>
            <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?></span> รายการ
        </h6>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th class="fw-semibold">ชื่อ-นามสกุล</th>
                    <th class="fw-semibold">ประเภท</th>
                    <th class="fw-semibold text-center">แผนก/ฝ่าย</th>
                    <th class="text-center" style="width:100px">ใช้เวร 8</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <tr>
                    <td class="text-center"><?= ($dataProvider->pagination->offset + 1) + $key ?></td>
                    <td><?= $item->getAvatar(false) ?></td>
                    <td><?= $item->positionType ? $item->positionType->title : '-' ?></td>
                    <td class="text-center"><?= $item->departmentName() ?></td>
                    <td class="text-center">
                        <div class="form-check form-switch">
                            <?= Html::checkbox('work_shift', $item->work_shift == 'shift', [
                                'class' => 'form-check-input use-shift8',
                                'data' => ['id' => $item->id],
                            ]) ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => ['class' => 'pagination pagination-sm'],
            ]) ?>
        </div>
    </div>
</div>
<?php
$updateUrl = Url::to(['/leave/work-shift/update-shift']);
$this->registerJs("
$('body').on('change', '.use-shift8', function () {
    var id = $(this).data('id');
    var value = $(this).is(':checked') ? 'shift' : 'normal';
    $.ajax({
        url: '" . addslashes($updateUrl) . "',
        type: 'POST',
        data: { id: id, work_shift: value },
        success: function () { if (typeof success === 'function') success('บันทึกสำเร็จ'); else alert('บันทึกสำเร็จ'); },
        error: function () { if (typeof error === 'function') error('เกิดข้อผิดพลาด'); else alert('เกิดข้อผิดพลาด'); }
    });
});
", View::POS_END);
?>
<?php Pjax::end(); ?>
