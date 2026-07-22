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


<?php $this->beginBlock('sub-title'); ?>
จำนวนทั้งหมด <span id="showTotalCount"> <?= $dataProvider->getTotalCount() ?>
</span>
รายการ
<?= $notStatus > 0 ? Html::a('| ' . AppHelper::MsgWarning('ไม่ระบุตำแหน่ง') . ' ' . $notStatus . ' คน', ['/hr/employees/', 'not-status' => true]) : '' ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'employees'])
?>
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
                        <a href="#" id="download-button"
                            class="dropdown-item d-flex align-items-center gap-2<?= (int) $dataProvider->getTotalCount() === 0 ? ' disabled' : '' ?>"
                            <?= (int) $dataProvider->getTotalCount() === 0 ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                            <i class="bi bi-file-earmark-excel"></i>ส่งออก Excel</a>
                    </li>
                    <li><?= Html::a(
                            '<i class="fa-solid fa-file-csv me-2"></i>นำเข้าด้วย CSV',
                            ['/hr/employees/import-csv', 'title' => '<i class="fas fa-file-csv text-white"></i> นำเข้าไฟล์ CSV'],
                            ['class' => 'dropdown-item open-modal']
                        ) ?>
                    </li>
                    <li><?= Html::a(
                            '<i class="bi bi-file-earmark-arrow-down me-2"></i> ดาวน์โหลด Template นำเข้า',
                            ['/hr/employees/import-template'],
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

// SweetAlert popup radius มาตรฐาน export (popup 12px / ปุ่ม 8px) ตาม DESIGN.md
$this->registerCss(<<<CSS
.emp-export-swal { border-radius: 12px !important; }
.emp-export-swal__confirm, .emp-export-swal__cancel { border-radius: 8px !important; font-weight: 600; }
.emp-export-swal__item { color: #1a202c; font-size: .95rem; font-weight: 600; }
.emp-export-swal__meta { color: #718096; font-size: .84rem; }
CSS);

$js = <<< JS

        $('#hr-container').on('pjax:success', function() {
             $('body').find('#total-count').text(\$('#totalCount').text());
        });

        // ดึงชื่อไฟล์จาก Content-Disposition (เลือก filename* UTF-8 ก่อน, fallback เป็นชื่อ default)
        function empExportFileName(resp, fallback) {
            var cd = resp.headers.get('Content-Disposition') || resp.headers.get('content-disposition') || '';
            var utf8 = cd.match(/filename\\*=UTF-8''([^;]+)/i);
            if (utf8 && utf8[1]) { return decodeURIComponent(utf8[1].replace(/"/g, '')); }
            var ascii = cd.match(/filename="?([^";]+)"?/i);
            if (ascii && ascii[1]) { return ascii[1]; }
            return fallback;
        }

        $("body").on("click", "#download-button", function (e) {
            e.preventDefault();
            if ($(this).hasClass('disabled')) { return; }

            var form = $('#employees-filter');
            var query = form.length ? form.serialize() : '';
            var reqUrl = '$url' + (query ? ('?' + query) : '');
            var fallbackName = 'ข้อมูลบุคลากร.xlsx';
            var total = ($('#totalCount').text() || '').trim();

            // fallback: ไม่มี SweetAlert → ดาวน์โหลดตรงแบบเดิม (ไม่พัง)
            if (!window.Swal) {
                window.location.href = reqUrl;
                return;
            }

            var noMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var swalBase = {
                customClass: { popup: 'emp-export-swal', confirmButton: 'emp-export-swal__confirm', cancelButton: 'emp-export-swal__cancel' }
            };
            if (noMotion) { swalBase.showClass = { popup: '' }; swalBase.hideClass = { popup: '' }; }
            var mk = function (o) { return Object.assign({}, swalBase, o); };

            // 1) confirm
            Swal.fire(mk({
                icon: 'question',
                iconColor: '#0d6efd',
                title: 'ส่งออกข้อมูลบุคลากรเป็น Excel?',
                html: '<div class="emp-export-swal__item">ข้อมูลบุคลากร</div>'
                    + (total ? '<div class="emp-export-swal__meta">จำนวน ' + total + ' รายการ</div>' : ''),
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-file-earmark-excel me-1"></i>ส่งออก',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                reverseButtons: false,
                focusConfirm: true
            })).then(function (r) {
                if (!r.isConfirmed) { return; }

                // 2) loading
                Swal.fire(mk({
                    title: 'กำลังสร้างไฟล์...',
                    html: '<span class="emp-export-swal__meta">กรุณารอสักครู่</span>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function () { Swal.showLoading(); }
                }));

                fetch(reqUrl, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (resp) {
                        if (!resp.ok) { throw new Error('สร้างไฟล์ไม่สำเร็จ (สถานะ ' + resp.status + ')'); }
                        var fname = empExportFileName(resp, fallbackName);
                        return resp.blob().then(function (blob) { return { blob: blob, fname: fname }; });
                    })
                    .then(function (o) {
                        var objUrl = window.URL.createObjectURL(o.blob);
                        var a = document.createElement('a');
                        a.href = objUrl; a.download = o.fname;
                        document.body.appendChild(a); a.click(); document.body.removeChild(a);
                        setTimeout(function () { window.URL.revokeObjectURL(objUrl); }, 2000);

                        // 3) success (auto-dismiss)
                        Swal.fire(mk({
                            icon: 'success',
                            iconColor: '#198754',
                            title: 'ดาวน์โหลดเรียบร้อย',
                            html: '<span class="emp-export-swal__meta">' + o.fname + '</span>',
                            timer: 1800,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }));
                    })
                    .catch(function (err) {
                        Swal.fire(mk({
                            icon: 'error',
                            title: 'ส่งออกไม่สำเร็จ',
                            text: (err && err.message) || 'ไม่สามารถดาวน์โหลดไฟล์ได้ กรุณาลองใหม่อีกครั้ง'
                        }));
                    });
            });
        });
JS;
$this->registerJS($js, View::POS_END)

?>
<?php Pjax::end(); ?>