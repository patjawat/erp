<?php
use yii\helpers\Html;
$this->title = 'แจ้งคืนบ้านพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
?>
<div class="container py-3" style="max-width:760px"><div class="card shadow-sm"><div class="card-body">
<?= $this->render('_checkout_modal', compact('model', 'occupancy')) ?>
</div></div></div>
