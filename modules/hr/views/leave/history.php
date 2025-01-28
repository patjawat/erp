<?php
use yii\bootstrap5\Html;
?>

<table class="table table-striped mt-3">
    <thead>
        <tr class="table-secondary">
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th scope="col">เหตุผล</th>
            <th class="text-center" scope="col">เป็นเวลา/วัน</th>
            <th scope="col">จากวันที่</th>
            <th scope="col">ถึงวันที่</th>
            <th scope="col">ปีงบประมาณ</th>
           
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach($model->listHistory() as $item):?>
        <tr class="">
            <td class="text-truncate" style="max-width: 230px;">
                <?=$item->getAvatar(false)['avatar']?>
            </td>
            <td class="text-start"><?php echo $item->data_json['reason']?></td>
            <td class="text-center fw-semibold"><?php echo $item->total_days?></td>
            <td><?=Yii::$app->thaiFormatter->asDate($item->date_start, 'medium')?></td>
            <td><?=Yii::$app->thaiFormatter->asDate($item->date_end, 'medium')?></td>
            <td class="text-center fw-semibold"><?php echo $item->thai_year?></td>
            
        </tr>
        <?php endforeach;?>
    </tbody>
</table>
<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?php
    //  yii\bootstrap5\LinkPager::widget([
    //             'pagination' => $dataProvider->pagination,
    //             'firstPageLabel' => 'หน้าแรก',
    //             'lastPageLabel' => 'หน้าสุดท้าย',
    //             'options' => [
    //                 'listOptions' => 'pagination pagination-sm',
    //                 'class' => 'pagination-sm',
    //             ],
    //         ]); 
            ?>
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