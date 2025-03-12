<?php
use yii\helpers\Html;
$warehouse_id =  Yii::$app->session->get('warehouse_id');
echo $warehouse_id;
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6 class="card-title
            ">รายการเบิกวัสดุคลังหลัก</h6>
            <div class="d-flex gap-2">
                <?php echo Html::a('เบิกคลังหลัก',['/me/store-v2/create-order','title' => 'เบิกวัสดุคลังหลัก'],['class' => 'btn btn-primary open-modal','data' => ['size' => 'modal-lg']])?>


        </div>
        </div>
        <div
            class="table-responsive"
        >
            <table
                class="table table-primary"
            >
                <thead>
                    <tr>
                        <th scope="col">รายการ</th>
                        <th scope="col">คลังหลัก</th>
                        <th scope="col">มูลค่า</th>
                        <th scope="col">สถานะ</th>
                        <th scope="col">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $item):?>
                    <tr class="">
                        <td>
                            <div class="d-flex flex-column">
                                <div><?php echo $item->code?></div>
                                <div><?php echo $item->viewCreatedAt()?></div>
                            </div>
                        </td>
                        <td><?php echo $item->warehouse->warehouse_name?></td>
                        <td><?php echo $item->getTotalOrderPrice()?></td>
                        <td><?php echo $item->viewStatus()?></td>
                        <td>
                            <?php echo Html::a('<i class="fa-solid fa-pen-to-square fs-6"></i>',['/me/store-v2/view','id' => $item->id])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>
