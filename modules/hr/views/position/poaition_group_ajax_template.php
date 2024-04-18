<div>
    <span>
        <?=$model->title;?>
    </span>
    |
    <span>
        ประเภท : <i class="bi bi-clipboard-check"></i> <label
            class="badge rounded-pill text-primary-emphasis bg-primary-subtle me-1"><?=isset($model->positionType) ? $model->positionType->title : '-';?></label>
    </span>

</div>
