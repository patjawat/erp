<?php
use yii\helpers\Html;
?>
<nav class="d-flex flex-wrap gap-2" aria-label="เมนู MedSOP">
    <?= Html::a('คลังเอกสาร', ['/medsop/document/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
    <?= Html::a('สร้างเอกสาร', ['/medsop/document/create'], ['class' => 'btn btn-sm btn-success']) ?>
</nav>
