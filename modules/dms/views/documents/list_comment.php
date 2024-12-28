<h6><i class="fa-regular fa-comments fs-2"></i> การลงความเห็น</h6>

<div class="p-4">
    <?php foreach($model->listComment() as $item):?>
    <div class="d-flex justify-content-between d-flex align-items-start mb-2">
        <?php echo $item->getAvatar('xx',false)['avatar']?>
        <div class="d-flex align-items-start">
            <span class="text-secondary">
                <i class="fa-solid fa-clock"></i>
                <?php
                    $dateTime = $item->created_at;
                    $time = explode(' ', $dateTime)[1]; // แยกเวลาจากวันที่
                    echo $time; // ผลลัพธ์: 23:09:30
                    ?>
            </span>
        </div>

    </div>
    <?php endforeach;?>
</div>