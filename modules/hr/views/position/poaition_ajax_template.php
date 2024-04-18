<div>
    <span>
        <?=$model->title;?>
    </span>
    |
    
    <span >
         <label  class="test badge rounded-pill text-primary-emphasis bg-success-subtle">กลุ่ม : <i class="fa-solid fa-layer-group text-success me-1"></i><?= isset($model->positionGroup->title) ? $model->positionGroup->title : '-';?></label>
    </span>
    <label class="badge rounded-pill text-primary-emphasis bg-primary-subtle me-1">ประเภ : <i
                class="fa-solid fa-tags ext-success text-primary"></i>
                <span class="plain" id="selectPositionGroup">
                    <?= isset($model->positionGroup->positionType) ? $model->positionGroup->positionType->title : '-';?>
                </span>
</label>
</div>


