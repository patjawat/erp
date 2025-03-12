<?php
use yii\helpers\Html;

$title = '';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-clipboard-user fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6 class="card-title
            ">รายการเบิกวัสดุคลังหลัก</h6>
            <div class="d-flex gap-2">
                <?php echo Html::a('<i class="fa-solid fa-plus"></i> สร้างใบเบิก',['/me/store-v2/create-order','title' => 'เบิกวัสดุคลังหลัก'],['class' => 'btn btn-primary open-modal rounded-pill','data' => ['size' => 'modal-lg']])?>


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
                        <th class="text-center">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $item):?>
                    <tr class="">
                        <td>
                            <div class="d-flex flex-column">
                                <div class="fw-semibold"><?php echo $item->code?></div>
                                <div><?php echo $item->viewCreatedAt()?></div>
                            </div>
                        </td>
                        <td><?php echo $item->warehouse->warehouse_name?></td>
                        <td><?php echo $item->getTotalOrderPrice()?></td>
                        <td><?php echo $item->viewStatus()?></td>
                        <td class="text-center">
                            <?php echo Html::a('<i class="fa-solid fa-pen-to-square fs-3"></i>',['/me/store-v2/view','id' => $item->id])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>
