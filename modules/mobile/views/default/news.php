<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string $filter */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->params['current_page'] = $current_page ?? 'news';

$filter = trim((string) ($filter ?? 'all'));
if (!in_array($filter, ['all', 'unread'], true)) {
    $filter = 'all';
}

$officialUnreadCount = (int) ($officialUnreadCount ?? 0);
$officialTotalCount = (int) ($officialTotalCount ?? 0);
$this->params['mobileTitle'] = 'หนังสือราชการ';
$this->params['mobileSubtitle'] = $filter === 'unread' ? 'หนังสือที่ยังไม่ได้อ่าน' : 'รายการหนังสือที่ส่งมาถึงคุณ';

$tabs = [
    'all' => [
        'label' => 'ทั้งหมด',
        'icon' => 'list',
        'count' => $officialTotalCount,
    ],
    'unread' => [
        'label' => 'ยังไม่อ่าน',
        'icon' => 'mail',
        'count' => $officialUnreadCount,
    ],
];

$documents = $dataProvider->getModels();
?>
<style>
    .official-doc-hero {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.10) 0%, rgba(13, 110, 253, 0.03) 100%);
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    }

    .official-doc-tabs {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.25rem;
        -webkit-overflow-scrolling: touch;
    }

    .official-doc-tabs::-webkit-scrollbar {
        height: 4px;
    }

    .official-doc-tab {
        flex-shrink: 0;
        border-radius: 999px;
        padding: 0.55rem 0.85rem;
        text-decoration: none;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #6c757d;
        background: #f0f2f5;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }

    .official-doc-tab.active {
        background: var(--mobile-primary);
        color: #fff;
    }

    .official-doc-tab .tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.4rem;
        height: 1.4rem;
        border-radius: 999px;
        padding: 0 0.35rem;
        background: rgba(255, 255, 255, 0.18);
        font-size: 0.75rem;
    }

    .official-doc-empty {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }

    .official-doc-empty .empty-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        background: rgba(13, 110, 253, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--mobile-primary);
        margin: 0 auto 0.75rem;
    }
</style>

<div class="d-flex flex-column gap-3">
    <div class="card official-doc-hero">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="min-w-0">
                    <div class="small text-body-secondary mb-1">หนังสือที่ส่งมาถึงคุณ</div>
                    <h2 class="h5 fw-semibold mb-1">หนังสือราชการ</h2>
                    <p class="small text-body-secondary mb-0">เปิดเอกสารเพื่ออ่าน คอมเมนต์ และส่งต่อไปยังผู้อื่นหรือหน่วยงานอื่นได้ทันที</p>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="fw-semibold text-primary fs-4 lh-1"><?= Html::encode((string) $officialUnreadCount) ?></div>
                    <div class="small text-body-secondary">ยังไม่อ่าน</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                    <?= Html::encode((string) $officialTotalCount) ?> ฉบับทั้งหมด
                </span>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">
                    <?= Html::encode((string) $officialUnreadCount) ?> ฉบับยังไม่อ่าน
                </span>
            </div>
        </div>
    </div>

    <nav class="official-doc-tabs" role="tablist" aria-label="ตัวกรองหนังสือราชการ">
        <?php foreach ($tabs as $key => $tab): ?>
            <a href="<?= Html::encode(Url::to(['/mobile/default/news', 'filter' => $key])) ?>" class="official-doc-tab <?= $filter === $key ? 'active' : '' ?>">
                <i data-lucide="<?= Html::encode($tab['icon']) ?>" style="width: 1rem; height: 1rem;"></i>
                <?= Html::encode($tab['label']) ?>
                <span class="tab-count"><?= Html::encode((string) $tab['count']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if (empty($documents)): ?>
        <div class="card official-doc-empty">
            <div class="card-body py-5 text-center">
                <div class="empty-icon">
                    <i data-lucide="mail-open" style="width: 1.5rem; height: 1.5rem;"></i>
                </div>
                <div class="fw-semibold text-dark mb-1">
                    <?= $filter === 'unread' ? 'ไม่มีหนังสือที่ยังไม่ได้อ่าน' : 'ยังไม่มีหนังสือราชการ' ?>
                </div>
                <p class="small text-body-secondary mb-3">
                    <?= $filter === 'unread' ? 'ตอนนี้ไม่มีหนังสือใหม่ที่ยังไม่ได้เปิดอ่าน' : 'เมื่อมีหนังสือส่งมาถึง รายการจะปรากฏที่หน้านี้' ?>
                </p>
                <?php if ($filter === 'unread'): ?>
                    <a href="<?= Html::encode(Url::to(['/mobile/default/news', 'filter' => 'all'])) ?>" class="btn btn-outline-primary btn-sm rounded-pill">ดูหนังสือทั้งหมด</a>
                <?php else: ?>
                    <a href="<?= Html::encode(Url::to(['/mobile/default/index'])) ?>" class="btn btn-outline-secondary btn-sm rounded-pill">กลับหน้าหลัก</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?= $this->render('_official_documents_cards', [
            'documents' => $documents,
            'compact' => false,
        ]) ?>

        <?php if ($dataProvider->pagination !== false && $dataProvider->pagination->pageCount > 1): ?>
            <div class="d-flex justify-content-center mt-2">
                <?= \yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination pagination-sm mb-0'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link rounded-pill'],
                    'disabledPageCssClass' => 'disabled',
                    'activePageCssClass' => 'active',
                ]) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
