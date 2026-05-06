<?php
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\VendorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ผู้แทนจำหน่าย';
$this->params['breadcrumbs'][] = ['label' => 'บริหารพัสดุ', 'url' => ['/sm']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="fa-solid fa-truck-fast"></i>
    <?= $this->title ?>
  </h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/sm/views/default/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'sm-container']); ?>
<?php $indexQueryString = http_build_query(Yii::$app->request->getQueryParams()); ?>

<div class="container-fluid px-2 px-md-3 pb-3">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
        <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
        <?= Html::encode(Yii::$app->session->getFlash('warning')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
        <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header table-light">
                    <h6 class="mb-0"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
                </div>
                <div class="card-body">
                    <?= $this->render('_search', ['model' => $searchModel]) ?>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header table-light">
                    <div class="d-flex flex-wrap flex-md-nowrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0">
                            <i class="bi bi-ui-checks"></i> รายการผู้แทนจำหน่าย
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= $dataProvider->getTotalCount() ?></span> รายการ
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php
                            $missingCodeCount = (int) ($completeness['missing_code'] ?? 0);
                            $missingCodeFilterActive = (int) ($searchModel->missing_code_only ?? 0) === 1;
                            $missingCodeUrlParams = [];
                            if ($searchModel->q !== null && (string) $searchModel->q !== '') {
                                $missingCodeUrlParams['q'] = $searchModel->q;
                            }
                            if ((int) $searchModel->incomplete_only === 1) {
                                $missingCodeUrlParams['incomplete_only'] = 1;
                            }
                            if (!$missingCodeFilterActive) {
                                $missingCodeUrlParams['missing_code_only'] = 1;
                            }
                            $missingCodeToggleUrl = ['index'];
                            if ($missingCodeUrlParams !== []) {
                                $missingCodeToggleUrl['VendorSearch'] = $missingCodeUrlParams;
                            }
                            ?>
                            <?php if ($missingCodeCount > 0 || $missingCodeFilterActive): ?>
                            <?= Html::a(
                                ($missingCodeFilterActive
                                    ? '<i class="fa-solid fa-list"></i> แสดงรายการทั้งหมด'
                                    : '<i class="fa-solid fa-gear"></i> ตั้งค่ารหัสที่ยังไม่ครบ'
                                        . ' <span class="badge bg-warning text-dark ms-1">' . $missingCodeCount . '</span>'),
                                $missingCodeToggleUrl,
                                [
                                    'class' => 'btn ' . ($missingCodeFilterActive ? 'btn-warning' : 'btn-outline-warning'),
                                    'data-pjax' => 1,
                                    'title' => $missingCodeFilterActive ? 'ยกเลิกกรองรหัส' : 'แสดงเฉพาะรายการที่รหัสว่างหรือเป็น -',
                                ]
                            ) ?>
                            <?php endif; ?>
                            <?php if ($missingCodeCount > 0): ?>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalAssignVendorCodes" title="กำหนดรหัสแบบ V001, V002, … อัตโนมัติ">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> กำหนดรหัสอัตโนมัติ
                            </button>
                            <?php endif; ?>
                            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['create','title' => 'สร้างใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <div class="dropdown">
                                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-file-excel"></i> Excel
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><?= Html::a('<i class="fa-solid fa-table me-2"></i> ดาวน์โหลด Template (Google Sheet)', 'https://docs.google.com/spreadsheets/d/1ofAIy6K0JG1zm2FZO9w42wPx9-2LD1rLxb5NZOt5iL0/edit?usp=sharing', ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener']) ?></li>
                                    <li><?= Html::a('<i class="fa-solid fa-file-excel me-2"></i> ส่งออกข้อมูล Vendor', ['/sm/vendor/export-vendor'], ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener', 'data-pjax' => 0]) ?></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><?= Html::a('<i class="fa-solid fa-file-import me-2"></i> นำเข้าข้อมูล', ['/sm/vendor/import'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($completeness) && is_array($completeness)): ?>
                    <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                        <span class="text-muted small">ข้อมูลไม่ครบ:</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                            รหัส <?= (int) ($completeness['missing_code'] ?? 0) ?>
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                            ชื่อ <?= (int) ($completeness['missing_title'] ?? 0) ?>
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                            เลขผู้เสียภาษี <?= (int) ($completeness['missing_tax_id'] ?? 0) ?>
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                            ผู้ติดต่อ <?= (int) ($completeness['missing_contact_name'] ?? 0) ?>
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                            โทรศัพท์ <?= (int) ($completeness['missing_phone'] ?? 0) ?>
                        </span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                            อีเมล <?= (int) ($completeness['missing_email'] ?? 0) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 48px">#</th>
                                    <th class="fw-semibold">รหัส</th>
                                    <th class="fw-semibold">ชื่อผู้แทนจำหน่าย</th>
                                    <th class="fw-semibold d-none d-lg-table-cell">เลขประจำตัวผู้เสียภาษี</th>
                                    <th class="fw-semibold d-none d-md-table-cell">ผู้ติดต่อ</th>
                                    <th class="fw-semibold">โทรศัพท์</th>
                                    <th class="fw-semibold d-none d-xl-table-cell">อีเมล</th>
                                    <th class="fw-semibold text-center" style="width: 100px">สถานะ</th>
                                    <th class="fw-semibold text-center" style="min-width: 140px">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle table-group-divider" id="pjax-loading">
                                <?php if ($dataProvider->getTotalCount() === 0): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-inbox fa-2x mb-2 opacity-50"></i>
                                        <p class="mb-0">ยังไม่มีข้อมูลผู้แทนจำหน่าย</p>
                                        <small>คลิก «สร้างใหม่» หรือ «นำเข้าข้อมูล» เพื่อเพิ่มรายการ</small>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                                <?php $dj = is_array($item->data_json) ? $item->data_json : []; ?>
                                <?php
                                $vendorCodeNeedsSetup = $item->code === null || $item->code === '' || $item->code === '-';
                                ?>
                                <tr>
                                    <td class="text-center text-muted"><?= ($dataProvider->pagination->offset + 1) + $key ?></td>
                                    <td>
                                        <?php
                                        $codeDisplay = ($item->code === null || $item->code === '') ? '—' : $item->code;
                                        ?>
                                        <code class="<?= $vendorCodeNeedsSetup ? 'text-warning' : 'text-body' ?>"><?= Html::encode($codeDisplay) ?></code>
                                        <?php if ($vendorCodeNeedsSetup): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium ms-1 small">รอรหัส</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= Html::encode($item->title) ?></div>
                                        <?php if (!empty($dj['address'])): ?>
                                        <div class="text-muted small text-truncate d-none d-md-block" style="max-width: 200px" title="<?= Html::encode($dj['address']) ?>"><?= Html::encode($dj['address']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell small"><?= Html::encode($dj['tax_id'] ?? '-') ?></td>
                                    <td class="d-none d-md-table-cell"><?= Html::encode($dj['contact_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($dj['phone'])): ?>
                                        <a href="tel:<?= Html::encode($dj['phone']) ?>" class="text-decoration-none"><?= Html::encode($dj['phone']) ?></a>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-xl-table-cell small">
                                        <?php if (!empty($dj['email'])): ?>
                                        <a href="mailto:<?= Html::encode($dj['email']) ?>" class="text-decoration-none text-break"><?= Html::encode($dj['email']) ?></a>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($item->active)): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">ใช้งาน</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">ไม่ใช้งาน</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex justify-content-end align-items-center gap-1 flex-wrap">
                                        <?php if ($vendorCodeNeedsSetup): ?>
                                        <?= Html::a('<i class="fa-solid fa-gear"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-warning btn-sm open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'ตั้งค่ารหัส']) ?>
                                        <?php endif; ?>
                                        <div class="btn-group btn-group-sm">
                                            <?= Html::a('<i class="fa-solid fa-pen-to-square"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข']) ?>
                                            <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                                <span class="visually-hidden">เมนู</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><?= Html::a('<i class="fa-solid fa-eye me-2"></i> แสดง', ['view', 'id' => $item->id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><?= Html::a('<i class="fa-solid fa-trash me-2"></i> ลบ', ['delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item text-danger']) ?></li>
                                            </ul>
                                        </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted d-flex justify-content-center mt-3">
                        <?= \yii\bootstrap5\LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => ['class' => 'pagination pagination-sm mb-0'],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($missingCodeCount) && (int) $missingCodeCount > 0): ?>
<div class="modal fade" id="modalAssignVendorCodes" tabindex="-1" aria-labelledby="modalAssignVendorCodesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?= Html::beginForm(['/sm/vendor/assign-missing-codes'], 'post', ['data-pjax' => 0]) ?>
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <?= Html::hiddenInput('index_query', $indexQueryString) ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modalAssignVendorCodesLabel"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>กำหนดรหัสอัตโนมัติ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">ระบบจะตั้งรหัสรูปแบบ <code>V001</code>, <code>V002</code>, … ต่อจากเลขสูงสุดที่มีอยู่แล้วในผู้แทนจำหน่ายทั้งระบบ</p>
                <div class="list-group">
                    <label class="list-group-item d-flex gap-2 align-items-start">
                        <input class="form-check-input flex-shrink-0 mt-1" type="radio" name="scope" value="all" checked>
                        <span>
                            <span class="fw-semibold d-block">ทุกรายการในระบบ</span>
                            <span class="small text-muted">ผู้แทนจำหน่ายทุกรายการที่รหัสว่างหรือเป็น &quot;-&quot; (ไม่สนใจการค้นหา/กรองบนหน้าจอ)</span>
                        </span>
                    </label>
                    <label class="list-group-item d-flex gap-2 align-items-start">
                        <input class="form-check-input flex-shrink-0 mt-1" type="radio" name="scope" value="current_filter">
                        <span>
                            <span class="fw-semibold d-block">เฉพาะตามการค้นหาและตัวกรองปัจจุบัน</span>
                            <span class="small text-muted">เฉพาะรายการที่เข้าเงื่อนไขบนหน้านี้ (รวมช่องค้นหา, กรองข้อมูลไม่ครบ, กรองรหัสยังไม่ครบ) และยังขาดรหัสหรือเป็น &quot;-&quot;</span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> ดำเนินการ', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php Pjax::end(); ?>