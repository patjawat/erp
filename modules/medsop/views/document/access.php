<?php
use app\modules\medsop\assets\MedSopAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var app\modules\medsop\services\DocumentAccessService $access
 * @var app\modules\hr\models\Organization[] $ownerOrganizations หน่วยงานที่ผู้ใช้กำหนดสิทธิ์ได้
 * @var int $ownerId หน่วยงานที่กำลังตั้งค่าอยู่
 * @var app\modules\hr\models\Organization[] $organizations หน่วยงานทั้งหมดที่เลือกให้เข้าดูได้
 * @var int[] $selectedViewerIds
 * @var app\modules\medsop\models\OrganizationAccess[] $incomingGrants หน่วยงานอื่นที่เปิดสิทธิ์ให้เรา
 */

MedSopAsset::register($this);
$this->title = 'สิทธิ์เข้าถึงข้ามหน่วยงาน';
$ownerName = '';
foreach ($ownerOrganizations as $organization) {
    if ((int) $organization->id === $ownerId) {
        $ownerName = (string) $organization->name;
    }
}
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>เปิดให้หน่วยงานอื่นเข้าดูเอกสาร SOP/WI ที่เผยแพร่แล้วของหน่วยงานท่าน โดยไม่ต้องกำหนดผู้รับทีละฉบับ<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('_nav', ['access' => $access, 'active' => 'access']) ?><?php $this->endBlock(); ?>

<?php if (count($ownerOrganizations) > 1): ?>
    <section class="card shadow-sm mb-3" aria-labelledby="owner-picker-title">
        <div class="card-header bg-body-tertiary py-3"><h2 id="owner-picker-title" class="h6 fw-semibold mb-0">เลือกหน่วยงานเจ้าของเอกสาร</h2></div>
        <div class="card-body">
            <label class="form-label" for="owner-picker">หน่วยงานที่ต้องการกำหนดสิทธิ์</label>
            <select id="owner-picker" class="form-select" onchange="window.location = this.value">
                <?php foreach ($ownerOrganizations as $organization): ?>
                    <option value="<?= Url::to(['access', 'org' => $organization->id]) ?>" <?= (int) $organization->id === $ownerId ? 'selected' : '' ?>>
                        <?= Html::encode($organization->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </section>
<?php endif; ?>

<?= Html::beginForm(['access'], 'post') ?>
<?= Html::hiddenInput('owner_organization_id', $ownerId) ?>
<section class="card shadow-sm mb-3" aria-labelledby="grant-title">
    <div class="card-header bg-body-tertiary py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h2 id="grant-title" class="h6 fw-semibold mb-0">หน่วยงานที่เข้าดูเอกสารของ<?= Html::encode($ownerName ?: 'หน่วยงานนี้') ?>ได้</h2>
        <span class="badge text-bg-secondary rounded-pill" data-access-count><?= number_format(count($selectedViewerIds)) ?> หน่วยงาน</span>
    </div>
    <div class="card-body">
        <p class="small text-body-secondary">
            ติ๊กเลือกหน่วยงานที่อนุญาตให้เข้าดู หน่วยงานที่ไม่ได้เลือกจะเห็นเฉพาะเอกสารที่ถูกกำหนดเป็นผู้รับรายฉบับเท่านั้น
            สิทธิ์นี้ครอบคลุมเฉพาะเอกสารที่เผยแพร่แล้ว ฉบับร่างและเอกสารที่รออนุมัติยังคงเห็นได้เฉพาะภายในหน่วยงาน
        </p>
        <div class="row g-2">
            <?php foreach ($organizations as $organization): ?>
                <?php if ((int) $organization->id === $ownerId) { continue; } ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="form-check border rounded-3 px-3 py-2 h-100">
                        <?= Html::checkbox('viewer_organization_id[]', in_array((int) $organization->id, $selectedViewerIds, true), [
                            'value' => $organization->id,
                            'class' => 'form-check-input',
                            'id' => 'viewer-' . $organization->id,
                            'data-access-toggle' => true,
                        ]) ?>
                        <label class="form-check-label" for="viewer-<?= (int) $organization->id ?>"><?= Html::encode($organization->name) ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-footer bg-body-tertiary d-flex justify-content-end gap-2 py-3">
        <?= Html::a('ยกเลิก', ['access', 'org' => $ownerId], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::submitButton('<i class="bi bi-save me-1" aria-hidden="true"></i>บันทึกสิทธิ์เข้าถึง', ['class' => 'btn btn-primary rounded-pill px-3']) ?>
    </div>
</section>
<?= Html::endForm() ?>

<section class="card shadow-sm" aria-labelledby="incoming-title">
    <div class="card-header bg-body-tertiary py-3"><h2 id="incoming-title" class="h6 fw-semibold mb-0">หน่วยงานอื่นที่เปิดสิทธิ์ให้หน่วยงานนี้เข้าดู</h2></div>
    <div class="card-body">
        <?php if (!$incomingGrants): ?>
            <p class="text-body-secondary mb-0" role="status">ยังไม่มีหน่วยงานใดเปิดสิทธิ์ให้หน่วยงานนี้เข้าดูเอกสาร</p>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($incomingGrants as $grant): ?>
                    <span class="badge text-bg-light border rounded-pill px-3 py-2">
                        <i class="bi bi-unlock me-1" aria-hidden="true"></i>
                        <?= Html::encode($grant->ownerOrganization ? $grant->ownerOrganization->name : 'หน่วยงาน ' . $grant->owner_organization_id) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <p class="small text-body-secondary mt-3 mb-0">สิทธิ์เหล่านี้หน่วยงานเจ้าของเป็นผู้เปิดให้ ยกเลิกได้จากหน้าของหน่วยงานนั้น</p>
        <?php endif; ?>
    </div>
</section>

<?php $this->registerJs(<<<'JS'
(function () {
    var counter = document.querySelector('[data-access-count]');
    if (!counter) { return; }
    var boxes = Array.prototype.slice.call(document.querySelectorAll('[data-access-toggle]'));
    var refresh = function () {
        var total = boxes.filter(function (box) { return box.checked; }).length;
        counter.textContent = total.toLocaleString() + ' หน่วยงาน';
    };
    boxes.forEach(function (box) { box.addEventListener('change', refresh); });
})();
JS); ?>
