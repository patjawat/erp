<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\dms\models\Documents $model */
/** @var app\modules\dms\models\DocumentsDetail $detail */

$this->params['current_page']   = $current_page ?? 'news';
$this->params['mobileTitle']    = 'หนังสือราชการ';
$this->params['mobileSubtitle'] = $model->topic ?? 'รายละเอียดหนังสือ';

$topicRaw = trim((string) ($model->topic ?? ''));
$heroTitle = $topicRaw !== '' ? mb_strimwidth($topicRaw, 0, 60, '…', 'UTF-8') : 'หนังสือราชการ';

// Read status drives the hero icon: opened ⇒ mail-check, otherwise ⇒ mail-open.
$isRead    = !empty($detail->doc_read);
$heroIcon  = $isRead ? 'mail-check' : 'mail-open';
$heroSub   = $isRead ? 'เปิดอ่านแล้ว' : 'เปิดดูรายละเอียด';
?>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon'     => $heroIcon,
    'title'    => $heroTitle,
    'subtitle' => $heroSub,
]) ?>

<div class="app-scroll">
    <div class="d-flex flex-column gap-3">
        <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill align-self-start">
            <i data-lucide="arrow-left" class="me-1" style="width: 1rem; height: 1rem; vertical-align: -0.2em;"></i>
            กลับรายการหนังสือ
        </a>

        <?= $this->render('@app/modules/dms/views/documents/view', [
            'model'  => $model,
            'detail' => $detail,
        ]) ?>
    </div>
</div>
