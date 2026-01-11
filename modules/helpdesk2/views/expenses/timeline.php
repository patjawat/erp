<div class="timeline">
    <?php foreach($lists as $item):?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?=$item->viewCreateDateTime()?></div>
                        <div class="timeline-title"><?=$item->status?></div>
                        <div class="timeline-body"><?=$item->title?></div>
                    </div>
                    <?php endforeach;?>
                </div>