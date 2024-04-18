<?php
use yii\bootstrap5\Breadcrumbs;

?>
<?php if($this->title !==''):?>
<div class="page-header">
						
    <h3 class="page-title"><?=$this->title;?></h3>
    <?= Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        </div>
        <?php endif; ?>
