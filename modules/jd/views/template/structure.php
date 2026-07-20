<?php

use yii\helpers\Html;
use yii\helpers\Json;
use app\modules\jd\models\JdTemplateBlock;
use app\modules\jd\services\JdAiDraftService;

/** @var yii\web\View $this */
/** @var app\modules\jd\models\JdTemplate $model */

$this->title = 'จัดทำเนื้อหา: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Template JD', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$sectionHelp = [
    'job_identification' => 'ข้อมูลชื่อ รหัส ระดับ สังกัด ผู้บังคับบัญชา และใบอนุญาต',
    'job_purpose' => 'สรุปเหตุผลของตำแหน่งและผลลัพธ์สำคัญที่องค์กรคาดหวัง',
    'scope_overview' => 'จัดกลุ่มภาพรวมงานเพื่อให้เข้าใจขอบเขตได้อย่างรวดเร็ว',
    'responsibilities' => 'แบ่งหมวดย่อยและระบุหน้าที่สำคัญของแต่ละหมวด',
    'kpi' => 'กำหนดตัวชี้วัด เป้าหมาย และวิธีติดตามผล',
    'qualifications' => 'การศึกษา ใบอนุญาต ประสบการณ์ ความรู้ และทักษะ',
    'competencies' => 'สมรรถนะสำคัญพร้อมพฤติกรรมหรือคำอธิบาย',
    'role_boundary' => 'แยกสิ่งที่ตำแหน่งรับผิดชอบกับสิ่งที่ต้องประสาน',
    'working_conditions' => 'สถานที่ เวลา PPE ความเสี่ยง ความก้าวหน้า และ CPD',
    'approval' => 'ระบุผู้จัดทำ ผู้ตรวจสอบ และผู้อนุมัติเอกสาร',
];
$completedSections = 0;
foreach ($model->blocks as $summaryBlock) {
    $summaryData = $summaryBlock->getData();
    if (trim((string)($summaryData['intro'] ?? '')) !== '' || !empty($summaryData['items'])) {
        $completedSections++;
    }
}
?>
<?php $this->beginBlock('page-title'); ?>
<div>
    <h4 class="fw-semibold mb-1"><?= Html::encode($model->name) ?></h4>
    <div class="text-muted small">
        <?= Html::encode($model->getPositionTitle()) ?> · Revision <?= (int) $model->revision_no ?> · <?= Html::encode($model->lifecycle_status) ?>
    </div>
</div>
<?php $this->endBlock(); ?>

<style>
.jd-editor-page{--ink-1:#1a202c;--ink-2:#4a5568;--ink-3:#718096;--surface:#fff;--surface-2:#f7f9fc;--surface-hover:#f1f5f9;--line:rgba(15,23,42,.08);--line-strong:rgba(15,23,42,.14);--primary:#0d6efd;--primary-soft:rgba(13,110,253,.08);padding:1rem 1rem 5rem}.jd-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;box-shadow:0 1px 2px rgba(15,23,42,.04)}.jd-toolbar__hint{margin-left:auto;color:var(--ink-3);font-size:.78rem}.jd-editor-layout{display:grid;grid-template-columns:250px minmax(0,1fr);gap:1rem;align-items:start}.jd-editor-nav{position:sticky;top:1rem;background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:.5rem;box-shadow:0 1px 2px rgba(15,23,42,.04)}.jd-editor-nav a{display:flex;align-items:flex-start;gap:.55rem;padding:.6rem .65rem;color:var(--ink-2);text-decoration:none;border-radius:8px;font-size:.84rem;line-height:1.35}.jd-editor-nav a:hover,.jd-editor-nav a:focus-visible{background:var(--surface-hover);color:var(--ink-1)}.jd-section{background:var(--surface);border:1px solid var(--line);border-radius:10px;margin-bottom:1rem;box-shadow:0 1px 2px rgba(15,23,42,.04);scroll-margin-top:1rem}.jd-section__head{padding:.9rem 1rem;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;gap:1rem;background:var(--surface-2);border-radius:10px 10px 0 0}.jd-section__body{padding:1rem}.jd-item{border:1px solid var(--line-strong);border-radius:8px;padding:.8rem;margin-bottom:.7rem}.jd-item-grid{display:grid;gap:.7rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}.jd-label{display:block;font-size:.8rem;font-weight:600;color:var(--ink-2);margin-bottom:.35rem}.jd-input{width:100%;min-height:42px;border:1px solid var(--line-strong);border-radius:8px;padding:.55rem .65rem;color:var(--ink-1);background:var(--surface)}.jd-input:focus{border-color:var(--primary);outline:3px solid var(--primary-soft)}.jd-actions{position:sticky;bottom:0;background:rgba(255,255,255,.97);border:1px solid var(--line);border-radius:10px;padding:.75rem 1rem;z-index:10;box-shadow:0 -4px 16px rgba(15,23,42,.06)}@media(max-width:991.98px){.jd-editor-page{padding:.75rem .75rem 5rem}.jd-editor-layout{grid-template-columns:1fr}.jd-editor-nav{position:static;display:flex;overflow:auto}.jd-editor-nav a{min-width:180px}.jd-toolbar__hint{width:100%;margin-left:0}}@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important;transition:none!important}}
</style>

<div class="jd-editor-page">
<div class="jd-toolbar">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>คลัง Template', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::a('<i class="bi bi-pencil me-1"></i>ข้อมูล Template', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::beginForm(['copy', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('<i class="bi bi-copy me-1"></i>คัดลอก', ['class' => 'btn btn-outline-secondary']) ?><?= Html::endForm() ?>
    <?= Html::beginForm(['new-revision', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?><?= Html::submitButton('<i class="bi bi-clock-history me-1"></i>สร้าง Revision', ['class' => 'btn btn-outline-secondary']) ?><?= Html::endForm() ?>
    <?php if (JdAiDraftService::isConfigured()): ?>
        <?= Html::beginForm(['ai-draft', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
        <?= Html::submitButton('<i class="bi bi-stars me-1"></i>สร้างร่างด้วย AI', ['class' => 'btn btn-outline-primary', 'data-confirm' => 'AI จะเติมข้อมูลฉบับร่างลงในทั้ง 10 หมวด และอาจแทนข้อมูลที่กำลังมีอยู่ ต้องการดำเนินการหรือไม่?']) ?>
        <?= Html::endForm() ?>
        <span class="jd-toolbar__hint">AI เป็นผู้ช่วยร่าง ผู้รับผิดชอบต้องตรวจสอบก่อนประกาศใช้</span>
    <?php else: ?>
        <span class="badge text-bg-warning align-self-center">AI ยังไม่ได้ตั้งค่า API key</span>
    <?php endif; ?>
</div>

<div class="d-flex align-items-center justify-content-between gap-3 mb-3 p-3 bg-white border rounded-3">
    <div><strong>ความครบถ้วนของ Template</strong><div class="small text-muted">กรอกแล้ว <?= $completedSections ?> จาก <?= count($model->blocks) ?> หมวด</div></div>
    <div class="progress flex-grow-1" style="max-width:320px;height:8px" role="progressbar" aria-label="ความครบถ้วน" aria-valuenow="<?= $completedSections ?>" aria-valuemin="0" aria-valuemax="<?= count($model->blocks) ?>"><div class="progress-bar" style="width:<?= count($model->blocks) ? round($completedSections * 100 / count($model->blocks)) : 0 ?>%"></div></div>
</div>

<?= Html::beginForm(['structure', 'id' => $model->id], 'post', ['id' => 'jd-structure-form']) ?>
<div class="jd-editor-layout">
    <nav class="jd-editor-nav" aria-label="สารบัญ Template">
        <?php foreach ($model->blocks as $i => $block): ?>
            <?php $navData = $block->getData(); $isComplete = trim((string)($navData['intro'] ?? '')) !== '' || !empty($navData['items']); ?>
            <a href="#block-<?= Html::encode($block->section_code) ?>"><span class="<?= $isComplete ? 'text-success' : 'text-muted' ?>"><i class="bi <?= $isComplete ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i></span><span><?= Html::encode(preg_replace('/^\d+\.\s*/', '', $block->title)) ?></span></a>
        <?php endforeach; ?>
    </nav>
    <main>
        <?php foreach ($model->blocks as $block):
            $columns = JdTemplateBlock::editorColumns($block->block_type);
            $data = $block->getData() + ['intro' => '', 'items' => []];
        ?>
        <section class="jd-section" id="block-<?= Html::encode($block->section_code) ?>" data-block data-code="<?= Html::encode($block->section_code) ?>" data-columns='<?= Html::encode(Json::htmlEncode($columns)) ?>'>
            <header class="jd-section__head">
                <div><h5 class="fw-semibold mb-1"><?= Html::encode($block->title) ?></h5><div class="small text-muted"><?= Html::encode($sectionHelp[$block->section_code] ?? 'เพิ่ม แก้ไข หรือลบรายการตามความเหมาะสมของตำแหน่ง') ?></div></div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-add><i class="bi bi-plus-lg me-1"></i>เพิ่มรายการ</button>
            </header>
            <div class="jd-section__body">
                <label class="jd-label">บทนำหรือหมายเหตุของหมวด</label>
                <textarea class="jd-input mb-3" rows="2" data-intro><?= Html::encode($data['intro'] ?? '') ?></textarea>
                <div data-items>
                    <?php foreach (($data['items'] ?? []) as $item): ?>
                    <div class="jd-item">
                        <div class="jd-item-grid">
                            <?php foreach ($columns as $key => $label): ?>
                            <label><span class="jd-label"><?= Html::encode($label) ?></span><textarea class="jd-input" rows="2" data-key="<?= Html::encode($key) ?>"><?= Html::encode($item[$key] ?? '') ?></textarea></label>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove><i class="bi bi-trash me-1"></i>ลบรายการ</button></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?= Html::hiddenInput('blocks[' . $block->section_code . ']', '', ['data-output' => true]) ?>
            </div>
        </section>
        <?php endforeach; ?>
    </main>
</div>
<div class="jd-actions d-flex justify-content-end gap-2">
    <?= Html::a('กลับคลัง Template', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึกทั้ง Template', ['class' => 'btn btn-primary']) ?>
</div>
<?= Html::endForm() ?>
</div>

<?php
$this->registerJs(<<<'JS'
(function(){
  function itemHtml(columns){
    var fields='';
    Object.keys(columns).forEach(function(key){
      fields+='<label><span class="jd-label">'+escapeHtml(columns[key])+'</span><textarea class="jd-input" rows="2" data-key="'+escapeHtml(key)+'"></textarea></label>';
    });
    return '<div class="jd-item"><div class="jd-item-grid">'+fields+'</div><div class="text-end mt-2"><button type="button" class="btn btn-sm btn-outline-danger" data-remove><i class="bi bi-trash me-1"></i>ลบรายการ</button></div></div>';
  }
  function escapeHtml(v){var d=document.createElement('div');d.textContent=v||'';return d.innerHTML;}
  document.addEventListener('click',function(e){
    var add=e.target.closest('[data-add]');
    if(add){var block=add.closest('[data-block]');var columns=JSON.parse(block.dataset.columns||'{}');block.querySelector('[data-items]').insertAdjacentHTML('beforeend',itemHtml(columns));}
    var remove=e.target.closest('[data-remove]');if(remove){remove.closest('.jd-item').remove();}
  });
  document.getElementById('jd-structure-form').addEventListener('submit',function(){
    document.querySelectorAll('[data-block]').forEach(function(block){
      var payload={intro:block.querySelector('[data-intro]').value.trim(),items:[]};
      block.querySelectorAll('.jd-item').forEach(function(row){var item={};row.querySelectorAll('[data-key]').forEach(function(input){item[input.dataset.key]=input.value.trim();});payload.items.push(item);});
      block.querySelector('[data-output]').value=JSON.stringify(payload);
    });
  });
})();
JS);
?>
