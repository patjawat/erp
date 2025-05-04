<div class="d-flex justify-content-between pt-2">
    <h6 class="fs-13"><?=$model->room->title?></h6>
    <?=$model->viewStatus()['view']?>
</div>
<div class="avatar-detail">
    <h6 class="mb-0 fs-13"><?= $model->title ?></h6>
    <p class="text-muted mb-0 fs-13"><?= $model->viewMeetingTime()?></p>
</div>