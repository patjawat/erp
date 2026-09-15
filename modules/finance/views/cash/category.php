<?php

use app\modules\finance\models\FinanceCashCategory;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $type */
/** @var array $tree ผังบัญชี asArray (id, parent_id, level, name, description, sort_order) */

$typeLabel = FinanceCashCategory::typeLabel($type);
$this->title = 'จัดการผังบัญชี';
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รับ–จ่ายเงิน', 'url' => ['/finance/cash']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-diagram-3" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?>ผังหมวดรับ-จ่าย 3 ระดับ (กลุ่ม → หมวด → หัวข้อบัญชี)<?php $this->endBlock();
$this->beginBlock('page-action');
echo $this->render('@app/modules/finance/menu', ['active' => 'payment']);
$this->endBlock();

// สร้าง map ลูกตาม parent
$children = [0 => []];
foreach ($tree as $n) {
    $children[(int) ($n['parent_id'] ?? 0)][] = $n;
    $children[(int) $n['id']] = $children[(int) $n['id']] ?? [];
}
?>

<?= $this->render('_menu', ['active' => 'category']) ?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
    <?php if ($flash = Yii::$app->session->getFlash($key)): ?>
        <div class="alert alert-<?= $cls ?> alert-dismissible fade show"><?= Html::encode($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div class="btn-group">
        <a href="<?= Url::to(['category', 'type' => 'IN']) ?>" class="btn <?= $type === 'IN' ? 'btn-success' : 'btn-outline-success' ?>">รายรับ</a>
        <a href="<?= Url::to(['category', 'type' => 'OUT']) ?>" class="btn <?= $type === 'OUT' ? 'btn-warning' : 'btn-outline-warning' ?>">รายจ่าย</a>
    </div>
    <div class="d-flex gap-2">
        <?php if (empty($tree)): ?>
            <?= Html::beginForm(['seed-chart'], 'post') ?>
            <button type="submit" class="btn btn-primary" onclick="return confirm('ป้อนผังบัญชีมาตรฐาน (รายรับ+รายจ่าย) ทั้งชุด?')">
                <i class="bi bi-magic me-1"></i> ป้อนผังบัญชีมาตรฐาน
            </button>
            <?= Html::endForm() ?>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-primary" data-cat-add data-parent="" data-title="เพิ่มกลุ่ม<?= Html::encode($typeLabel) ?>">
            <i class="bi bi-plus-lg me-1"></i> เพิ่มกลุ่ม
        </button>
    </div>
</div>

<?php if (empty($tree)): ?>
    <div class="card border"><div class="card-body text-center text-body-secondary py-5">
        ยังไม่มีผังบัญชี<?= Html::encode($typeLabel) ?> — กด “ป้อนผังบัญชีมาตรฐาน” เพื่อเริ่ม หรือ “เพิ่มกลุ่ม” เพื่อสร้างเอง
    </div></div>
<?php else: ?>
    <div class="card border"><div class="list-group list-group-flush">
        <?php
        $render = function ($node, int $depth) use (&$render, $children) {
            $id = (int) $node['id'];
            $pad = 12 + $depth * 28;
            $levelBadge = ['group' => 'กลุ่ม', 'category' => 'หมวด', 'account' => 'หัวข้อบัญชี'][$node['level']] ?? $node['level'];
            $badgeCls = ['group' => 'bg-primary-subtle text-primary-emphasis', 'category' => 'bg-info-subtle text-info-emphasis', 'account' => 'bg-secondary-subtle text-secondary-emphasis'][$node['level']] ?? 'bg-light';
            ?>
            <div class="list-group-item d-flex align-items-center gap-2" style="padding-left: <?= $pad ?>px">
                <span class="badge <?= $badgeCls ?>"><?= $levelBadge ?></span>
                <span class="flex-grow-1 <?= $depth === 0 ? 'fw-semibold' : '' ?>"><?= Html::encode($node['name']) ?></span>
                <?php if ($node['level'] !== 'account'): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-cat-add
                            data-parent="<?= $id ?>" data-title="เพิ่มย่อยใน: <?= Html::encode($node['name']) ?>">
                        <i class="bi bi-plus-lg"></i> ย่อย
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-cat-edit
                        data-id="<?= $id ?>" data-parent="<?= (int) ($node['parent_id'] ?? 0) ?>"
                        data-name="<?= Html::encode($node['name']) ?>" data-desc="<?= Html::encode((string) ($node['description'] ?? '')) ?>"
                        data-sort="<?= (int) $node['sort_order'] ?>"><i class="bi bi-pencil"></i></button>
                <?= Html::beginForm(['category-delete'], 'post', ['class' => 'd-inline']) ?>
                <?= Html::hiddenInput('id', $id) ?>
                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบ “<?= Html::encode($node['name']) ?>” ?')"><i class="bi bi-trash"></i></button>
                <?= Html::endForm() ?>
            </div>
            <?php
            foreach ($children[$id] ?? [] as $child) {
                $render($child, $depth + 1);
            }
        };
        foreach ($children[0] as $group) {
            $render($group, 0);
        }
        ?>
    </div></div>
<?php endif; ?>

<!-- Modal เพิ่ม/แก้ไขหมวด -->
<div class="modal fade" id="catModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <?= Html::beginForm(['category-save'], 'post', ['class' => 'modal-content']) ?>
        <div class="modal-header">
            <h5 class="modal-title" id="catModalTitle">หมวด</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <?= Html::hiddenInput('id', '', ['id' => 'cm-id']) ?>
            <?= Html::hiddenInput('parent_id', '', ['id' => 'cm-parent']) ?>
            <?= Html::hiddenInput('txn_type', $type) ?>
            <div class="mb-3">
                <label class="form-label" for="cm-name">ชื่อ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="cm-name" name="name" required maxlength="500">
            </div>
            <div class="mb-3">
                <label class="form-label" for="cm-desc">คำอธิบาย</label>
                <textarea class="form-control" id="cm-desc" name="description" rows="2"></textarea>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label" for="cm-sort">ลำดับ</label>
                    <input type="number" class="form-control" id="cm-sort" name="sort_order" value="0">
                </div>
                <div class="col-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="cm-active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="cm-active">ใช้งาน</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="submit" class="btn btn-primary">บันทึก</button>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    const modalEl = document.getElementById('catModal');
    const modal = new bootstrap.Modal(modalEl);
    const set = (id, v) => { document.getElementById(id).value = v; };

    document.querySelectorAll('[data-cat-add]').forEach(btn => btn.addEventListener('click', function () {
        document.getElementById('catModalTitle').textContent = this.dataset.title || 'เพิ่มหมวด';
        set('cm-id', ''); set('cm-parent', this.dataset.parent || '');
        set('cm-name', ''); set('cm-desc', ''); set('cm-sort', '0');
        document.getElementById('cm-active').checked = true;
        modal.show();
    }));
    document.querySelectorAll('[data-cat-edit]').forEach(btn => btn.addEventListener('click', function () {
        document.getElementById('catModalTitle').textContent = 'แก้ไข: ' + this.dataset.name;
        set('cm-id', this.dataset.id); set('cm-parent', this.dataset.parent || '');
        set('cm-name', this.dataset.name || ''); set('cm-desc', this.dataset.desc || '');
        set('cm-sort', this.dataset.sort || '0');
        document.getElementById('cm-active').checked = true;
        modal.show();
    }));
})();
JS);
?>
