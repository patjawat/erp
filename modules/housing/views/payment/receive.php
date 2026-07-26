<?php
use yii\helpers\Html;
$this->title = 'รับชำระค่าใช้จ่าย';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'utility']) ?><?php $this->endBlock();
?>
<div class="container py-3" style="max-width:760px"><div class="card shadow-sm"><div class="card-body"><?= $this->render('_receive_form', compact('form','account')) ?></div></div></div>
