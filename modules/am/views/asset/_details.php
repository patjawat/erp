<div class="card">
    <div class="card-header border-bottom">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="erp-icon-box bg-primary bg-opacity-10">
                        <i data-lucide="file-text"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">ข้อมูลทั่วไป</h6>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <div class="erp-icon-box bg-primary bg-opacity-10">
                        <i data-lucide="shopping-cart"></i>
                    </div>
                    <h6 class="text-uppercase text-secondary m-0">ข้อมูลการได้มา</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">


        <div class="row g-5">
            <div class="col-12 col-md-6">
                <dl class="row mb-0 text-sm" style="font-size: 0.9rem;">
                    <dt class="col-sm-4 text-secondary fw-normal mb-3">รหัสทรัพย์สิน</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->code ?></dd>

                    <dt class="col-sm-4 text-secondary fw-normal mb-3">ชื่อรายการ</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->asset_name ?></dd>
                    <?php if ($model->license_plate !== null): ?>
                        <dt class="col-sm-4 text-secondary fw-normal mb-3">หมายเลขทะเบียน</dt>
                        <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->license_plate ?></dd>
                    <?php endif; ?>
                    <dt class="col-sm-4 text-secondary fw-normal mb-3">ยี่ห้อ / รุ่น</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->data_json['brand'] ?? 'ไม่ระบุ' ?>/<?= $model->data_json['asset_model'] ?? 'ไม่ระบุ' ?></dd>

                    <dt class="col-sm-4 text-secondary fw-normal mb-3">Serial Number</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->data_json['serial_number'] ?? 'ไม่ระบุ' ?></dd>

                    <dt class="col-sm-4 text-secondary fw-normal mb-3">สถานที่ตั้ง</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= isset($model->data_json['location']) ? $model->data_json['location'] : '-' ?></dd>
                    <dt class="col-sm-4 text-secondary fw-normal mb-3">หน่วยงานรับผิดชอบ</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->departmentName() ?></dd>
                </dl>
            </div>

            <div class="col-12 col-md-6">
                <dl class="row mb-0 text-sm">
                    <dt class="col-sm-4 text-secondary fw-normal mb-3">วันที่รับ</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?></dd>

                    <dt class="col-sm-4 text-secondary fw-normal mb-3">วิธีได้มา</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->method_get ?></dd>

                    <dt class="col-sm-4 text-secondary fw-normal mb-3">ผู้จำหน่าย</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->vendor?->title ?? '-' ?></dd>

                    <dt class="col-sm-4 text-secondary fw-normal mb-3">วิธีการได้มา</dt>
                    <dd class="col-sm-8 text-dark fw-medium mb-3"><?= $model->purchaseName?->title ?? '-' ?></dd>
                </dl>
            </div>
        </div>

        <div class="border-top pt-4 mt-2">
            <h6 class="fw-bold text-dark mb-3">คุณลักษณะเฉพาะ / รายละเอียดเพิ่มเติม</h6>
            <div class="p-3 bg-light rounded text-secondary small">
                <?= $model->data_json['asset_options'] ?? '-' ?>
            </div>
        </div>
    </div>
</div>