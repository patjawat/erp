<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\AppHelper;
$this->title = 'รายงานวันลา';
$this->params['breadcrumbs'][] = ['label' => 'ระบบลา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-chart-simple fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'report'])?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>

<div class="card">
 <div class="card-header bg-primary-gradient text-white">
    <div class="d-flex justify-content-between">

    <h6 class="text-white">
                <i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา
                 <span class="badge text-bg-light"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>

         <span class="btn btn-success shadow export-report"><i class="fa-solid fa-file-export me-1"></i> Excel</span>

    </div>
    </div>



    <div class="card-body">
        <div class="d-flex justify-content-between">
            
            
        </div>

        <table class="table table-bordered table-striped table-hover">
            <thead class="">
                <tr>
                    <th class="fw-semibold text-center">ลำดับ</th>
                    <th class="fw-semibold">ชื่อ นามสกุล</th>
                    <th class="fw-semibold">ตำแหน่ง</th>
                    <th class="fw-semibold text-center">เลขบัตรประชาชน</th>
                    <th class="fw-semibold">ฝ่าย/แผนก</th>
                    <th class="fw-semibold text-center">ประเภท</th>
                    <th class="fw-semibold text-center">ลาป่วย</th>
                    <th class="fw-semibold text-center">ลากิจ</th>
                    <th class="fw-semibold text-center">ลาคลอดบุตร</th>
                    <th class="fw-semibold text-center">ลาพักผ่อน</th>
                    <th class="fw-semibold text-center">รวมได้ลาแล้ว</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">

            <?php
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
            ?>
                <?php  foreach($dataProvider->getModels() as $key => $item):?>
                <tr>
                    <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                    <td><?php echo $item->employee->fullname ?? '-'?></td>
                    <td><?php echo $item->employee->positionName()?></td>
                    <td class="text-center"><?php echo $item->employee->cid?></td>
                    <td><?php echo $item->employee->departmentName()?></td>
                    <td><?php echo $item->employee->positionTypeName()?></td>
                    <td class="text-center fw-bolder">
                        <?= Html::a($item->sum_lt1, ['/hr/leave/leave-history', 'emp_id' => $item->emp_id,'thai_year' => $searchModel->thai_year,'date_start' => $dateStart,'date_end' => $dateEnd,'status' => $searchModel->status,'leave_type_id' => 'LT1'], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>    
                </td>
                    <td class="text-center fw-bolder">
                        <?= Html::a($item->sum_lt3, ['/hr/leave/leave-history', 'emp_id' => $item->emp_id,'thai_year' => $searchModel->thai_year,'date_start' => $dateStart,'date_end' => $dateEnd,'status' => $searchModel->status,'leave_type_id' => 'LT3'], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>    
                </td>
                    <td class="text-center fw-bolder">
                        <?= Html::a($item->sum_lt2, ['/hr/leave/leave-history', 'emp_id' => $item->emp_id,'thai_year' => $searchModel->thai_year,'date_start' => $dateStart,'date_end' => $dateEnd,'status' => $searchModel->status,'leave_type_id' => 'LT2'], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>    
                    </td>
                    <td class="text-center fw-bolder">
                    <?= Html::a($item->sum_lt4, ['/hr/leave/leave-history', 'emp_id' => $item->emp_id,'thai_year' => $searchModel->thai_year,'date_start' => $dateStart,'date_end' => $dateEnd,'status' => $searchModel->status,'leave_type_id' => 'LT4'], ['class' => 'open-modal', 'data' => ['size' => 'modal-xl']]) ?>    
                    <?php  // echo $item->sum_lt4?>
                </td>
                    <td class="text-center fw-bolder">
                        <?php echo ($item->sum_lt1 + $item->sum_lt2 +$item->sum_lt3 +$item->sum_lt4)?></td>
                </tr>
                <?php  endforeach;?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-4">
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
$url = Url::to(['/hr/leave/export']);
$params = Yii::$app->request->queryParams;
$js = <<< JS
   $("body").on("click", ".export-report", function (e) {
    e.preventDefault();

    var form = $('#search-leave').serialize();
    $('#leavesearch-export').val('true');

    Swal.fire({
        title: "ยืนยันการดาวน์โหลด?",
        text: "คุณต้องการดาวน์โหลดรายงานวันลาใช่หรือไม่?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก",
        confirmButtonText: "ใช่, ดาวน์โหลด!"
    }).then((result) => {
        if (result.isConfirmed) {
            
            Swal.fire({
                title: 'กำลังดาวน์โหลด...',
                text: 'โปรดรอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                type: "get",
                url: "/hr/leave/report",
                data: $('#search-leave').serialize(),
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    $('#leavesearch-export').val('');
                    
                    Swal.close(); // ปิด loading

                    const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'รายงานวันลา.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    URL.revokeObjectURL(url);

                    Swal.fire({
                        icon: 'success',
                        title: 'ดาวน์โหลดเสร็จสิ้น',
                        showConfirmButton: false,
                        timer: 1500
                    });
                },
                error: function(xhr, status, error) {
                    $('#leavesearch-export').val('');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถดาวน์โหลดไฟล์ได้'
                    });

                    console.log('Error occurred:', error);
                    console.log('Status:', status);
                    console.log('Response:', xhr.responseText);
                }
            });
        } else {
            $('#leavesearch-export').val('');
        }
    });
});

JS;
$this->registerJS($js, View::POS_END);
?>