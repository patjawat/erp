<?php
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
$this->title = 'จองรถ';
?>
<?php // Pjax::begin(['id' => 'leave', 'timeout' => 500000]); ?>
<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-route fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/views/approve/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?> <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
    <p>
        <?php echo html::a('<i class="fa-solid fa-car-side"></i> ขอใช้รถยนต์ทั่วไป',['/me/booking-car/create','title' => '<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลขอใช้รถยนต์'],['class' => 'btn btn-primary rounded-pill shadow open-modal-x','data' => ['size' => 'modal-xl']])?>
        <?php echo html::a('<i class="fa-solid fa-truck-medical"></i> ขอใช้รถพยาบาล',['/me/booking-car/create','title' => '<i class="fa-solid fa-plus"></i> เพิ่มข้อมูลขอใช้รถยนต์'],['class' => 'btn btn-danger rounded-pill shadow open-modal-x','data' => ['size' => 'modal-xl']])?>
    </p>

        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col">ปีงบประมาณ</th>
                    <th scope="col">ผู้ขออนุมัติการลา</th>
                    <th class="text-center" scope="col">วัน</th>
                    <th scope="col">จากวันที่</th>
                    <th scope="col">ถึงวันที่</th>
                    <th class="text-start" scope="col">หนวยงาน</th>
                    <!-- <th scope="col">มอบหมาย</th> -->
                    <th scope="col">ผู้ตรวจสอบและอนุมัติ</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
               
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

<?php // Pjax::end(); ?>