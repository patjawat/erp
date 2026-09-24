<?php

use yii\helpers\Html;
use app\modules\pm\models\Projects;
use app\modules\pm\models\ProjectResponsible;

/** @var yii\web\View $this */
/** @var Projects $model */
/** @var ProjectResponsible[] $responsibles */

// ข้อ 13 ตามแบบฟอร์ม: ลงชื่อผู้เสนอ · ผู้เห็นชอบ · ผู้อนุมัติ
$signers = $model->signers();

// โครงการใหม่ยังไม่มีผู้รับผิดชอบในฐาน — ใช้ผู้รับผิดชอบคนแรกที่เตรียมไว้ในฟอร์มเป็นผู้เสนอ
if ($signers[Projects::SIGNER_PROPOSER]['name'] === '') {
    foreach ($responsibles as $r) {
        if ($r->role === ProjectResponsible::ROLE_OWNER && $r->fullname) {
            $signers[Projects::SIGNER_PROPOSER] = ['name' => (string) $r->fullname, 'position' => (string) $r->position];
            break;
        }
    }
}

$hints = [
    Projects::SIGNER_PROPOSER => 'นักวิชาการ/เจ้าหน้าที่ ของหน่วยงานเจ้าของโครงการ',
    Projects::SIGNER_ENDORSER => 'ค่าเริ่มต้นดึงจากผู้อำนวยการในหน้าตั้งค่าองค์กร · กรณี รพ.สต.เสนอ ให้ สสอ.เห็นชอบ',
    Projects::SIGNER_APPROVER => 'แบบฟอร์มกำหนดให้เว้นชื่อว่างไว้ได้ (กรณีเสนอ นพ.สสจ.อนุมัติ)',
];
?>
<div class="card mb-3">
    <div class="card-header fw-semibold">13. ผู้อนุมัติโครงการ</div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach (Projects::signerRoleList() as $role => $label): ?>
                <div class="col-lg-4">
                    <div class="border rounded p-2 h-100">
                        <div class="fw-semibold mb-2"><?= Html::encode($label) ?></div>
                        <label class="form-label small mb-1">ชื่อ-สกุล</label>
                        <?= Html::textInput("Signers[$role][name]", $signers[$role]['name'], [
                            'class' => 'form-control form-control-sm mb-2',
                            'placeholder' => $role === Projects::SIGNER_APPROVER ? 'เว้นว่างได้' : 'ชื่อ-สกุล',
                        ]) ?>
                        <label class="form-label small mb-1">ตำแหน่ง <span class="text-muted">(ขึ้นบรรทัดใหม่ได้)</span></label>
                        <?= Html::textarea("Signers[$role][position]", $signers[$role]['position'], [
                            'class' => 'form-control form-control-sm', 'rows' => 2,
                        ]) ?>
                        <div class="form-text"><?= Html::encode($hints[$role]) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
