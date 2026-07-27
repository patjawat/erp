<?php

use app\modules\jd\models\JdTemplateBlock;
use app\modules\jd\components\RichText;
use yii\helpers\Html;

/** @var app\modules\jd\models\JdEmployee $jd */
$editable = $editable ?? false;
$approvalRows = $approvalRows ?? [];
$approvalRowsByEmployee = [];
foreach ($approvalRows as $approvalRow) {
    $approvalData = (array) $approvalRow->data_json;
    $approvalRowsByEmployee[(int) $approvalRow->emp_id . '|' . ($approvalData['role'] ?? '')] = $approvalRow;
}
?>
<div class="jd-document">
    <?php foreach ($jd->sections as $section): ?>
        <?php $data = $section->getData() + ['intro' => '', 'items' => []]; ?>
        <section class="jd-document__section" id="jd-section-<?= Html::encode($section->section_code ?: $section->id) ?>">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <h6 class="jd-document__title mb-0"><?= Html::encode($section->title) ?></h6>
                <?php if ($editable): ?>
                    <?= Html::a('<i class="bi bi-pencil me-1"></i>บันทึกรายละเอียด', ['update-section', 'id' => $section->id], ['class' => 'btn btn-sm btn-outline-secondary text-nowrap']) ?>
                <?php endif; ?>
            </div>
            <?php if (trim((string) $data['intro']) !== ''): ?>
                <div class="jd-document__intro"><?= RichText::render($data['intro']) ?></div>
            <?php endif; ?>
            <?php if (!empty($data['items']) && is_array($data['items'])): ?>
                <?php $columns = JdTemplateBlock::editorColumns(JdTemplateBlock::editorType($section->block_type ?: 'named_items', $section->section_code)); ?>
                <?php if ($section->block_type === 'approval'): ?>
                    <div class="jd-signatures">
                    <?php foreach ($data['items'] as $index => $item): ?>
                        <?php
                        $approvalKey = (int) ($item['employee_id'] ?? 0) . '|' . ($item['role'] ?? '');
                        $approvalRow = $approvalRowsByEmployee[$approvalKey] ?? null;
                        $approvalData = $approvalRow ? (array) $approvalRow->data_json : [];
                        $signedAt = $approvalData['approve_date'] ?? null;
                        $status = $approvalRow->status ?? null;
                        $signatureImage = $status === 'Pass' && $approvalRow && $approvalRow->employee
                            ? $approvalRow->employee->SignatureShow()
                            : null;
                        ?>
                        <div class="jd-signature">
                            <div class="jd-signature__mark <?= $status === 'Pass' ? 'is-signed' : '' ?>">
                                <?php if ($signatureImage): ?>
                                    <?= Html::img($signatureImage, ['class' => 'jd-signature__image', 'alt' => 'ลายมือชื่อ ' . ($item['employee_name'] ?? '')]) ?>
                                <?php else: ?>
                                    <i class="bi <?= $status === 'Pass' ? 'bi-patch-check-fill' : ($status === 'Pending' ? 'bi-pen' : 'bi-hourglass') ?>"></i>
                                <?php endif; ?>
                                <span><?= $status === 'Pass' ? 'ลงนามแล้ว' : ($status === 'Pending' ? 'รอลงนามลำดับนี้' : ($status === 'None' ? 'รอตามลำดับ' : 'ยังไม่ส่งลงนาม')) ?></span>
                            </div>
                            <strong><?= Html::encode($item['employee_name'] ?? 'รอลงนาม') ?></strong>
                            <span><?= Html::encode($item['role'] ?? 'ผู้ลงนาม') ?></span>
                            <small><?= $signedAt ? 'ลงนามเมื่อ ' . Yii::$app->formatter->asDatetime($signedAt, 'php:d/m/Y H:i') : 'ยังไม่ได้ลงนาม' ?></small>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 jd-document__table">
                        <thead><tr>
                            <th class="jd-document__number">#</th>
                            <?php foreach ($columns as $label): ?><th><?= Html::encode($label) ?></th><?php endforeach; ?>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data['items'] as $index => $item): ?>
                            <tr>
                                <td class="jd-document__number"><?= $index + 1 ?></td>
                                <?php foreach ($columns as $key => $label): ?>
                                    <td><?= RichText::render((string) ($item[$key] ?? ($key === 'expectation' ? ($item['measurement'] ?? '—') : '—'))) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            <?php elseif (trim((string) $section->content) !== ''): ?>
                <div class="jd-document__intro"><?= nl2br(Html::encode($section->content)) ?></div>
            <?php elseif (trim((string) $data['intro']) === ''): ?>
                <p class="text-muted mb-0">ยังไม่ได้ระบุรายละเอียด</p>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>

<?php
$css = <<<'CSS'
.jd-document{border:1px solid rgba(15,23,42,.08);border-radius:10px;background:#fff;overflow:hidden}
.jd-document__section{padding:1rem 1.1rem;border-bottom:1px solid rgba(15,23,42,.08)}
.jd-document__section:last-child{border-bottom:0}
.jd-document__title{margin:0;color:#1a202c;font-weight:600;line-height:1.35}
.jd-document__intro{color:#4a5568;line-height:1.65;max-width:75ch}
.jd-document__table th{background:#f7f9fc;color:#4a5568;font-size:.78rem;font-weight:600;border-bottom-color:rgba(15,23,42,.14)}
.jd-document__table td{padding:.55rem .65rem;color:#1a202c;font-size:.86rem;border-color:rgba(15,23,42,.08);vertical-align:top}
.jd-document__number{width:42px;text-align:center;color:#718096!important;font-variant-numeric:tabular-nums}
.jd-signatures{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem}.jd-signature{text-align:center;border:1px solid rgba(15,23,42,.08);border-radius:8px;padding:1rem}.jd-signature__mark{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.35rem;min-height:60px;margin-bottom:.65rem;color:#718096}.jd-signature__mark.is-signed{color:#15803d}.jd-signature__mark i{font-size:1.25rem}.jd-signature__image{display:block;max-width:150px;max-height:64px;object-fit:contain}.jd-signature strong,.jd-signature span,.jd-signature small{display:block}.jd-signature span{color:#4a5568}.jd-signature small{color:#718096;margin-top:.35rem}
@media(max-width:767.98px){.jd-document__section{padding:.85rem}.jd-document__table{min-width:620px}}
CSS;
$this->registerCss($css);
?>
