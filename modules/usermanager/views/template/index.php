<?php

use yii\helpers\Html;

/** @var array $templates */
$this->title = 'Template กลุ่มสิทธิ';
?>
<?php $this->beginBlock('page-title'); ?>Template กลุ่มสิทธิ<?php $this->endBlock(); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1">Template กลุ่มสิทธิ</h1>
        <p class="text-body-secondary mb-0">รวมบทบาทที่ใช้ร่วมกัน แล้วเลือกให้เจ้าหน้าที่ได้ในครั้งเดียว</p>
    </div>
    <?= Html::a('<i class="bi bi-plus-lg me-1" aria-hidden="true"></i> สร้าง template', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<?php if (!$templates): ?>
    <div class="card border"><div class="card-body py-4 text-center text-body-secondary">ยังไม่มี template — สร้างชุดสิทธิแรกเพื่อใช้กับเจ้าหน้าที่</div></div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($templates as $entry): ?>
            <?php $role = $entry['role']; ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 mb-1"><?= Html::encode($role->description ?: $role->name) ?></h2>
                        <div class="small text-body-secondary mb-3"><?= Html::encode($role->name) ?> · <?= count($entry['children']) ?> บทบาท</div>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php foreach ($entry['children'] as $child): ?>
                                <span class="badge bg-primary-subtle text-primary-emphasis"><?= Html::encode($child->description ?: $child->name) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?= Html::a('แก้ไขชุดสิทธิ', ['update', 'id' => $role->name], ['class' => 'btn btn-sm btn-outline-primary mt-auto align-self-start']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
