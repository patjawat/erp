<?php

use app\modules\inventory\models\Order;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\OrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'รับสินค้า';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>


<div class="order-index">

    <div class="card">
        <div class="card-body">
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> รับเข้า', ['/inventory/receive/create', 'receive_type' => 'receive', 'title' => '<i class="fa-solid fa-cubes-stacked"></i> ใบรับสินค้า'], ['id' => 'btn-add1', 'class' => 'btn btn-success open-modal', 'data' => ['size' => 'modal-lg']]) ?>
            <?= Html::a('<i class="fa-solid fa-file-circle-plus"></i> รับจากใบสั่งซื้อ', ['/inventory/receive/list-order-by-po', 'title' => '<i class="fa-solid fa-file-circle-plus"></i> รายการรอรับเข้าคลัง'], ['id' => 'btn-add2', 'class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </div>
    </div>


    <?php Pjax::begin(['id' => 'inventory']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'header' => 'วันที่',
                'value' => function ($model) {
                    return $model->movement_date;
                }
            ],
            [
                // 'attribute' => 'code',
                'header' => 'เลขที่เอกสาร',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->rc_number, ['view', 'id' => $model->id]);
                }
            ],
            [
                // 'attribute' => 'code',
                'header' => 'เลขที่สั่งซื้อ',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->po_number;
                }
            ],
            [
                'attribute' => 'category_id',
                'header' => 'ประเภท',
                'value' => function ($model) {
                    return $model->viewReceiveType();
                }
            ],
            [
                // 'attribute' => 'category_id',
                'header' => 'สาขา',
                'value' => function ($model) {
                    return $model->tomWarehouse();
                }
            ],
            [
                'header' => 'ผู้จำหน่วย',
                'value' => function ($model) {
                    // return $model->category_id;
                }
            ],
            [
                'header' => 'หมายเหตุ',
                'value' => function ($model) {
                    // return $model->category_id;
                }
            ],
            [
                'header' => 'สถานะ',
                'value' => function ($model) {
                    return $model->viewStatus();
                }
            ],
        ],
    ]); ?>

<?php

$js = <<< JS
      const steps = [{
        content: "การรับเข้าทั่วไป",
        title: "การรับเข้าทั่วไป 👋",
        target: "#btn-add1",
        order: "",
        group: "groupA",
    },{
        content: "การรับเข้าจากใบสั่งซื้อ",
        title: "การรับเข้าจากใบสั่งซื้อ",
        target: "#btn-add2",
        dialogTarget: "#card1",
        order: "",
        group: "groupA"
    },{
        content: "Card One",
        title: "This is the first card",
        target: "#card1",
        order: "",
        group: "groupA",
        beforeEnter: ()=>{
            console.log('wait 6 seconds')
            return new Promise((resolve, reject) => {
                setTimeout(function () {  resolve(); }, 1000);
            })
        }
    },{
        content: "Card Deux",
        title: "This is the second card",
        target: "#card2",
        order: "",
        group: "groupA",
        beforeEnter: ()=>{
            return new Promise((resolve, reject) => {
                // Reject entering the step
                reject('skip step')
                // Prevent loading state from stopping step navigation
                tg._promiseWaiting = false
                // Get the desired step index based on direction
                let skipToIndex = 4
                if (tg.activeStep === 4) skipToIndex = 2
                // Visit the desired step
                tg.visitStep(skipToIndex)
            })
        }
    },{
        content: "Card Three",
        title: "This is the last card",
        target: "#card3",
        order: "",
        group: "groupA",
    }]

    const tg = new tourguide.TourGuideClient({
        steps: steps,
        group: "groupA",
        completeOnFinish: true,
        allowDialogOverlap: true,
        backdropColor: string = "rgba(20,20,21,0.50)"
    })

    // const triggerBtn = document.getElementById('tourTrigger')

    // tg.onBeforeStepChange(()=>{
    //     return new Promise((resolve, reject) => {
    //         setTimeout(function () {  resolve(); }, 3000);
    //     })
    // })


    function openTour(){
        tg.start()
    }
    JS;

$this->registerJS($js, View::POS_END);
?>


    <?php Pjax::end(); ?>

</div>