<h6>การลงความเห็น</h6>
<div class="p-4">
<?php foreach($model->listComment() as $listComment):?>
    <div class="mb-3">
            <?php echo $listComment->getAvatar('xx',false)['avatar']?>
    </div>
    <?php endforeach;?>
</div>