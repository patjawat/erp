<?php

use app\modules\jd\models\JdTemplateBlock;
use yii\helpers\Html;

/** @var app\modules\jd\models\JdEmployee $jd */
$editable = $editable ?? false;
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
                <div class="jd-document__intro"><?= nl2br(Html::encode($data['intro'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($data['items']) && is_array($data['items'])): ?>
                <?php $columns = JdTemplateBlock::editorColumns($section->block_type ?: 'named_items'); ?>
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
                                    <td><?= nl2br(Html::encode((string) ($item[$key] ?? '—'))) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
@media(max-width:767.98px){.jd-document__section{padding:.85rem}.jd-document__table{min-width:620px}}
CSS;
$this->registerCss($css);
?>
