<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\db\Expression;
use app\modules\inventory\models\Warehouse;
//ตรวจสอบว่าเป็นผู้ดูแลคลัง
$userid = \Yii::$app->user->id;
$getWarehouse = Warehouse::find()->andWhere(new Expression("JSON_CONTAINS(data_json->'$.officer','\"$userid\"')"))->one();
if ($getWarehouse) {
    $warehouse = $warehouse->id;
} else {
    $warehouse = 0;
}

?>
<div
class="table-responsive"
>
<table
class="table table-primary"
>
<thead>
    <tr>
        <th scope="col">รายการ</th>
        <th scope="col">จำนวน</th>
        <th scope="col">ราคา</th>
    </tr>
</thead>
<tbody>
    <tr class="">
        <td scope="row">R1C1</td>
        <td>R1C2</td>
        <td>R1C3</td>
    </tr>
    <tr class="">
        <td scope="row">Item</td>
        <td>Item</td>
        <td>Item</td>
    </tr>
    
</tbody>
</table>
</div>

<?php Pjax::begin(['id' => 'inventory-container', 'enablePushState' => true, 'timeout' => 88888888]); ?>
<?php echo $this->render('_search', ['model' => $searchModel]); ?>

<div class="d-flex flex-wrap overflow-scroll" style="height:500px;background-color: #eceff3;">
            <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="p-2 col-2">
                <div class="card position-relative rounded">
                    <p class="position-absolute top-0 end-0 p-2">
                        <i class="fa-solid fa-circle-info fs-4"></i>
                    </p>
                    <?php echo Html::img($model->product->ShowImg(), ['class' => 'card-top']); ?>
                    <div class="card-body w-100">
                        <a href="<?=isset($model->getLotQty()['id']) ? Url::to(['/inventory/main-stock/add-to-cart', 'id' => $model->getLotQty()['id']]) : '#'?>" class="add-cart">
                        
                        <div class="d-flex justify-content-start align-items-center">
                            <?php if($model->SumQty() >= 1):?>
                            <span class="badge text-bg-primary  mt--45"><?php echo $model->SumQty(); ?>
                                <?php echo $model->product->unit_name; ?></span>
                            <?php else:?>
                            <span class="btn btn-sm btn-secondary fs-13 mt--45 rounded-pill"> หมด</span>
                            <?php endif;?>
                        </div>
                        <p class="text-truncate mb-0"><?php echo $model->product->title; ?></p>

                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold text-danger"> <i
                                    class="fa-solid fa-dollar-sign"></i><?php echo number_format($model->unit_price,2); ?></code>


                                <?php
                                                try {
                                                    echo Html::a('เลือก', ['/inventory/main-stock/add-to-cart', 'id' => $model->getLotQty()['id']], ['class' => 'add-cart btn btn-sm btn-primary rounded-pill']);
                                                } catch (Throwable $th) {
                                                    // throw $th;
                                                }
                                ?>
                        </div>
    
                        </a>
                        
                    </div>
                </div>

            </div>
            <?php endforeach?>
        </div>

<?php Pjax::end(); ?>