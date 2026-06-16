<?php

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\web\View;
use yii\helpers\Url;

$this->title = 'อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = $this->title;
$totalCount = $dataProvider->getTotalCount();
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary" aria-hidden="true">
            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
            <path d="m9 9.5 2 2 4-4" />
        </svg>
        ทะเบียน<?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/hr/views/development/menu', ['active' => 'index']) ?>
<?php $this->endBlock(); ?>

<!-- Search card -->
<div class="card border-0 shadow-sm rounded-3 mb-3 dev-card">
    <div class="card-header dev-card__header">
        <h6 class="mb-0 d-flex align-items-center gap-2 text-body">
            <i class="fa-solid fa-magnifying-glass text-primary" aria-hidden="true"></i>
            การค้นหา
        </h6>
    </div>
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel, 'type' => 'development']) ?>
    </div>
</div>

<!-- List card -->
<div class="card border-0 shadow-sm rounded-3 dev-card">
    <div class="card-header dev-card__header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 d-flex align-items-center gap-2 text-body">
                <i class="bi bi-ui-checks text-primary" aria-hidden="true"></i>
                ทะเบียน<?= $this->title ?>
                <span class="badge text-bg-primary rounded-pill"><?= number_format($totalCount, 0) ?></span>
            </h6>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-success export-excel d-inline-flex align-items-center gap-1">
                    <i class="fa-solid fa-file-excel" aria-hidden="true"></i>
                    <span>Excel</span>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?= $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]) ?>
    </div>
</div>

<?php
$css = <<<CSS
.dev-card__header {
    background-color: var(--bs-body-bg);
    border-bottom: 1px solid var(--bs-border-color);
    padding: 0.875rem 1.25rem;
}

.dev-card__header h6 {
    font-size: 0.9375rem;
    font-weight: 600;
}

.dev-card .card-body.p-0 .dev-list-wrap {
    border-radius: 0;
}
CSS;
$this->registerCss($css);

$urlExportLeave = Url::to(array_merge(
    ['/hr/development/export-excel'],
    Yii::$app->request->queryParams
));

$js = <<<JS
    \$("body").on("click", ".export-excel", function (e) {
        e.preventDefault();
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
                    didOpen: () => { Swal.showLoading(); }
                });

                \$.ajax({
                    type: "get",
                    url: '$urlExportLeave',
                    method: 'GET',
                    xhrFields: { responseType: 'blob' },
                    success: function (response) {
                        Swal.close();
                        const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'ทะเบียนอบรม/ประชุม/ดูงาน.xlsx';
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
                    error: function (xhr) {
                        Swal.close();
                        \$('#page-content').show();
                        \$('#loader').hide();
                        if (typeof warning === 'function') { warning(xhr.responseText); }
                    }
                });
            }
        });
    });
JS;
$this->registerJs($js, View::POS_END);
?>
