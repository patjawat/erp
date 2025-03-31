
<?php
use yii\widgets\Pjax;
?>
<?php Pjax::begin(['id' => 'employee-container', 'enablePushState' => false, 'timeout' => 500000]); ?>
<?= $this->render('_search_employee', ['model' => $searchModel]); ?>
<div class="p-1 mt-4">
    <?php foreach($dataProvider->getModels() as $item):?>
        <div class="card mb-2 hover-card">
            <div class="card-body p-3">
                <?php echo $item->getAvatar(false)?>
                
            </div>
        </div>
        <?php endforeach;?>
    </div>
    <?php Pjax::end(); ?>