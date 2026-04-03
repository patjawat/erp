<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'กำหนดวันหยุด';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0"><i class="bi bi-gear"></i> <?= $this->title ?></h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/menu_admin', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'leave']); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มวันหยุด', ['/leave/holiday/create', 'title' => 'เพิ่มวันหยุด'], ['class' => 'btn btn-primary open-modal rounded-pill shadow', 'data' => ['size' => 'modal-md']]) ?>
                <?= Html::a('<i class="fa-solid fa-cloud"></i> โหลดวันหยุดอัตโนมัติ', ['/leave/holiday/sync-date'], ['class' => 'btn btn-secondary rounded-pill shadow sync-date text-white']) ?>
            </div>
            <?= $this->render('_search', ['model' => $searchModel]) ?>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> วันหยุด <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount() ?></span> รายการ</h6>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" style="width:150px">วันที่</th>
                    <th scope="col">รายการ</th>
                    <th scope="col" class="text-center" style="width:120px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $model): ?>
                <tr>
                    <td scope="row"><?= Yii::$app->thaiFormatter->asDate($model->date_start, 'long') ?></td>
                    <td><?= Html::encode($model->title) ?></td>
                    <td class="text-center">
                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/leave/holiday/update', 'id' => $model->id, 'title' => 'แก้ไข'], ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-md']]) ?>
                        <?= Html::a('<i class="fa-solid fa-trash"></i>', ['/leave/holiday/delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-danger delete-item', 'data-method' => 'post', 'data-confirm' => 'ยืนยันลบ?']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-4">
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
$this->registerJs("
$('.sync-date').on('click', function (e) {
    e.preventDefault();
    var href = $(this).attr('href');
    Swal.fire({
        title: 'ยืนยัน',
        html: '<i class=\"bi bi-database-fill-check me-1 fs-1\"></i> การซิงค์ข้อมูลวันหยุด',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'ยกเลิก',
        confirmButtonText: 'ยืนยัน'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.ajax({ type: 'get', url: href, dataType: 'json' }).done(function (res) {
                if (res.status == 'success') window.location.reload(true);
            });
        }
    });
});
", View::POS_END);
?>
<?php Pjax::end(); ?>
