<?php

use yii\web\View;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeaveSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนการลา';
$this->params['breadcrumbs'][] = ['label' => 'ระบบลา', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-check-icon lucide-book-check">
            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
            <path d="m9 9.5 2 2 4-4" />
        </svg>
        ทะเบียนประวัติการลา
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('@app/modules/hr/views/leave/menu',['active' => 'list'])?>
<?php $this->endBlock(); ?>


<style>
.hover-card-under {

    transition: border-color 0.3s ease, transform 0.3s ease;
}

.hover-card-under:hover {
    border: 3px solid transparent !important;
    border-color: #dc3545 !important;
    border-top: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-left: 0 !important;
    transform: scale(1.04);
}
</style>
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
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> ทะเบียนประวัติการลา
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-center gap-2">
              
            </div>
        </div>
    </div>
  <div class="card-body p-0">
        <?php
        echo $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
        ?>

    </div>
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


<?php
$js = <<< JS

    \$('.filter-status').click(function (e) { 
        e.preventDefault();
        var id = \$(this).data('id');
        \$('#leavesearch-status').val(id);
        \$('#w0').submit();
        console.log(id);
    });

        $("body").on("click", ".export-leave", function (e) {
            e.preventDefault();
            let form = $('#search-leave');
            let action = form.attr('action');
            let data = form.serialize();

            Swal.fire({
                title: 'ยืนยันการส่งออกข้อมูล?',
                text: 'คุณต้องการส่งออกข้อมูลหรือไม่',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ส่งออก',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังส่งออกข้อมูล...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        type: "get",
                        url: '/hr/leave/export-leave',
                        data: form.serialize(),
                        xhrFields: {
                            responseType: 'blob' 
                        },
                        success: function (response) {
                            Swal.close();

                            const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'ทะเบียนวันลา.xlsx'; // The default file name
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);

                            Swal.fire({
                                icon: 'success',
                                title: 'ส่งออกสำเร็จ',
                                text: 'ไฟล์ถูกดาวน์โหลดเรียบร้อยแล้ว',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function (xhr, status, error) {
                            Swal.close();
                            $('#page-content').show();
                            $('#loader').hide();
                            warning(xhr.responseText);
                            console.log('Error occurred:', error);
                            console.log('Status:', status);
                            console.log('Response:', xhr.responseText);
                        }
                    });
                }
            });
        });
    JS;
$this->registerJs($js,View::POS_END);
?>

<?php //  Pjax::end(); ?>