<?php
use yii\helpers\Html;
?>
<div class="d-flex flex-wrap gap-2">
    <?= Html::a('<i class="bi bi-signpost-split me-1"></i> แม่แบบ Roadmap', ['/hr/training-roadmap/index'], ['class' => 'btn ' . (($active ?? '') === 'index' ? 'btn-primary' : 'btn-outline-primary')]) ?>
    <?= Html::a('<i class="bi bi-journal-check me-1"></i> ประวัติอบรม', ['/hr/development/index'], ['class' => 'btn btn-outline-primary']) ?>
</div>
