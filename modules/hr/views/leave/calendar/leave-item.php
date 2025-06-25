    <?php
    use yii\helpers\Html;
    ?>
    <div class="d-flex align-items-center p-1 border border-4 border-end-0 border-top-0 border-bottom-0 border-primary <?=$item->leaveType?->code?>" style="cursor: pointer; <?= $item->leaveType?->data_json['color'] ? ('background-color:' . $item->leaveType?->data_json['color']) : '' ?>">
        <?=Html::img($item->employee->ShowAvatar(),["class" => "leave-avatar",'alt' => $item->employee->fullname])?>
        <div>
            <span><?=$item->employee->fullname?>  <?= $item->leaveType?->title ?? '-' ?></span>
        </div>
    </div>
