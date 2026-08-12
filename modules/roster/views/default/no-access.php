<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees|null $employee */
/** @var bool $hasProfile */

$this->title = 'ยังเข้าใช้ตารางเวรไม่ได้';
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-shield-lock"></i> <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<div class="card border shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-diagram-3 fs-1 text-body-secondary"></i>

        <?php if (!$hasProfile): ?>
            <h6 class="mt-3 mb-2">บัญชีผู้ใช้ของคุณยังไม่ได้ผูกกับข้อมูลพนักงาน</h6>
            <p class="text-body-secondary small mb-0">
                กรุณาติดต่อฝ่ายบุคคลเพื่อผูกบัญชีกับทะเบียนพนักงานก่อน
            </p>
        <?php else: ?>
            <h6 class="mt-3 mb-2">คุณยังไม่ได้ถูกกำหนดเป็นหัวหน้าหน่วยงานในผังองค์กร</h6>
            <p class="text-body-secondary small mb-3">
                สิทธิ์จัดตารางเวรไม่ได้มาจากการตั้ง role แต่มาจากการเป็น<strong>หัวหน้าหน่วยงาน</strong>
                ในผังโครงสร้างองค์กร<br>
                ให้ผู้ดูแลระบบเข้าไปตั้งชื่อคุณเป็นหัวหน้าของหน่วยงานที่ดูแล แล้วเข้าใหม่อีกครั้ง
            </p>
            <div class="alert alert-info border-0 d-inline-block text-start small mb-3">
                <div class="fw-semibold mb-1"><i class="bi bi-info-circle"></i> วิธีตั้งค่า</div>
                เข้า <code>/hr/organization</code> → เลือกหน่วยงาน → กำหนด <strong>หัวหน้าหน่วยงาน (คนที่ 1)</strong>
                เป็นชื่อของคุณ
                <?php if ($employee): ?>
                    <div class="mt-1 text-body-secondary">
                        ชื่อของคุณในระบบ: <strong><?= Html::encode($employee->fullname) ?></strong>
                        (รหัสพนักงาน <?= (int) $employee->id ?>)
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <?= Html::a('<i class="bi bi-diagram-3"></i> เปิดผังโครงสร้างองค์กร', ['/hr/organization'], [
                    'class' => 'btn btn-outline-primary btn-sm',
                ]) ?>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <?= Html::a('<i class="bi bi-arrow-left"></i> กลับหน้าหลัก', ['/me'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
        </div>
    </div>
</div>
