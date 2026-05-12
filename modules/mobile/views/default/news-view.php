<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\dms\models\Documents $model */
/** @var app\modules\dms\models\DocumentsDetail $detail */

$this->params['current_page'] = $current_page ?? 'news';
$this->params['mobileTitle'] = 'หนังสือราชการ';
$this->params['mobileSubtitle'] = $model->topic ?? 'รายละเอียดหนังสือ';
?>

<div class="d-flex flex-column gap-3">
    <a href="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="btn btn-outline-secondary btn-sm rounded-pill align-self-start">
        <i data-lucide="arrow-left" class="me-1" style="width: 1rem; height: 1rem; vertical-align: -0.2em;"></i>
        กลับรายการหนังสือ
    </a>

    <?= $this->render('@app/modules/dms/views/documents/view', [
        'model' => $model,
        'detail' => $detail,
    ]) ?>
</div>
