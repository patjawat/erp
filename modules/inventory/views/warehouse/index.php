<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\inventory\models\Warehouse;

$this->title = 'ตั้งค่าระบบคลัง';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/inventory/views/default/menu_dashbroad') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu_dashbroad',['active' => 'warehouse'])?>
<?php $this->endBlock(); ?>



<?php Pjax::begin(['id' => 'inventory']); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> จำนวนคลัง <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?></span> รายการ</h6>
            <div class="d-flex flex-row align-items-center gap-3">
                <?= $this->render('_search', ['model' => $searchModel]); ?>
                <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างคลังใหม่', ['/inventory/warehouse/create', 'title' => '<i class="fa-solid fa-circle-plus me-1"></i> สร้างคลังใหม่'], ['id' => 'addWarehouse', 'class' => 'btn btn-primary open-modal mt-2', 'data' => ['size' => 'modal-xl']]); ?>
            </div>
        </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" style="width:50px">รหัส</th>
                    <th scope="col">ชื่อรายการ</th>
                    <th scope="col">ประเภทคลัง</th>
                    <th scope="col">ผู้รับผิดชอลคลัง</th>
                    <th scope="col" style="width:150px">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach ($dataProvider->getModels() as $model): ?>
                <tr class="">
                    <td scope="row"><?=$model->id ?></td>
                    <td><?=$model->warehouse_name ?></td>
                    <td><?=($model->warehouse_type == 'MAIN' ? 'คลังหลัก <i class="fa-solid fa-crown text-warning"></i>' : 'คลังย่อย') ?>
                    </td>
                    <td><?= $model->avatarStack() ?></td>
                    <td class="d-flex justify-content-center gap-2">
                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/warehouse/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning open-modal', 'data' => ['size' => 'modal-xl']]); ?>
                        <?= Html::a('<i class="fa-solid fa-trash"></i>', ['/inventory/warehouse/delete', 'id' => $model->id], ['class' => 'btn btn-danger delete-item']) ?>
                    </td>
                </tr>
                <?php endforeach;?>
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

    const steps = [{
            content: "เลือกรายการคลังที่ต้องการ",
            title: "รายการคลัง 👋",
            target: "#listWarehouse",
            order: "",
            group: "groupA",
        },{
            content: "เลือกคลังเพื่อจะสามารถเข้าไปใช้งาน",
            title: "เลือกคลัง",
            target: "#selectWarehouse1",
            // dialogTarget: "#card1",
            order: "",
            group: "groupA"
        },
        {
            content: "คลิ๊กที่นี่เพื่อสร้างคลัง",
            title: "การสร้างคลังใหม่",
            target: "#addWarehouse",
            order: "",
            group: "groupA",
        
        },
    ]

        const tg = new tourguide.TourGuideClient({
            steps: steps,
            group: "groupA",
            completeOnFinish: true,
            allowDialogOverlap: true,
            backdropColor: string = "rgba(20,20,21,0.30)"
        })

        function openTour(){
            tg.start()
        }

    JS;
$this->registerJS($js, View::POS_END);

?>