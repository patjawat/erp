<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\booking\models\Booking;
/** @var yii\web\View $this */
if($searchModel->car_type == 'general'){
    $this->title = ' ทะเบียนขอใช้รถทั่วไป'; 
}

if($searchModel->car_type == 'ambulance'){
    $this->title = 'ทะเบียนขอใช้รถพยาบาล'; 
}
?>


<?php $this->beginBlock('icon'); ?>
<?php if($searchModel->car_type == 'general'):?>
    <i class="fa-solid fa-car fs-1"></i>
    <?php endif;?>
    <?php if($searchModel->car_type == 'ambulance'):?>
        <i class="fa-solid fa-truck-medical fs-1"></i>
    <?php endif;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
ระบบยานพาหนะ
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>


<?php  echo $this->render('menu') ?>

    

    <!-- Admin View (Hidden by default) -->
    <div id="official-view" class="mt-3">
            <!-- <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>จัดการขอใช้รถยนต์</h3>
                <div>
                    <button class="btn btn-outline-primary me-2">
                        <i class="bi bi-calendar-week me-1"></i>ปฏิทินการใช้รถ
                    </button>
                    <button class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>ส่งออกรายงาน
                    </button>
                </div>
            </div> -->

            <!-- Vehicle Calendar View -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-2">
                    <h5 class="card-title">ปฏิทินการใช้รถ</h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary disabled">มกราคม 2568</button>
                        <button class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%">รถ / วันที่</th>
                                    <th>1 ม.ค. 68</th>
                                    <th>2 ม.ค. 68</th>
                                    <th>3 ม.ค. 68</th>
                                    <th>4 ม.ค. 68</th>
                                    <th>5 ม.ค. 68</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-light">รถพยาบาล กข-1234</td>
                                    <td colspan="3" class="bg-warning bg-opacity-25">สมชาย (รพ.ศิริราช)</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="table-light">รถตู้ ขค-5678</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="bg-success bg-opacity-25">วิชัย (สสจ.)</td>
                                </tr>
                                <tr>
                                    <td class="table-light">รถกระบะ งจ-9012</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="bg-primary bg-opacity-25">สมศรี (ประชุม)</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            
        </div>
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
        

<?php
$js = <<< JS

$(document).ready(function () {
    // Toggle between user and admin views
    $('#user-view-btn').click(function () {
        $('#user-view').show();
        $('#admin-view').hide();
        $('#user-view-btn').addClass('active');
        $('#admin-view-btn').removeClass('active');
    });

    $('#admin-view-btn').click(function () {
        $('#user-view').hide();
        $('#official-view').show();
        $('#user-view-btn').removeClass('active');
        $('#admin-view-btn').addClass('active');
    });

    // Toggle vehicle options based on selection
    $('#vehicleType').change(function () {
        const vehicleType = $(this).val();
        $('#officialCarOptions, #ambulanceOptions').hide();

        if (vehicleType === 'ambulance') {
            $('#ambulanceOptions').show();
        } else if (vehicleType === 'official') {
            $('#officialCarOptions').show();
        }
    });
});

JS;
$this->registerJs($js);
?>