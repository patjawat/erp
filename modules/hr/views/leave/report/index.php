<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
$this->title = 'รายงานระบบลา';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-chart-simple"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/leave/menu') ?>
<?php $this->endBlock(); ?>
<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
        </div>
        <div class="d-flex justify-content-between  align-top align-items-center mt-2">
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            <span class="btn btn-success rounded-pill shadow export-leave"><i class="fa-regular fa-file-excel"></i> ส่งออก</span>
            <?php // Html::a('<i class="fa-regular fa-file-excel"></i> ส่งออก', ['/hr/leave/export-leave', 'title' => '<i class="fa-solid fa-calendar-plus"></i> บันทึกขออนุมัติการลา'], ['class' => 'btn btn-success rounded-pill shadow export-leave', 'data' => ['size' => 'modal-lg']]) ?>
        </div>
<table class="table table-bordered">
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>ชื่อ นามสกุล</th>
                <th>ตำแหน่ง</th>
                <th>ฝ่าย/แผนก</th>
                <th class="text-center">ลาป่วย</th>
                <th class="text-center">ลากิจ</th>
                <th class="text-center">ลาคลอดบุตร</th>
                <th class="text-center">ลาพักผ่อน</th>
                <th class="text-center">รวมได้ลาแล้ว</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dataProvider->getModels() as $item):?>
            <tr>
                <td>1</td>
                <td><?php echo $item->employee->fullname?></td>
                <td><?php echo $item->employee->positionName()?></td>
                <td><?php echo $item->employee->departmentName()?></td>
                <td class="text-center fw-bolder"><?php echo $item->sum_lt1?></td>
                <td class="text-center fw-bolder"><?php echo $item->sum_lt2?></td>
                <td class="text-center fw-bolder"><?php echo $item->sum_lt3?></td>
                <td class="text-center fw-bolder"><?php echo $item->sum_lt4?></td>
                <td class="text-center fw-bolder"><?php echo ($item->sum_lt1 + $item->sum_lt2 +$item->sum_lt3 +$item->sum_lt4)?></td>
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
    </div>
</div>


<?php
$url = Url::to(['/hr/leave/report/export']);
$params = Yii::$app->request->queryParams;
$js = <<< JS
    $("body").on("click", ".export-leave", function (e) {
           e.preventDefault();
           $('#leavesearch-data_json-export').val('true')
            $.ajax({
                type: "get",
                url: $('#w0').attr('action'),
                data:$('#w0').serialize(),
                xhrFields: {
                    responseType: 'blob' // Important for handling binary data
                },
                beforeSend: function(){
                    $('#page-content').hide()
                    $('#loader').show()
                },
                success: function(response) {
                    $('#page-content').show()
                    $('#loader').hide()
                    $('#leavesearch-data_json-export').val('')
                    const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'รายงาน.xlsx'; // The default file name
                    document.body.appendChild(a);
                    a.click();
                    URL.revokeObjectURL(url);
                },
                error: function(xhr, status, error) {
                    $('#page-content').show()
                    $('#loader').hide()
                    warning(xhr.responseText)
                    console.log('Error occurred:', error);
                    console.log('Status:', status);
                    console.log('Response:', error);
                }
            });
        });



    JS;
$this->registerJS($js, View::POS_END);
?>

