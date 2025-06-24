    <?php
    use yii\helpers\Html;
    ?>
    <div class="leave-item leave-sick" style="cursor: pointer;">
        <?=Html::img($item->employee->ShowAvatar(),["class" => "leave-avatar",'alt' => $item->employee->fullname])?>
        <div>
            <div><?=$item->employee->fullname?></div>
            <div>ลาป่วย</div>
        </div>
    </div>
