<?php

use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\widgets\DataSummaryWidget;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ทะเบียนบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'hr-container', 'enablePushState' => true, 'timeout' => 50000]); ?>

<style>
    #w1-cols-list {
        padding: 10px;
    }
</style>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
            <circle cx="9" cy="7" r="4"></circle>
        </svg>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'employees'])
?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('sub-title'); ?>
จำนวนทั้งหมด <span id="showTotalCount"> <?= $dataProvider->getTotalCount() ?>
</span>
รายการ
<?= $notStatus > 0 ? Html::a('| ' . AppHelper::MsgWarning('ไม่ระบุตำแหน่ง') . ' ' . $notStatus . ' คน', ['/hr/employees/', 'not-status' => true]) : '' ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/hr/views/employees/menu', ['active' => 'employees']) ?>
<?php $this->endBlock(); ?>

<!-- การค้นหา -->
<div class="mb-3">
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
</div>

<!-- รายการบุคลากร -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom rounded-top-4 py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="p-3">
                <h5 class="fw-semibold text-primary mb-1">
                    <i class="fa-solid fa-users me-2"></i>ทะเบียนบุคลากร
                </h5>
                <p class="text-muted small mb-0">
                    จัดการและตรวจสอบข้อมูลบุคลากรทั้งหมด
                    <span class="fw-semibold text-body"><?= number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
                    <?= $notStatus > 0 ? Html::a('· ' . AppHelper::MsgWarning('ไม่ระบุตำแหน่ง') . ' ' . $notStatus . ' คน', ['/hr/employees/', 'not-status' => true], ['class' => 'text-decoration-none']) : '' ?>
                </p>
            </div>

        </div>
        <div class="d-flex gap-2">
            <div class="btn-group" role="group">
                <?= Html::a('<i class="bi bi-list-ul"></i>', ['/setting/set-view', 'view' => 'list'], ['class' => 'btn btn-outline-secondary setview' . (SiteHelper::getDisplay() == 'list' ? ' active' : '')]) ?>
                <?= Html::a('<i class="bi bi-grid"></i>', ['/setting/set-view', 'view' => 'grid'], ['class' => 'btn btn-outline-secondary setview' . (SiteHelper::getDisplay() != 'list' ? ' active' : '')]) ?>
            </div>
            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i><span class="d-none d-sm-inline">เพิ่มบุคลากร</span>', ['/hr/employees/create'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-xl']]) ?>

            <div class="dropdown w-md-auto">
                <button class="btn btn-success dropdown-toggle w-100 w-md-auto" type="button"
                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-file-excel"></i>
                    <span class="d-none d-sm-inline">Excel</span>
                </button>

                <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton1">
                    <li>
                        <a href="#" id="download-button" class="dropdown-item">
                            <i class="fa-solid fa-file-export me-1"></i>ส่งออก</a>
                    </li>
                    <li><?= Html::a(
                            '<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV',
                            ['/hr/employees/import-csv', 'title' => '<i class="fas fa-file-csv text-white"></i> นำเข้าไฟล์ CSV'],
                            ['class' => 'dropdown-item open-modal']
                        ) ?>
                    </li>
                    <li><?= Html::a(
                            '<i class="fa-solid fa-file me-2"></i> ตัวอย่างไฟล์นำเข้า',
                            'https://docs.google.com/spreadsheets/d/1ZlqklxVlRZqxFNRrBK74jHHh8y5tgKGrWgXjHY7h8gw/edit#gid=0',
                            ['class' => 'dropdown-item', 'target' => '_blank']
                        ) ?>
                    </li>
                    </a>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body px-3 p-md-4">
        <?php if (SiteHelper::getDisplay() == 'list'): ?>
            <?= $this->render('display/list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]); ?>
        <?php else: ?>
            <?= $this->render('display/grid', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]); ?>
        <?php endif ?>

    </div>
    <div class="card-footer bg-body border-top py-3 px-4">
        <?php
        echo DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]);
        ?>
    </div>
</div>
<span id="totalCount" class="d-none"><?= $dataProvider->getTotalCount(); ?></span>

<?php
$url = Url::to(['/hr/employees/export-excel']);
$js = <<< JS

        $('#hr-container').on('pjax:success', function() {
             $('body').find('#total-count').text(\$('#totalCount').text());
        });

        $("body").on("click", "#download-button", function (e) {
            var btn = $('#dropdownMenuButton1');
            var originalHtml = btn.html(); // เก็บเนื้อหาปุ่มไว้คืนตอนหลัง

            var form = $('#employees-filter');
            $.ajax({
                url: '$url', // ปรับเป็น URL ของคุณ
                method: 'GET',
                data: form.serialize(),
                xhrFields: {
                    responseType: 'blob' // สำคัญสำหรับ binary data
                },
                beforeSend: function(){
                    // เปลี่ยนปุ่มเป็นสถานะโหลด
                    btn.html('<i class="fa fa-spinner fa-spin me-1"></i> กำลังดาวน์โหลด...');
                },
                success: function(data) {
                    const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'ข้อมูลบุคลากร.xlsx';
                    link.click();
                },
                error: function() {
                    alert('ไม่สามารถดาวน์โหลดไฟล์ได้');
                },
                complete: function() {
                    // คืนปุ่มกลับ
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
JS;
$this->registerJS($js, View::POS_END)

?>
<?php Pjax::end(); ?>