<?php

use yii\helpers\Html;

/** @var yii\rbac\Role|null $template */
/** @var string $slug */
/** @var string $description */
/** @var string[] $roleNames */
/** @var yii\rbac\Role[] $roles */
/** @var string[] $errors */

$this->title = $template ? 'แก้ไข template กลุ่มสิทธิ' : 'สร้าง template กลุ่มสิทธิ';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<div class="d-flex align-items-center justify-content-between gap-2 mb-3">
    <h1 class="h4 mb-0"><?= Html::encode($this->title) ?></h1>
    <?= Html::a('กลับรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php if ($errors): ?>
    <div class="alert alert-danger" role="alert"><ul class="mb-0">
        <?php foreach ($errors as $error): ?><li><?= Html::encode($error) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<?= Html::beginForm($template ? ['update', 'id' => $template->name] : ['create'], 'post') ?>
<div class="card border mb-3"><div class="card-body">
    <div class="row g-3">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" for="template-slug">รหัส template</label>
            <?= Html::textInput('slug', $slug, ['id' => 'template-slug', 'class' => 'form-control', 'required' => true, 'readonly' => $template !== null, 'placeholder' => 'เช่น purchase_staff']) ?>
            <div class="form-text">ใช้ตัวอักษรอังกฤษตัวเล็ก ตัวเลข ขีดกลาง และขีดล่าง</div>
        </div>
        <div class="col-12 col-md-7">
            <label class="form-label fw-semibold" for="template-description">ชื่อที่แสดง</label>
            <?= Html::textInput('description', $description, ['id' => 'template-description', 'class' => 'form-control', 'required' => true, 'placeholder' => 'เช่น เจ้าหน้าที่พัสดุ']) ?>
        </div>
    </div>
</div></div>

<div class="card border mb-3"><div class="card-body">
    <h2 class="h5 mb-1">บทบาทใน template</h2>
    <p class="text-body-secondary small">เลือกบทบาทที่เจ้าหน้าที่ทุกคนในกลุ่มนี้ควรได้รับ ผู้ใช้ยังเพิ่มสิทธิพิเศษรายคนได้</p>
    <?php if ($template): ?><div class="alert alert-warning py-2">การแก้ไข template จะเปลี่ยนสิทธิของผู้ใช้ทุกคนที่ใช้ template นี้ทันที</div><?php endif; ?>
    <div class="row g-2">
        <?php foreach ($roles as $role): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <label class="d-flex gap-2 align-items-start border rounded-2 p-2 h-100">
                    <?= Html::checkbox('roles[]', in_array($role->name, $roleNames, true), ['value' => $role->name, 'class' => 'form-check-input mt-1']) ?>
                    <span><span class="d-block fw-medium"><?= Html::encode($role->name) ?></span>
                        <?php if ($role->description): ?><span class="d-block small text-body-secondary"><?= Html::encode($role->description) ?></span><?php endif; ?>
                    </span>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</div></div>
<div class="d-flex justify-content-end gap-2 mb-4">
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('บันทึก template', ['class' => 'btn btn-primary']) ?>
</div>
<?= Html::endForm() ?>
