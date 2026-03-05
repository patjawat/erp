<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\jd\models\JdTemplate $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0"><?= Html::encode($model->name) ?></h5>
    <div class="d-flex gap-2">
        <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไข Template: ' . $model->name], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        <?= Html::a('<i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ', ['add-section', 'id' => $model->id, 'title' => 'เพิ่มหัวข้อ: ' . $model->name], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal">ข้อมูล Template</h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-6">
                <span class="text-muted small">ตำแหน่งงาน</span>
                <div><?= Html::encode($model->getPositionTitle()) ?></div>
            </div>
            <div class="col-md-6">
                <span class="text-muted small">สถานะ</span>
                <div>
                    <?= $model->is_active
                        ? Html::tag('span', 'ใช้งาน', ['class' => 'badge text-bg-success'])
                        : Html::tag('span', 'ปิดใช้', ['class' => 'badge text-bg-secondary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-primary text-white py-2 px-3">
        <h6 class="mb-0 small fw-normal">หัวข้อใน Template</h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($model->sections)): ?>
            <div class="p-4 text-center text-muted">ยังไม่มีหัวข้อ — <?= Html::a('เพิ่มหัวข้อ', ['add-section', 'id' => $model->id, 'title' => 'เพิ่มหัวข้อ: ' . $model->name], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">ลำดับ</th>
                            <th>หัวข้อ</th>
                            <th class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider align-middle">
                        <?php foreach ($model->sections as $i => $s): ?>
                        <tr>
                            <td><?= $s->sort_order ?></td>
                            <td>
                                <strong><?= Html::encode($s->title) ?></strong>
                                <?php if (!empty($s->content)): ?>
                                    <div class="small text-muted mt-1"><?= nl2br(Html::encode(mb_substr($s->content, 0, 120))) ?><?= mb_strlen($s->content) > 120 ? '…' : '' ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?= Html::a('<i class="bi bi-pencil"></i>', ['update-section', 'id' => $s->id, 'title' => 'แก้ไขหัวข้อ: ' . $s->title], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข']) ?>
                                <?= Html::a('<i class="bi bi-trash"></i>', ['delete-section', 'id' => $s->id], [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'ลบ',
                                    'data' => ['method' => 'post', 'confirm' => 'ยืนยันลบหัวข้อนี้?'],
                                ]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<p class="mt-3">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับรายการ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</p>
