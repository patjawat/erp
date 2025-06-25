    <?php
    use yii\helpers\Html;
    $color = '';
    if (isset($item->leaveType) && isset($item->leaveType->data_json['color'])) {
        $color = $item->leaveType->data_json['color'];
    }

    $colorStatus = '';
    if (isset($item->leaveStatus) && isset($item->leaveStatus->data_json['color'])) {
        $colorStatus = $item->leaveStatus->data_json['color'];
    }

    $leaveTypeCode = isset($item->leaveType) && isset($item->leaveType->code) ? $item->leaveType->code : '';
    $leaveTitle = isset($item->data_json['reason']) ? $item->data_json['reason'] : '';
    ?>
    <div class="d-flex align-items-center p-1 <?=$leaveTypeCode?>" style="cursor: pointer; <?='background-color:' .$color?>; border-left: 5px solid <?=$colorStatus?>;">
        <?=Html::img($item->employee->ShowAvatar(),["class" => "leave-avatar",'alt' => $item->employee->fullname])?>
        <div>
          <?= $leaveTitle ?>
          <span class="badge text-bg-light p-0">
             <?php
                        try {
                            echo $item->viewStatus();
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        ?>
          </span>
        </div>
    </div>
