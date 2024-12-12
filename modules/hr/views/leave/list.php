<?php
use yii\bootstrap5\Html;
?>
<table class="table table-striped mt-3">
            <thead>
                <tr class="table-secondary">
                    <th scope="col">ผู้ขออนุมัติการลา</th>
                    <th class="text-center" scope="col">เป็นเวลา/วัน</th>
                    <th scope="col">จากวันที่</th>
                    <th scope="col">ถึงวันที่</th>
                    <th scope="col">ปีงบประมาณ</th>
                    <th class="text-start" scope="col">หนวยงาน</th>
                    <th scope="col">มอบหมาย</th>
                    <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
                    <th class="text-start">ความคืบหน้า</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle">
                <?php foreach($dataProvider->getModels() as $model):?>
                <tr class="">
                    <td class="text-truncate" style="max-width: 250px;">  
                    <?=$model->getAvatar(false)['avatar']?></td>
                    <td class="text-center fw-semibold"><?php echo $model->sum_days?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($model->date_start, 'medium')?></td>
                    <td><?=Yii::$app->thaiFormatter->asDate($model->date_end, 'medium')?></td>
                    <td class="text-center fw-semibold"><?php echo $model->thai_year?></td>
                    <td class="text-start text-truncate" style="max-width:150px;"><?=$model->getAvatar(false)['department']?></td>
                    <td><?php echo $model->leaveWorkSend()?></td>
                    <td><?php echo $model->stackChecker()?></td>
                    <td class="fw-light align-middle text-start" style="width:150px;"><?php echo $model->showStatus();?></td>
                    <td class="fw-center align-middle text-center">
                        <?php
                        try {
                            echo $model->leaveStatus->title;
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
                    </td>

                    <td class="text-center">
                    <?php echo Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', 
                            ['/hr/leave/word', 'id' => $model->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> พิมพ์เอกสาร'], 
                            ['class' => 'btn btn-sm btn-light', 'target' => '_blank']) ?>
                        <?=Html::a('<i class="fa-solid fa-eye me-1"></i>',['/hr/leave/view','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'btn btn-sm btn-primary open-modalx','data' => ['size' => 'modal-lg']]) ?>
                        <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>',['/hr/leave/update','id' => $model->id,'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา'],['class' => 'btn btn-sm btn-warning open-modal','data' => ['size' => 'modal-lg']]) ?>
                        
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

docker run \
    -e ANTHROPIC_API_KEY=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 \
    -v $HOME/.anthropic:/home/computeruse/.anthropic \
    -p 5900:5900 -p 8501:8501 \
    -p 6080:6080 -p 8080:8080 \
    -it ghcr.io/anthropics/anthropic-quickstarts:computer-use-demo-latest
