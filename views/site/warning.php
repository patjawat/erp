<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\CheckMaintenanceMode;

$this->title = 'System Configuration Access';
$checkList = CheckMaintenanceMode::checklist();

$message = Yii::$app->session->getFlash('error') ?? 'ไม่สามารถเข้าถึงการตั้งค่าระบบได้';
?>

<div class="settings-warning d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-5">
                <div class="setup-card">
                    <div class="text-center mb-4">
                        <div class="settings-icon mb-2">
                            <i class="fa-solid fa-gears text-secondary"></i>
                        </div>
                        <h4 class="fw-bold text-dark">การตั้งค่าระบบ</h4>
                        <p class="text-muted fs-13">System Configuration Security Check</p>
                    </div>

                    <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis fs-13 mb-4">
                        <i class="fa-solid fa-circle-info me-2"></i> 
                        ระบบตรวจพบว่าคุณยังขาดคุณสมบัติบางประการในการเข้าถึงหน้านี้
                    </div>

                    <div class="setup-list mb-4">
                        <?php foreach ($checkList as $item): ?>
                            <div class="setup-item d-flex align-items-start mb-3">
                                <div class="status-indicator me-3">
                                    <?php if ($item['status']): ?>
                                        <i class="fa-solid fa-circle-check text-success"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-circle-minus text-secondary opacity-50"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="setup-content">
                                    <a href="<?=Url::to($item['url'])?>">
                                        <h6 class="mb-0 fw-bold <?= $item['status'] ? 'text-dark' : 'text-secondary opacity-75' ?>">
                                            <?= $item['label'] ?>
                                        </h6>
                                        <p class="mb-0 fs-12 text-muted"><?= $item['desc'] ?></p>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="my-4 opacity-10">

                    <div class="d-grid gap-2">
                        <?php if (Yii::$app->user->isGuest): ?>
                            <?= Html::a('ลงชื่อเข้าใช้งาน', ['/site/login'], ['class' => 'btn btn-primary btn-setup']) ?>
                        <?php else: ?>
                            <div class="text-center mb-3">
                                <p class="text-danger small mb-0 fw-bold"><?= Html::encode($message) ?></p>
                            </div>
                            <?= Html::a('กลับไปหน้าภาพรวม', ['/site/index'], ['class' => 'btn btn-outline-dark btn-setup']) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="text-center mt-4 text-muted fs-12">
                    Request ID: <span class="text-monospace"><?= uniqid('REQ-') ?></span> | 
                    <?= date('Y-m-d H:i') ?>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f0f2f5; font-family: 'Inter', 'Sarabun', sans-serif; }
    .settings-warning { min-height: 90vh; }
    
    .setup-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .settings-icon {
        font-size: 3rem;
        background: #f8f9fa;
        width: 80px;
        height: 80px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
    }

    .status-indicator { font-size: 1.25rem; margin-top: 2px; }

    .fs-12 { font-size: 12px; }
    .fs-13 { font-size: 13px; }

    .btn-setup {
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.2s;
    }

    .btn-primary.btn-setup {
        background: #1a1c1e;
        border: none;
    }

    .btn-primary.btn-setup:hover {
        background: #333639;
        transform: translateY(-1px);
    }

    /* สไตล์สำหรับ Checklist ที่ยังไม่ผ่าน */
    .setup-item {
        padding: 10px;
        border-radius: 12px;
        transition: background 0.3s;
    }
    .setup-item:hover { background: #f8f9fa; }
</style>