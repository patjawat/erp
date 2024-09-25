<?php
use app\models\Categorise;
?>
<?=$model->title?>

<?php if($model->name == 'position_type'):?>
    <div class="d-flex" style="width:100px">
        
        <?php foreach(Categorise::find()->where(['name' => 'position_group','category_id' => $model->code])->all() as $group):?>
            <div>
        
        <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1"><i
        class="fa-solid fa-tags me-1 text-success"></i><?=$group->title?></label>
    </div>
        <?php endforeach;?>
</div>
    <?php endif;?>

    <?php if($model->name == 'position_group' && isset($model->positionType->title)):?>
    ( <code><?=$model->positionType->title?></code> )
    </div>
</div>
    <?php endif;?>