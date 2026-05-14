<?php

use app\components\EmployeeHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = 'ทะเบียนหนังสือ';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'หนังสือ', 'url' => ['/me']];

$viewQuery = array_merge(Yii::$app->request->queryParams, []);
unset($viewQuery['kpi']);
$viewListUrl = Url::to(array_merge(['/me/documents/index'], $viewQuery, ['view' => 'list']));
$viewGridUrl = Url::to(array_merge(['/me/documents/index'], $viewQuery, ['view' => 'grid']));
$bulkReadUrl = Url::to(['/me/documents/bulk-read']);
$isTableView = Yii::$app->request->get('view', 'list') !== 'grid';

/** @var app\modules\dms\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider|null $dataProvider เฉพาะโหมดฝังรายการ (isset($list)) */
/** @var array{total:int,read:int,unread:int,saved:int}|null $documentSummaryCounts */
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 w-100">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <span class="text-primary"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
        <?php //  Html::encode($to) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center justify-content-center justify-content-lg-end">
    <?= $this->render('@app/components/ui/btnReturn') ?>
</div>
<?php $this->endBlock(); ?>



<?php if (!empty($documentSummaryCounts) && !isset($list)): ?>
    <?php
    $kpiItems = [
        [
            'label' => 'ทั้งหมด (ฉบับ)',
            'value' => (int) ($documentSummaryCounts['total'] ?? 0),
            'icon' => 'bi-journal-text',
            'color' => 'primary',
        ],
        [
            'label' => 'อ่านแล้ว (ฉบับ)',
            'value' => (int) ($documentSummaryCounts['read'] ?? 0),
            'icon' => 'bi-envelope-open',
            'color' => 'success',
        ],
        [
            'label' => 'ยังไม่ได้อ่าน (ฉบับ)',
            'value' => (int) ($documentSummaryCounts['unread'] ?? 0),
            'icon' => 'bi-envelope',
            'color' => 'warning',
        ],
        [
            'label' => 'บันทึกไว้ (ฉบับ)',
            'value' => (int) ($documentSummaryCounts['saved'] ?? 0),
            'icon' => 'bi-bookmark-star',
            'color' => 'info',
        ],
    ];
    ?>
    <div class="row g-4 mt-1 mb-3">
        <?php foreach ($kpiItems as $item): ?>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="d-flex flex-column gap-3">
                                <span class="fw-bold fs-3"><?= number_format((int) $item['value']) ?></span>
                                <span class="text-<?= Html::encode($item['color']) ?>"><?= Html::encode($item['label']) ?></span>
                            </div>
                            <div class="bg-<?= Html::encode($item['color']) ?> bg-opacity-10 text-<?= Html::encode($item['color']) ?> p-3 rounded-pill">
                                <i class="bi <?= Html::encode($item['icon']) ?> fs-4" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!isset($list)): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <?php
//  $me = EmployeeHelper::GetEmployee();

        // echo $me->id;
        // echo $depId = $me->department ?? null;
            
            ?>

            <?= $this->render('_search', ['model' => $searchModel, 'action' => 'index']) ?>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-body border-bottom py-3 px-3 px-md-4">
                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-lg-between gap-3">
                    <h6 class="mb-0 d-flex align-items-center gap-2 text-body">
                        <span class="bg-primary bg-opacity-10 text-primary rounded-pill p-2 d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-ui-checks" aria-hidden="true"></i>
                        </span>
                        ทะเบียนหนังสือ <?= number_format($dataProvider->getTotalCount(),0)?> รายการ
                    </h6>
                    <div class="d-flex flex-wrap align-items-center gap-2 w-lg-auto justify-content-start justify-content-lg-end ms-lg-auto">
                        <?= Html::a('<i class="fa-solid fa-circle-plus me-1" aria-hidden="true"></i> ลงทะเบียน', ['/dms/documents/create'], [
                            'class' => 'btn btn-sm btn-primary text-white shadow-sm open-modal',
                            'data' => ['size' => 'modal-fullscreen'],
                            'data-pjax' => 0,
                        ]) ?>
                        <div class="btn-group btn-group-sm" role="group" aria-label="มุมมอง">
                            <?= Html::a('<i class="fa-solid fa-table me-1" aria-hidden="true"></i> ตาราง', $viewListUrl, [
                                'class' => 'btn ' . ($isTableView ? 'btn-primary' : 'btn-outline-primary'),
                                'data-pjax' => 0,
                            ]) ?>
                            <?= Html::a('<i class="fa-solid fa-grip me-1" aria-hidden="true"></i> การ์ด', $viewGridUrl, [
                                'class' => 'btn ' . (!$isTableView ? 'btn-primary' : 'btn-outline-primary'),
                                'data-pjax' => 0,
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card-body p-0">
                <?php if (isset($list)): ?>
                    <div class="p-3 border-bottom">
                        <?= Html::a('แสดงทั้งหมด', ['/me/documents'], ['class' => 'btn btn-light rounded-pill', 'data' => ['pjax' => 0]]) ?>
                    </div>
                <?php endif; ?>

                <?php if ($isTableView): ?>
                    <div id="documents-bulk-read-bar" class="d-none px-3 py-3 border-bottom bg-body-tertiary">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="small text-muted fw-semibold">อ่านเอกสารที่เลือก</span>
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2" id="documents-selected-count">
                                เลือก 0 รายการ
                            </span>
                            <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="btn-documents-clear-selection">
                                    ล้างการเลือก
                                </button>
                                <button type="button" class="btn btn-primary btn-sm rounded-pill" id="btn-documents-bulk-read" data-url="<?= Html::encode($bulkReadUrl) ?>">
                                    <i class="fa-regular fa-eye me-1" aria-hidden="true"></i> อ่านทั้งหมดที่เลือก
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($list) && isset($dataProvider)): ?>
                    <?php if ($isTableView): ?>
                        <?= $this->render('_list', [
                            'dataProvider' => $dataProvider,
                            'searchModel' => $searchModel,

                        ]) ?>
                    <?php else: ?>
                        <?= $this->render('_grid', [
                            'dataProvider' => $dataProvider,
                            'unreadOpenDetailIdByDocument' => $unreadOpenDetailIdByDocument ?? [],
                            'unreadOpenDocumentsDetailById' => $unreadOpenDocumentsDetailById ?? [],
                            'readAtByRoutingId' => $readAtByRoutingId ?? [],
                        ]) ?>
                    <?php endif; ?>
                <?php elseif (!isset($list) && isset($dataProvider)): ?>
                    <div id="me-documents-list-wrap">
                        <?php if ($isTableView): ?>
                            <?= $this->render('_list', [
                                'dataProvider' => $dataProvider,
                                'unreadOpenDetailIdByDocument' => $unreadOpenDetailIdByDocument ?? [],
                                'unreadOpenDocumentsDetailById' => $unreadOpenDocumentsDetailById ?? [],
                                'readAtByRoutingId' => $readAtByRoutingId ?? [],
                            ]) ?>
                        <?php else: ?>
                            <?= $this->render('_grid', [
                                'dataProvider' => $dataProvider,
                                'unreadOpenDetailIdByDocument' => $unreadOpenDetailIdByDocument ?? [],
                                'unreadOpenDocumentsDetailById' => $unreadOpenDocumentsDetailById ?? [],
                                'readAtByRoutingId' => $readAtByRoutingId ?? [],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<span id="totalCount" class="d-none"><?= isset($dataProvider) ? (int) $dataProvider->getTotalCount() : 0 ?></span>

<?php
$csrfParamJs = json_encode(Yii::$app->request->csrfParam, JSON_UNESCAPED_SLASHES);
$csrfTokenJs = json_encode(Yii::$app->request->csrfToken, JSON_UNESCAPED_SLASHES);
$js = <<< JS
$(document).ready(function() {
    $('.view-btn').on('click', function() {
        const docId = $(this).data('id');
        const docTitle = $(this).data('title');
        $('#doc-content').text(docTitle + " (ID: " + docId + ")");
        var myModal = new bootstrap.Modal(document.getElementById('view-fullscreen'));
        myModal.show();
    });
});

(function () {
    function getSelectedReadRows() {
        return $('.js-document-select-row:checked');
    }

    window.syncDocumentDetailLinks = function () {
        var \$showReading = $('#documentsearch-show_reading');
        var showReadingEnabled = \$showReading.length && \$showReading.is(':checked');

        $('.js-document-view-link').each(function () {
            try {
                var url = new URL(this.href, window.location.origin);
                if (showReadingEnabled) {
                    url.searchParams.set('show_reading', '1');
                } else {
                    url.searchParams.delete('show_reading');
                }
                this.href = url.toString();
            } catch (e) {
                // Keep the original href if URL parsing fails.
            }
        });
    };

    function updateBulkReadBar() {
        var \$rows = $('.js-document-select-row');
        var \$checked = getSelectedReadRows();
        var count = \$checked.length;
        var \$bar = $('#documents-bulk-read-bar');
        var \$selectAll = $('#chk-select-all-documents');
        var selectableCount = \$rows.filter(':not(:disabled)').length;

        $('#documents-selected-count').text('เลือก ' + count + ' รายการ');

        if (\$bar.length) {
            if (count > 0) {
                \$bar.removeClass('d-none');
            } else {
                \$bar.addClass('d-none');
            }
        }

        if (\$selectAll.length) {
            \$selectAll.prop('checked', selectableCount > 0 && count === selectableCount);
            \$selectAll.prop('indeterminate', count > 0 && count < selectableCount);
        }
    }

    function toggleRowState(\$checkbox) {
        \$checkbox.closest('tr').toggleClass('table-active', \$checkbox.is(':checked'));
    }

    $('body').on('change', '#chk-select-all-documents', function () {
        var isChecked = \$(this).is(':checked');
        $('.js-document-select-row:not(:disabled)').prop('checked', isChecked).each(function () {
            toggleRowState(\$(this));
        });
        updateBulkReadBar();
    });

    $('body').on('change', '.js-document-select-row', function () {
        toggleRowState(\$(this));
        updateBulkReadBar();
    });

    $('body').on('click', '#btn-documents-clear-selection', function () {
        $('.js-document-select-row').prop('checked', false).each(function () {
            toggleRowState(\$(this));
        });
        updateBulkReadBar();
    });

    $('body').on('click', '#btn-documents-bulk-read', function (e) {
        e.preventDefault();

        var ids = getSelectedReadRows().map(function () {
            return \$(this).val();
        }).get();

        if (!ids.length) {
            return;
        }

        var url = \$(this).data('url');
        Swal.fire({
            title: 'ยืนยันอ่านเอกสารที่เลือก?',
            text: 'ระบบจะบันทึกสถานะอ่านให้รายการที่เลือกทั้งหมด',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ใช่, อ่านทั้งหมดที่เลือก',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            Swal.fire({
                title: 'กำลังบันทึก...',
                allowOutsideClick: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });

            var data = { ids: ids };
            data[{$csrfParamJs}] = {$csrfTokenJs};

            $.ajax({
                type: 'post',
                url: url,
                dataType: 'json',
                data: data,
                success: function (res) {
                    if (res && res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกแล้ว',
                            text: res.message || 'อัปเดตสถานะอ่านเรียบร้อย',
                            timer: 1300,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.reload();
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'ไม่สำเร็จ',
                        text: (res && res.message) ? res.message : 'ไม่สามารถบันทึกสถานะอ่านได้'
                    });
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่สำเร็จ',
                        text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
                    });
                }
            });
        });
    });

    window.syncDocumentDetailLinks();
    updateBulkReadBar();
})();
JS;
$this->registerJS($js);
?>
