<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;

/** @var int $step */
/** @var array $purchase */
/** @var array $template */
/** @var array $rows */
/** @var int $quantity */
/** @var array $lists */

$this->title = 'รับครุภัณฑ์แบบกลุ่ม';
$this->params['breadcrumbs'][] = ['label' => 'จัดการทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนครุภัณฑ์', 'url' => ['/am/equip/index']];
$this->params['breadcrumbs'][] = $this->title;

$steps = [
    1 => 'ข้อมูลการจัดซื้อ',
    2 => 'ข้อมูลครุภัณฑ์',
    3 => 'จำนวน',
    4 => 'ตรวจสอบและบันทึก',
];
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
    </div>

    <ul class="nav nav-pills nav-fill gap-2 mb-4 mt-2">
        <?php foreach ($steps as $s => $label): ?>
        <li class="nav-item">
            <span class="nav-link <?= $step === $s ? 'active' : ($step > $s ? 'text-success' : '') ?>">
                <span class="badge bg-<?= $step >= $s ? 'primary' : 'secondary' ?> bg-opacity-10 text-<?= $step >= $s ? 'primary' : 'secondary' ?> border border-<?= $step >= $s ? 'primary' : 'secondary' ?>-subtle rounded-pill fw-medium px-2 py-1"><?= $s ?></span>
                <?= Html::encode($label) ?>
            </span>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h6 class="mb-0">ขั้นที่ 1 — ข้อมูลการจัดซื้อ</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= Url::to(['bulk-create']) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="step" value="1">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">วันที่จัดซื้อ/รับเข้า</label>
                                <?= DatepickerThai::widget([
                                    'name' => 'purchase_date',
                                    'value' => AppHelper::DateFormDb($purchase['purchase_date'] ?? date('Y-m-d')),
                                    'options' => ['required' => true],
                                ]) ?>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">เลขที่ใบแจ้งหนี้</label>
                                <input type="text" name="invoice_number" class="form-control" value="<?= Html::encode($purchase['invoice_number'] ?? '') ?>" placeholder="เลขที่ใบแจ้งหนี้">
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">ผู้ขาย/ผู้จำหน่าย</label>
                                <select name="supplier" class="form-select">
                                    <option value="">-- เลือก --</option>
                                    <?php foreach ($lists['vendors'] as $code => $title): ?>
                                        <option value="<?= Html::encode($code) ?>" <?= ($purchase['supplier'] ?? '') == $code ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">ปีงบประมาณ</label>
                                <select name="budget_year" class="form-select">
                                    <option value="">-- เลือก --</option>
                                    <?php foreach ($lists['years'] as $y): ?>
                                        <option value="<?= $y ?>" <?= ($purchase['budget_year'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">ประเภทครุภัณฑ์</label>
                                <select name="asset_type" class="form-select" required>
                                    <option value="">-- เลือก --</option>
                                    <?php foreach ($lists['asset_types'] as $code => $title): ?>
                                        <option value="<?= Html::encode($code) ?>" <?= ($purchase['asset_type'] ?? $purchase['category'] ?? '') == $code ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">สถานที่/คลัง</label>
                                <input type="text" name="warehouse_location" class="form-control" value="<?= Html::encode($purchase['warehouse_location'] ?? '') ?>" placeholder="อาคาร/ห้อง">
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">หน่วยงาน</label>
                                <select name="department" class="form-select">
                                    <option value="">-- เลือก --</option>
                                    <?php foreach ($lists['departments'] as $id => $name): ?>
                                        <option value="<?= (int) $id ?>" <?= ($purchase['department'] ?? '') == $id ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-label">การจัดซื้อ</label>
                                <select name="purchase" class="form-select">
                                    <option value="">-- เลือก --</option>
                                    <?php foreach ($lists['purchases'] as $code => $title): ?>
                                        <option value="<?= Html::encode($code) ?>" <?= ($purchase['purchase'] ?? '') == $code ? 'selected' : '' ?>><?= Html::encode($title) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">ถัดไป — ข้อมูลครุภัณฑ์</button>
                                <?= Html::a('ยกเลิก', ['/am/equip/index'], ['class' => 'btn btn-outline-secondary']) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($step === 2): ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h6 class="mb-0">ขั้นที่ 2 — ข้อมูลครุภัณฑ์ (ใช้กับทุกรายการ)</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= Url::to(['bulk-create']) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="step" value="2">
                        <div class="row g-3">
                            <input type="hidden" name="asset_item_id" id="asset-asset_item_id" value="<?= Html::encode($template['asset_item_id'] ?? '') ?>">
                            <input type="hidden" name="fsn_number" id="asset-fsn_number" value="<?= Html::encode($template['fsn_number'] ?? '') ?>">
                            <div class="col-12">
                                <label class="form-label">ชื่อครุภัณฑ์</label>
                                <div class="input-group">
                                    <input type="text" name="asset_name" id="asset-asset_name" class="form-control" value="<?= Html::encode($template['asset_name'] ?? '') ?>" placeholder="ค้นหาชื่อครุภัณฑ์">
                                    <a href="<?= Url::to(['/am/asset-item/list-item', 'title' => 'แสดงทะเบียนรหัสทรัพย์สิน']) ?>" class="btn btn-secondary open-modal" data-size="modal-xl" title="ค้นหารายการครุภัณฑ์">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </a>
                                </div>
                                <small class="text-muted">กดปุ่มค้นหาเพื่อเลือกรายการจากทะเบียนรหัสทรัพย์สิน (ใช้กับทุกรายการ)</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">ยี่ห้อ</label>
                                <input type="text" name="brand" class="form-control" value="<?= Html::encode($template['brand'] ?? '') ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">รุ่น/โมเดล</label>
                                <input type="text" name="model" class="form-control" value="<?= Html::encode($template['model'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">รายละเอียด/สเปก</label>
                                <textarea name="specification" class="form-control" rows="2"><?= Html::encode($template['specification'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">ราคาต่อหน่วย (บาท)</label>
                                <input type="number" name="purchase_price" class="form-control" step="0.01" min="0" value="<?= Html::encode($template['purchase_price'] ?? '0') ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">อายุการใช้งาน (ปี)</label>
                                <input type="number" name="useful_life" class="form-control" min="0" value="<?= Html::encode($template['useful_life'] ?? '') ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">มูลค่าซาก (บาท)</label>
                                <input type="number" name="residual_value" class="form-control" step="0.01" min="0" value="<?= Html::encode($template['residual_value'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">ถัดไป — กำหนดจำนวน</button>
                                <?= Html::a('ย้อนกลับ', ['bulk-create', 'step' => 1], ['class' => 'btn btn-outline-secondary']) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($step === 3): ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom">
                    <h6 class="mb-0">ขั้นที่ 3 — จำนวนและหมายเลข Serial (ตัวเลือก)</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= Url::to(['bulk-create']) ?>" enctype="multipart/form-data">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="step" value="3">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label">จำนวนรายการ</label>
                                <input type="number" name="quantity" class="form-control" min="1" max="500" value="<?= $quantity ?: '10' ?>" required>
                                <small class="text-muted">สูงสุด 500 รายการต่อครั้ง</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">ตัวเลือก A: วางรายการ Serial (หนึ่งบรรทัดต่อหนึ่งหมายเลข)</label>
                                <textarea name="serial_list" class="form-control" rows="5" placeholder="ABC123&#10;ABC124&#10;ABC125"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">ตัวเลือก B: นำเข้า CSV (คอลัมน์ serial_number, asset_name, remark)</label>
                                <input type="file" name="csv_file" class="form-control" accept=".csv">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">สร้างรายการและตรวจสอบ</button>
                                <?= Html::a('ย้อนกลับ', ['bulk-create', 'step' => 2], ['class' => 'btn btn-outline-secondary']) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($step === 4): ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">ขั้นที่ 4 — ตรวจสอบและบันทึก</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= count($rows) ?> รายการ</span>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= Url::to(['bulk-create']) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="step" value="4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 3rem;">#</th>
                                        <th>หมายเลขครุภัณฑ์</th>
                                        <th>Serial Number</th>
                                        <th>ชื่อครุภัณฑ์</th>
                                        <th>หมายเหตุ</th>
                                        <th class="text-center" style="width: 6rem;">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider align-middle">
                                    <?php foreach ($rows as $i => $row): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <input type="hidden" name="row_code[<?= $i ?>]" value="<?= Html::encode($row['code']) ?>">
                                            <code><?= Html::encode($row['code']) ?></code>
                                        </td>
                                        <td><input type="text" name="row_serial[<?= $i ?>]" class="form-control" value="<?= Html::encode($row['serial_number']) ?>" placeholder="S/N"></td>
                                        <td><input type="text" name="row_name[<?= $i ?>]" class="form-control" value="<?= Html::encode($row['asset_name']) ?>" placeholder="ชื่อ"></td>
                                        <td><input type="text" name="row_remark[<?= $i ?>]" class="form-control" value="<?= Html::encode($row['remark']) ?>" placeholder="หมายเหตุ"></td>
                                        <td class="text-center">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1">Ready</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa-solid fa-check me-1"></i> ยืนยันและบันทึกครุภัณฑ์ <?= count($rows) ?> รายการ
                            </button>
                            <?= Html::a('ย้อนกลับ', ['bulk-create', 'step' => 3], ['class' => 'btn btn-outline-secondary']) ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
