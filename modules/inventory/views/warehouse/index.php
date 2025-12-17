<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

$this->title = 'ตั้งค่าระบบคลัง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
            <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'inventory']); ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> จำนวนคลัง
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between">
                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างใหม่', ['/inventory/warehouse/create', 'title' => '<i class="fa-solid fa-circle-plus me-1"></i> สร้างคลังใหม่'], ['id' => 'addWarehouse', 'class' => 'btn btn-light open-modal mt-2', 'data' => ['size' => 'modal-xl']]); ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" style="width:50px">รหัส</th>
                    <th scope="col">ชื่อรายการ</th>
                    <th scope="col">หน่วยงาน</th>
                    <th scope="col">ประเภทคลัง</th>
                    <th scope="col">ผู้รับผิดชอบคลัง</th>
                    <th scope="col" style="width:150px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="">
                        <td scope="row"><?= $model->id ?></td>
                        <td><?= $model->warehouse_name ?></td>
                        <td><?= $model->departmentName() ?></td>
                        <td><?= $model->viewWarehouseType() ?>
                        </td>
                        <td><?= $model->avatarStack() ?></td>
                        <td class="d-flex justify-content-center gap-2">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/warehouse/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-xl']]); ?>
                            <?= Html::a('<i class="fa-solid fa-trash"></i>', ['/inventory/warehouse/delete', 'id' => $model->id], ['class' => 'btn btn-danger delete-item']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-5">
            <div class="text-muted">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'listOptions' => 'pagination pagination-sm',
                        'class' => 'pagination-sm',
                    ],
                ]); ?>
            </div>
        </div>

    </div>
</div>
</div>



<?php Pjax::end(); ?>

<?php
$warehouseUrl = Url::to(['/inventory/warehouse/set-warehouse']);
$js = <<< JS

    \$('.selct-warehouse').click(function (e) { 
        e.preventDefault();
        var title = \$(this).data('title');
        var imageUrl = \$(this).data('img');
        var warehouse_id = \$(this).data('warehouse_id');
        Swal.fire({
            imageUrl: imageUrl,
            imageWidth: 400,
            imageHeight: 200,
            title: title,
            text: "ต้องการเข้าใช้งานคลังนี้ !",
            // icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "<i class='bi bi-x-circle'></i> ยกเลิก",
            confirmButtonText: "<i class='bi bi-check-circle'></i> ยืนยัน"
            }).then((result) => {
            if (result.isConfirmed) {
                // Swal.fire({
                // title: "Deleted!",
                // text: "Your file has been deleted.",
                // icon: "success"
                // });
                \$.ajax({
                    type:"get",
                    url: "$warehouseUrl",
                    data: {id:warehouse_id},
                    dataType: "json",
                    success: function (response) {
                        console.log(response);
                    }
                });
            }
            });
        
    });

JS;
$this->registerJS($js, View::POS_END);

?>