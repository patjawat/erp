    <?php
    use yii\helpers\Html;
    $color = '';
    if (isset($item->leaveType) && isset($item->leaveType->data_json['color'])) {
        $color = $item->leaveType->data_json['color'];
    }
    $leaveTypeCode = isset($item->leaveType) && isset($item->leaveType->code) ? $item->leaveType->code : '';
    $leaveTypeTitle = isset($item->leaveType) && isset($item->leaveType->title) ? $item->leaveType->title : '-';
    ?>
    <div class="d-flex align-items-center p-1 border border-4 border-end-0 border-top-0 border-bottom-0 border-primary <?=$leaveTypeCode?>" style="cursor: pointer; <?='background-color:' .$color?>">
        <?=Html::img($item->employee->ShowAvatar(),["class" => "leave-avatar",'alt' => $item->employee->fullname])?>
        <div>
            <span><?=$item->employee->fullname?>  <?= $leaveTypeTitle ?></span>
        </div>
    </div>
