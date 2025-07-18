<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\db\Expression;
use app\modules\inventory\models\Warehouse;
$cart = \Yii::$app->cart;
//ตรวจสอบว่าเป็นผู้ดูแลคลัง
// $userid = \Yii::$app->user->id;
// $getWarehouse = Warehouse::find()->andWhere(new Expression("JSON_CONTAINS(data_json->'$.officer','\"$userid\"')"))->one();
// if ($getWarehouse) {
//     $warehouse = $warehouse->id;
// } else {
//     $warehouse = 0;
// }

?>



<?php Pjax::begin(['id' => 'inventory-container', 'enablePushState' => false, 'timeout' => 88888888]); ?>
<?php echo $this->render('_search', ['model' => $searchModel]); ?>

<div class="d-flex flex-wrap overflow-scroll p-3" style="height:500px;background-color: #eceff3;">
    <?php foreach ($dataProvider->getModels() as $model): ?>
    <div class="p-2 col-2">
        <div class="card position-relative rounded">
            <p class="position-absolute top-0 end-0 p-2">
                <i class="fa-solid fa-circle-info fs-4"></i>
            </p>
            <?php echo Html::img($model->product->ShowImg(),  ['class' => 'card-top object-fit-cover','style' => 'max-height: 125px;']); ?>
            <div class="card-body w-100">

                    <div class="d-flex justify-content-start align-items-center">
                        <span class="badge text-bg-primary  mt--45"><?php echo $model->SumQty(); ?>
                            <?php echo $model->product->unit_name; ?></span>
        
                    </div>
                    <p class="text-truncate mb-0"><?php echo $model->product->title; ?></p>

                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold text-danger"><?php echo number_format($model->unit_price,2); ?></code>
                        <?php if($model->SumQty() >= 1):?>
                            <?=Html::a('เลือก', ['/helpdesk/stock/add-to-cart', 'id' => $model->id], ['class' => 'add-cart btn btn-sm btn-primary rounded-pill']);?>
                            <?php else:?>
                                <span class="btn btn-sm btn-secondary rounded-pill">หมด</span>
                            <?php endif;?>
                    </div>

            </div>
        </div>

    </div>
    <?php endforeach?>
</div>

<?php Pjax::end(); ?>

<?php

$js = <<< JS

$('.add-cart').click(function (e) { 
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            console.log(res);
            $.pjax.reload({ container:res.container, history:false,replace: false,timeout: false});
            
        }
    });
    
});
  
JS;
$this->registerJS($js,View::POS_END)
?>