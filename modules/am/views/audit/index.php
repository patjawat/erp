<?php

use app\components\AppHelper;
use app\modules\am\models\AssetAudit;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\LinkPager;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetAuditSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ตรวจนับพัสดุประจำปี';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="file-check" class="me-2"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'work']) ?>
<?php $this->endBlock(); ?>



    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">ตัวกรองรายการ</h5>
                    <div class="text-muted small">ค้นหาใบตรวจนับจากเลขที่ ปีงบประมาณ สถานะ หรือคำค้นเพิ่มเติม</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    
                </div>
            </div>
            <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'q')->textInput(['placeholder' => 'ค้นหาจากเลขที่/ผู้ตรวจนับ/หมายเหตุ'])->label('คำค้น') ?>
                </div>
                <div class="col-12 col-md-2">
                    <?= $form->field($searchModel, 'thai_year')->textInput(['placeholder' => 'ปีงบประมาณ'])->label('ปีงบประมาณ') ?>
                </div>
                <div class="col-12 col-md-3">
                    <?= $form->field($searchModel, 'status')->dropDownList(AssetAudit::statusList(), ['prompt' => '-- สถานะทั้งหมด --'])->label('สถานะ') ?>
                </div>
               <div class="col-12 col-md-2">
                    <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass me-1"></i>ค้นหา', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="fa-solid fa-eraser me-1"></i>ล้างตัวกรอง', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>


    <div class="card audit-hero border-0 shadow-sm mb-3">
        <div class="card-body p-4 p-lg-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="pe-lg-4">
                    <h1 class="h3 mb-2 fw-semibold text-body">ตรวจนับพัสดุประจำปี</h1>
                    <div class="text-muted mb-0">
                        ตามระเบียบข้อ 209 ตรวจนับพัสดุอย่างน้อยปีละ 1 ครั้ง ก่อนสิ้นปีงบประมาณ
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?= Html::a('KPI Dashboard', ['dashboard'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= Html::a('รายงานครุภัณฑ์คงเหลือ', ['/am/report/register'], ['class' => 'btn btn-outline-success']) ?>
                    <?= Html::a('สร้างใบตรวจนับ', ['create'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>


        <?php $models = $dataProvider->getModels(); ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 pt-3 pb-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" id="sync-selected-audits" class="btn btn-outline-success btn-sm d-none">
                    <i class="fa-solid fa-rotate me-1"></i> บันทึกผลเข้าทะเบียนทรัพย์สินที่เลือก <span class="selected-audit-count">(0)</span>
                </button>
                <button type="button" id="delete-selected-audits" class="btn btn-outline-danger btn-sm d-none">
                    <i class="fa-solid fa-trash me-1"></i> ลบที่เลือก <span class="selected-audit-count">(0)</span>
                </button>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                    เลือกแล้ว <span id="selected-audit-count">0</span> รายการ
                </span>
            </div>
            <div class="text-muted small">ใช้ checkbox เพื่อเลือกหลายรายการแล้วลบพร้อมกันได้</div>
        </div>
        <form id="audit-bulk-form" method="post" action="<?= Html::encode(\yii\helpers\Url::to(['bulk-delete'])) ?>">
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 44px;" class="text-center">
                                <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                                    <input class="form-check-input" type="checkbox" id="audit-select-all">
                                </div>
                            </th>
                            <th style="width: 60px;">#</th>
                            <th>เลขที่ตรวจนับ</th>
                            <th style="width: 110px;" class="text-center">ปีงบประมาณ</th>
                            <th>หน่วยงาน</th>
                            <th style="width: 120px;" class="text-center">วันที่ตรวจนับ</th>
                            <th>ผู้ตรวจนับ</th>
                            <th style="min-width: 240px;">ความคืบหน้า</th>
                            <th style="width: 100px;" class="text-center">คงเหลือ</th>
                    <th style="width: 120px;" class="text-center">อัตรา</th>
                    <th style="width: 130px;" class="text-center">สถานะ</th>
                    <th style="width: 120px;" class="text-center">บันทึกผล</th>
                    <th style="width: 140px;" class="text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($models as $index => $model): ?>
                        <?php
                        $summary = $model->progressSummary;
                        $isScoped = !empty($summary['scopeResolved']);
                        $percent = (float) ($summary['percent'] ?? 0);
                        $checked = (int) ($summary['checked'] ?? 0);
                        $total = (int) ($summary['total'] ?? 0);
                        $remaining = (int) ($summary['remaining'] ?? 0);
                        $barClass = $percent >= 100 ? 'bg-success' : ($percent >= 50 ? 'bg-primary' : 'bg-warning');
                        $barLabelClass = $percent >= 100 ? 'success' : ($percent >= 50 ? 'primary' : 'warning');
                        $statusClass = $model->status === AssetAudit::STATUS_ACTIVE ? 'success' : 'warning';
                        $syncLabel = 'ยังไม่บันทึก';
                        $syncClass = 'warning';
                        if ($model->status === AssetAudit::STATUS_DRAFT) {
                            $syncLabel = 'ฉบับร่าง';
                            $syncClass = 'secondary';
                        } else {
                            $syncComplete = !empty($model->department) && !empty($model->auditItems);
                            if ($syncComplete) {
                                foreach ($model->auditItems as $item) {
                                    if (empty($item->asset_id) || empty($item->asset)) {
                                        $syncComplete = false;
                                        break;
                                    }
                                    if ((int) $item->asset->department !== (int) $model->department) {
                                        $syncComplete = false;
                                        break;
                                    }
                                    if (!empty($item->asset_condition) && (string) $item->asset->asset_condition !== (string) $item->asset_condition) {
                                        $syncComplete = false;
                                        break;
                                    }
                                }
                            }
                            if ($syncComplete) {
                                $syncLabel = 'บันทึกแล้ว';
                                $syncClass = 'success';
                            } else {
                                $syncLabel = 'เสร็จสิ้น';
                                $syncClass = 'warning';
                            }
                        }
                        ?>
                        <tr data-audit-id="<?= (int) $model->id ?>">
                            <td class="text-center">
                                <div class="form-check d-inline-flex align-items-center justify-content-center m-0">
                                    <input class="form-check-input audit-row-select" type="checkbox" name="ids[]" value="<?= (int) $model->id ?>">
                                </div>
                            </td>
                            <td class="text-muted"><?= number_format((int) ($dataProvider->pagination->offset + $index + 1)) ?></td>
                            <td>
                                <?= Html::a(Html::encode($model->audit_no), ['view', 'id' => $model->id], ['class' => 'fw-semibold text-decoration-none']) ?>
                            </td>
                            <td class="text-center"><?= Html::encode($model->thai_year) ?></td>
                            <td><?= Html::encode($model->departmentRef->name ?? '-') ?></td>
                            <td class="text-center"><?= $model->audit_date ? Html::encode(AppHelper::convertToThai($model->audit_date)) : '-' ?></td>
                            <td><?= Html::encode($model->auditorLabel) ?></td>
                            <td>
                                <?php if (!$isScoped): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ยังไม่ระบุหน่วยงาน</span>
                                <?php else: ?>
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                        <div class="small text-body-secondary"><?= number_format($checked) ?> / <?= number_format($total) ?> รายการ</div>
                                        <span class="badge bg-<?= $barLabelClass ?> bg-opacity-10 text-<?= $barLabelClass ?> border border-<?= $barLabelClass ?>-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($percent) ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar <?= $barClass ?>" role="progressbar" style="width: <?= Html::encode($percent) ?>%" aria-valuenow="<?= Html::encode($percent) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?= $isScoped ? Html::encode($remaining) : '-' ?>
                            </td>
                            <td class="text-center">
                                <?= $isScoped
                                    ? '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">' . Html::encode(($model->progressSummary['percent'] ?? 0) . '%') . '</span>'
                                    : '-' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?> border border-<?= $statusClass ?>-subtle rounded-pill fw-medium px-2 py-1">
                                    <?= Html::encode($model->getStatusLabel()) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="audit-sync-badge badge bg-<?= $syncClass ?> bg-opacity-10 text-<?= $syncClass ?> border border-<?= $syncClass ?>-subtle rounded-pill fw-medium px-2 py-1">
                                    <?= Html::encode($syncLabel) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <?= Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-primary',
                                        'title' => 'ดูรายละเอียด',
                                    ]) ?>
                                    <?= Html::a('<i class="fa-solid fa-pen"></i>', ['update', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-warning',
                                        'title' => 'แก้ไข',
                                    ]) ?>
                                    <?= Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-danger',
                                        'data' => [
                                            'confirm' => 'ยืนยันลบรายการนี้หรือไม่?',
                                            'method' => 'post',
                                        ],
                                        'title' => 'ลบ',
                                    ]) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($models)): ?>
                            <tr>
                                <td colspan="13" class="text-center text-muted py-4">ไม่พบข้อมูลใบตรวจนับ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 px-3 py-3 border-top">
            <div class="text-muted small">
                แสดง <?= number_format((int) $dataProvider->getCount()) ?> รายการจากทั้งหมด <?= number_format((int) $dataProvider->getTotalCount()) ?> รายการ
            </div>
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
            ]) ?>
        </div>


        </div>
    </div>
    

<?php
$bulkJs = <<<'JS'
(function() {
    const csrfParam = __CSRF_PARAM__;
    const syncUrl = __SYNC_URL__;
    const $selectAll = $('#audit-select-all');
    const $syncSelected = $('#sync-selected-audits');
    const $deleteSelected = $('#delete-selected-audits');
    const $selectedCount = $('#selected-audit-count');
    const $selectedCountInline = $('.selected-audit-count');

    function syncSelectionUI() {
        const $rows = $('.audit-row-select');
        const selected = $rows.filter(':checked').length;
        const total = $rows.length;

        if ($selectAll.length) {
            $selectAll.prop('checked', total > 0 && selected === total);
            $selectAll.prop('indeterminate', selected > 0 && selected < total);
        }

        if ($deleteSelected.length) {
            $deleteSelected.toggleClass('d-none', selected === 0);
        }

        if ($syncSelected.length) {
            $syncSelected.toggleClass('d-none', selected === 0);
        }

        if ($selectedCount.length) {
            $selectedCount.text(selected);
        }

        if ($selectedCountInline.length) {
            $selectedCountInline.text('(' + selected + ')');
        }
    }

    function setSyncBadge(auditId, label, color) {
        const $row = $('tr[data-audit-id="' + auditId + '"]');
        const $badge = $row.find('.audit-sync-badge').first();
        if (!$badge.length) {
            return;
        }

        const colors = ['success', 'warning', 'secondary', 'danger', 'info', 'primary'];
        $badge.removeClass(function(index, className) {
            return (className.match(/bg-(success|warning|secondary|danger|info|primary)|text-(success|warning|secondary|danger|info|primary)|border-(success|warning|secondary|danger|info|primary)-subtle/g) || []).join(' ');
        });
        $badge.addClass('badge bg-' + color + ' bg-opacity-10 text-' + color + ' border border-' + color + '-subtle rounded-pill fw-medium px-2 py-1');
        $badge.text(label);
    }

    function updateProgress(done, total, text) {
        const $container = Swal.getHtmlContainer ? Swal.getHtmlContainer() : null;
        if (!$container) {
            return;
        }
        const bar = $container.querySelector('#sync-progress-bar');
        const label = $container.querySelector('#sync-progress-text');
        const percent = total > 0 ? Math.round((done / total) * 100) : 0;
        if (bar) {
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', String(percent));
        }
        if (label) {
            label.textContent = text || (done + ' / ' + total + ' รายการ');
        }
    }

    function syncOne(id) {
        return $.ajax({
            url: syncUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                id: id,
                [csrfParam]: $('meta[name="csrf-token"]').attr('content') || ''
            }
        });
    }

    $(document).on('change', '#audit-select-all', function() {
        $('.audit-row-select').prop('checked', $(this).is(':checked'));
        syncSelectionUI();
    });

    $(document).on('change', '.audit-row-select', function() {
        syncSelectionUI();
    });

    $(document).on('click', '#delete-selected-audits', function() {
        const ids = $('.audit-row-select:checked').map(function() {
            return this.value;
        }).get();

        if (!ids.length) {
            return;
        }

        const submitDelete = function() {
            const $form = $('<form>', {
                method: 'post',
                action: $('#audit-bulk-form').attr('action')
            });
            $form.append($('<input>', { type: 'hidden', name: csrfParam, value: $('meta[name="csrf-token"]').attr('content') || '' }));
            ids.forEach(function(id) {
                $form.append($('<input>', { type: 'hidden', name: 'ids[]', value: id }));
            });
            $('body').append($form);
            $form.trigger('submit');
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ลบรายการที่เลือก?',
                text: 'รายการที่เลือกจะถูกลบออกจากระบบ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก',
            }).then(function(result) {
                if (result.isConfirmed) {
                    submitDelete();
                }
            });
        } else if (window.confirm('ลบรายการที่เลือกใช่หรือไม่?')) {
            submitDelete();
        }
    });

    $(document).on('click', '#sync-selected-audits', function() {
        const ids = $('.audit-row-select:checked').map(function() {
            return this.value;
        }).get();

        if (!ids.length) {
            return;
        }

        const doSync = function() {
            let done = 0;
            let success = 0;
            let skipped = 0;

            const finish = function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: success > 0 ? 'success' : 'info',
                        title: 'บันทึกผลเสร็จแล้ว',
                        text: 'สำเร็จ ' + success + ' รายการ' + (skipped > 0 ? ' | ข้าม ' + skipped + ' รายการ' : ''),
                    });
                }
                syncSelectionUI();
            };

            const runNext = function(index) {
                if (index >= ids.length) {
                    finish();
                    return;
                }

                const id = ids[index];
                updateProgress(done, ids.length, 'กำลัง sync รายการที่ ' + (index + 1) + ' จาก ' + ids.length);
                syncOne(id)
                    .done(function(res) {
                        if (res && res.success) {
                            success++;
                            setSyncBadge(id, 'บันทึกแล้ว', 'success');
                        } else {
                            skipped++;
                            if (res && res.message) {
                                setSyncBadge(id, 'บันทึกไม่ได้', 'danger');
                            }
                        }
                    })
                    .fail(function() {
                        skipped++;
                        setSyncBadge(id, 'ผิดพลาด', 'danger');
                    })
                    .always(function() {
                        done++;
                        updateProgress(done, ids.length);
                        runNext(index + 1);
                    });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'กำลังบันทึกผลเข้าทะเบียนทรัพย์สิน',
                    html: '<div class="text-start"><div class="small text-muted mb-2" id="sync-progress-text">0 / ' + ids.length + ' รายการ</div><div class="progress" style="height: 8px;"><div class="progress-bar bg-success" id="sync-progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div></div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function() {
                        runNext(0);
                    }
                });
            } else {
                runNext(0);
            }
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'บันทึกผลเข้าทะเบียนทรัพย์สินที่เลือก?',
                text: 'ระบบจะอัปเดตหน่วยงานและสภาพครุภัณฑ์ตามใบตรวจนับ',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'บันทึกผล',
                cancelButtonText: 'ยกเลิก',
            }).then(function(result) {
                if (result.isConfirmed) {
                    doSync();
                }
            });
        } else if (window.confirm('บันทึกผลรายการที่เลือกใช่หรือไม่?')) {
            doSync();
        }
    });

    syncSelectionUI();
})();
JS;
$bulkJs = str_replace('__CSRF_PARAM__', json_encode(Yii::$app->request->csrfParam), $bulkJs);
$bulkJs = str_replace('__SYNC_URL__', json_encode(Url::to(['sync-audit'])), $bulkJs);
$this->registerJs($bulkJs);
?>
