<?php

use app\modules\warehouse\models\Warehouse;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

$this->title = 'ระบบคลัง';
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('./menu') ?>
<?php $this->endBlock(); ?>



<?php Pjax::begin(['id' => 'warehouse']); ?>

<div class="container-lg" id="listWarehouse">
<div class="row d-flex justify-content-center">


<?php foreach ($dataProvider->getModels() as $model): ?>
    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
        <!-- Card -->
        <div
            class="p-2 bg-white rounded transform transition-all hover-translate-y-n2 duration-300 shadow-lg hover-shadow mt-3">
            <!-- Image -->

            <?php echo Html::img($model->ShowImg(), ['class' => 'h-40 object-cover rounded img-fluid']) ?>
            <div class="p-2">
                <!-- Heading -->
                 <div class="d-flex justify-content-between">
                     <h2 class="font-weight-bold h5 mb-2"><?= $model->warehouse_name ?></h2>
                     <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-2"></i> แก้ไข', ['/warehouse/warehouse/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]); ?>
                            <?= Html::a('<i class="bx bx-trash me-1"></i>ลบ', ['/warehouse/warehouse/delete', 'id' => $model->id], ['class' => 'dropdown-item  delete-item']) ?>
                        </div>
                    </div>
                 </div>
                <!-- Description -->
                 <?= $model->avatarStack() ?>
                <!-- <p class="text-muted small"></p> -->
            </div>
            <!-- CTA -->

            <div class="d-grid gap-2 m-2" id="selectWarehouse<?= $model->id ?>">
                <?= html::a('เลือก', [
                    '/warehouse/warehouse/view',
                    'id' =>
                        $model->id
                ], ['class' => 'btn btn-primary text-white bg-purple-600 rounded-md']) ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

</div>
<?php Pjax::end(); ?>


<?php
$js = <<< JS

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
        // {
        //     content: "Card Deux",
        //     title: "This is the second card",
        //     target: "#card2",
        //     order: "",
        //     group: "groupA",
        //     beforeEnter: ()=>{
        //         return new Promise((resolve, reject) => {
        //             // Reject entering the step
        //             reject('skip step')
        //             // Prevent loading state from stopping step navigation
        //             tg._promiseWaiting = false
        //             // Get the desired step index based on direction
        //             let skipToIndex = 4
        //             if (tg.activeStep === 4) skipToIndex = 2
        //             // Visit the desired step
        //             tg.visitStep(skipToIndex)
        //         })
        //     }
        // },{
        //     content: "Card Three",
        //     title: "This is the last card",
        //     target: "#card3",
        //     order: "",
        //     group: "groupA",
        // }
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