<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <a href="<?= Url::to(['/jd/template/index']) ?>" class="btn <?= ($active ?? '') !== 'template' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <i class="bi bi-file-earmark-text"></i> Template JD
    </a>
</div>
