<?php
use yii\helpers\Html;
use yii\helpers\Url;

$ticketId = (string) Yii::$app->request->get('id', '');
?>

<div class="container-fluid py-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 border border-primary-subtle bg-primary bg-opacity-10 text-primary p-3">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1">Helpdesk Demo Navigator</h4>
                                <div class="text-muted">ปุ่มนำทางเดโม่สำหรับทดสอบหน้าสำคัญของระบบงานซ่อม</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <?= Html::a(
                                '<i class="fa-solid fa-list-check me-2"></i>รายการงานซ่อม (General)',
                                ['/helpdesk/general/index'],
                                ['class' => 'btn btn-outline-primary']
                            ) ?>
                            <?= Html::a(
                                '<i class="fa-solid fa-circle-plus me-2"></i>แจ้งซ่อม (ฟอร์มใหม่)',
                                ['/helpdesk/service/create-v2'],
                                ['class' => 'btn btn-primary']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">เปิดหน้าดูงานซ่อม (View v2)</div>
                <div class="card-body p-4">
                    <form class="row g-3" action="<?= Html::encode(Url::to(['/helpdesk/service/view-v2'])) ?>" method="get">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Ticket ID</label>
                            <input type="text" name="id" class="form-control" placeholder="เช่น 1050" value="<?= Html::encode($ticketId) ?>">
                            <div class="text-muted small mt-2">ใช้สำหรับทดสอบหน้า `view-v2` ที่ออกแบบใหม่</div>
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa-regular fa-eye me-2"></i>เปิดหน้า View v2
                                </button>
                                <?= Html::a(
                                    '<i class="fa-solid fa-rotate-right me-2"></i>ล้างค่า',
                                    ['/helpdesk/default/index'],
                                    ['class' => 'btn btn-outline-secondary']
                                ) ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">ลิงก์เดโม่เพิ่มเติม</div>
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        <?= Html::a(
                            '<i class="fa-solid fa-clipboard-list me-2"></i>ทะเบียนงานซ่อม (General)',
                            ['/helpdesk/general/index'],
                            ['class' => 'btn btn-outline-primary']
                        ) ?>
                        <?= Html::a(
                            '<i class="fa-solid fa-user-clock me-2"></i>คิวงานช่าง (Technician v2)',
                            ['/helpdesk/service/technician-v2'],
                            ['class' => 'btn btn-outline-primary']
                        ) ?>
                        <?= Html::a(
                            '<i class="fa-solid fa-user-gear me-2"></i>ทีมช่าง (Team)',
                            ['/helpdesk/team/index'],
                            ['class' => 'btn btn-outline-primary']
                        ) ?>
                        <?= Html::a(
                            '<i class="fa-solid fa-gauge-high me-2"></i>แดชบอร์ด (SLA)',
                            ['/helpdesk/dashboard/index'],
                            ['class' => 'btn btn-outline-primary']
                        ) ?>
                    </div>
                    <div class="text-muted small mt-3">
                        หมายเหตุ: ปุ่มชุดนี้เป็นเดโม่สำหรับทดสอบบน localhost เท่านั้น
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
