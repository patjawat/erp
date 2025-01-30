<?php
use yii\helpers\Url;
use yii\bootstrap5\Html;
?>


    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th class="text-center" scope="col">วัน</th>
            <th scope="col">จากวันที่</th>
            <th scope="col">ถึงวันที่</th>
            <th scope="col">ปีงบประมาณ</th>
            <th class="text-start" scope="col">หนวยงาน</th>
            <th scope="col">มอบหมาย</th>
            <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
            <th class="text-start">ความคืบหน้า</th>
            <th class="text-start">สถานะ</th>
            <th class="text-center">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach($dataProvider->getModels() as $model):?>
        <tr class="">
            <td class="text-truncate" style="max-width: 230px;">
                <a href="<?php echo Url::to(['/me/leave/view','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'])?>" class="open-modal" data-size="modal-xl">
                <?=$model->getAvatar(false)['avatar']?>
                </a>
            </td>
            <td class="text-center fw-semibold"><?php echo $model->total_days?></td>
            <td><?=Yii::$app->thaiFormatter->asDate($model->date_start, 'medium')?></td>
            <td><?=Yii::$app->thaiFormatter->asDate($model->date_end, 'medium')?></td>
            <td class="text-center fw-semibold"><?php echo $model->thai_year?></td>
            <td class="text-start text-truncate" style="max-width:150px;"><?=$model->getAvatar(false)['department']?>
            </td>
            <td><?php echo $model->leaveWorkSend()['avatar']?></td>
            <td><?php echo $model->stackChecker()?></td>
            <td class="fw-light align-middle text-start" style="width:150px;"><?php echo $model->showStatus();?></td>
            <td class="fw-center align-middle text-start">

                <?php
                        try {
                            echo $model->viewStatus();
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
            </td>

            <td class="text-center">

            <div class="d-flex gap-2 justify-content-center">

    <?php echo Html::a('<i class="fa-solid fa-eye fa-2x"></i>',['/me/leave/view','id' => $model->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>
    <?php if($model->status == 'Allow'):?>
        <i class="fa-solid fa-pencil fa-2x text-secondary"></i>
        <?php else:?>
            <?php echo Html::a('<i class="fa-solid fa-pencil fa-2x text-warning"></i>',['/me/leave/update','id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
    <?php endif?>
    <?php echo Html::a('<i class="fa-solid fa-file-arrow-down fa-2x text-success"></i>',['/'])?>
</div>

                <!-- <div class="dropdown">
                    <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" style="">

                    <?=Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงรายละเอียด',['/me/leave/view','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'dropdown-item open-modalx','data' => ['size' => 'modal-lg']]) ?>
                    <?php if($model->status !== 'Allow'):?>
                            <?php echo Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข',['/me/leave/update','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']]) ?>
                    <?php endif;?>
                <?php echo Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', 
                            [$model->leave_type_id == 'LT4' ? '/hr/document/leavelt4' : '/hr/document/leavelt1', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'], 
                            ['class' => 'dropdown-item', 'target' => '_blank','data-pjax' => '0','disable']) ?>
                    </div>
                </div> -->

               
           
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => [
                    'listOptions' => 'pagination pagination-sm',
                    'class' => 'pagination-sm',
                ],
            ]); ?>
</div>
<?php
$js = <<< JS
    var offcanvasElement = document.getElementById('offcanvasExample');
    var offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
    backdrop: 'static'
    });

JS;
$this->registerJs($js);
?>