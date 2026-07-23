<?php
use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $q */

$this->title = 'ประเภทวัสดุ';
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title');
?>
<span class="d-inline-flex align-items-center gap-2 fw-semibold text-body">
    <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
    <span><?= Html::encode($this->title) ?></span>
</span>
<?php
$this->endBlock();

$this->beginBlock('sub-title');
?>
จัดการรหัสและชื่อประเภทวัสดุสำหรับงานคลัง
<?php
$this->endBlock();

$q = isset($q) ? (string) $q : '';
$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$offset = $pagination === false ? 0 : $pagination->getOffset();
$exportRoute = $q === '' ? ['export'] : ['export', 'q' => $q];
$flashTypes = [
    'success' => 'bi-check2-circle',
    'warning' => 'bi-exclamation-triangle',
    'danger' => 'bi-x-circle',
];
?>

<div class="container-fluid px-0">
    <?php foreach ($flashTypes as $type => $icon): ?>
        <?php if (Yii::$app->session->hasFlash($type)): ?>
            <div class="alert alert-<?= Html::encode($type) ?> d-flex gap-2 align-items-start" role="alert">
                <i class="bi <?= Html::encode($icon) ?> mt-1"></i>
                <div><?= nl2br(Html::encode(Yii::$app->session->getFlash($type))) ?></div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-2 align-items-center mb-3">
        <div class="col-12 col-lg">
            <?= Html::beginForm(['index'], 'get', ['class' => 'mb-0', 'role' => 'search']) ?>
                <div class="input-group">
                    <span class="input-group-text bg-body text-secondary"><i class="bi bi-search"></i></span>
                    <?= Html::textInput('q', $q, [
                        'class' => 'form-control',
                        'placeholder' => 'ค้นหารหัส ชื่อ หรือรายละเอียด',
                        'aria-label' => 'ค้นหาประเภทวัสดุ',
                    ]) ?>
                    <button class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" type="submit">
                        <i class="bi bi-search"></i>
                        <span>ค้นหา</span>
                    </button>
                    <?php if ($q !== ''): ?>
                        <?= Html::a('<i class="bi bi-x-lg"></i>', ['index'], [
                            'class' => 'btn btn-outline-secondary',
                            'title' => 'ล้างคำค้น',
                            'aria-label' => 'ล้างคำค้น',
                        ]) ?>
                    <?php endif; ?>
                </div>
            <?= Html::endForm() ?>
        </div>

        <div class="col-12 col-lg-auto">
            <div class="d-grid d-sm-flex gap-2 justify-content-lg-end">
                <?= Html::a('<i class="bi bi-plus-circle"></i> <span>เพิ่มประเภท</span>', ['create'], [
                    'class' => 'btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 open-modal',
                    'data' => ['pjax' => 0, 'size' => 'modal-lg'],
                ]) ?>

                <div class="dropdown d-grid">
                    <button class="btn btn-outline-success dropdown-toggle d-inline-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                        <span>Excel</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm p-2">
                        <?= Html::a('<i class="bi bi-file-earmark-spreadsheet"></i> <span>ดาวน์โหลด Template</span>', ['template'], [
                            'class' => 'dropdown-item d-flex align-items-center gap-2 rounded',
                        ]) ?>
                        <?= Html::a('<i class="bi bi-download"></i> <span>ส่งออก Excel</span>', $exportRoute, [
                            'class' => 'dropdown-item d-flex align-items-center gap-2 rounded',
                        ]) ?>

                        <div class="dropdown-divider"></div>

                        <?= Html::beginForm(['import'], 'post', [
                            'class' => 'px-2 py-2',
                            'enctype' => 'multipart/form-data',
                        ]) ?>
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                            <div class="mb-2">
                                <label class="form-label small text-secondary mb-1" for="excel-import-file">นำเข้า Excel</label>
                                <input id="excel-import-file" class="form-control form-control-sm" type="file" name="excel_file" accept=".xlsx,.xls,.csv" required aria-label="ไฟล์ Excel สำหรับนำเข้า">
                            </div>
                            <button class="btn btn-success btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" type="submit">
                                <i class="bi bi-upload"></i>
                                <span>นำเข้าไฟล์</span>
                            </button>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($models)): ?>
                <div class="text-center text-secondary py-5 px-3">
                    <i class="bi bi-inbox d-block fs-4 mb-2"></i>
                    <div class="fw-semibold">ไม่พบประเภทวัสดุ</div>
                    <div class="small">ลองปรับคำค้นหา หรือเพิ่มประเภทวัสดุใหม่</div>
                </div>
            <?php else: ?>
                <div class="table-responsive d-none d-lg-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary small">
                            <tr>
                                <th scope="col" class="text-nowrap">#</th>
                                <th scope="col" class="text-nowrap">รหัส</th>
                                <th scope="col">ชื่อประเภท</th>
                                <th scope="col">รายละเอียด</th>
                                <th scope="col" class="text-center text-nowrap">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($models as $index => $model): ?>
                                <?php $description = trim((string) $model->description); ?>
                                <tr>
                                    <td class="text-secondary text-nowrap"><?= $offset + $index + 1 ?></td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-light text-dark border fw-semibold font-monospace px-2 py-2"><?= Html::encode($model->code) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-body"><?= Html::encode($model->title) ?></div>
                                    </td>
                                    <td>
                                        <div class="text-secondary small"><?= $description !== '' ? Html::encode($description) : '-' ?></div>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <?= Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-info open-modal',
                                            'title' => 'แสดง',
                                            'aria-label' => 'แสดง ' . $model->title,
                                            'data' => ['size' => 'modal-lg'],
                                        ]) ?>
                                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-warning open-modal',
                                            'title' => 'แก้ไข',
                                            'aria-label' => 'แก้ไข ' . $model->title,
                                            'data' => ['size' => 'modal-lg'],
                                        ]) ?>
                                        <?= Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => 'ลบ',
                                            'aria-label' => 'ลบ ' . $model->title,
                                            'data' => [
                                                'confirm' => 'ยืนยันลบประเภทวัสดุนี้?',
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <ul class="list-unstyled d-lg-none m-0 p-3">
                    <?php foreach ($models as $index => $model): ?>
                        <?php $description = trim((string) $model->description); ?>
                        <li class="border rounded bg-body p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span class="badge bg-light text-dark border fw-semibold font-monospace px-2 py-2"><?= Html::encode($model->code) ?></span>
                                <span class="text-secondary small text-nowrap">#<?= $offset + $index + 1 ?></span>
                            </div>
                            <div class="fw-semibold text-body mt-2 mb-1"><?= Html::encode($model->title) ?></div>
                            <div class="text-secondary small"><?= $description !== '' ? Html::encode($description) : '-' ?></div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <?= Html::a('<i class="fa-solid fa-eye"></i>', ['view', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-info open-modal',
                                    'title' => 'แสดง',
                                    'aria-label' => 'แสดง ' . $model->title,
                                    'data' => ['size' => 'modal-lg'],
                                ]) ?>
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-warning open-modal',
                                    'title' => 'แก้ไข',
                                    'aria-label' => 'แก้ไข ' . $model->title,
                                    'data' => ['size' => 'modal-lg'],
                                ]) ?>
                                <?= Html::a('<i class="fa-solid fa-trash"></i>', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'ลบ',
                                    'aria-label' => 'ลบ ' . $model->title,
                                    'data' => [
                                        'confirm' => 'ยืนยันลบประเภทวัสดุนี้?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!empty($models)): ?>
            <div class="card-footer bg-body border-top py-3 px-3 px-md-4">
                <?= DataSummaryWidget::widget([
                    'dataProvider' => $dataProvider,
                    'pagerOptions' => [],
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
