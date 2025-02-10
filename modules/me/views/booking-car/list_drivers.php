<?php
use yii\helpers\Html;
use app\modules\hr\models\Employees;
$listDrivers = Employees::find()
                ->from('employees e')
                ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
                ->where(['a.item_name' => 'driver'])
                ->all();
?>

<div class="d-flex flex-column">
    <?php foreach($listDrivers as $item):?>
    <a href="#" data-driver_id="<?php  echo $item->id?>" data-driver_fullname="<?php echo $item->fullname;?>"
        class="select-driver">
        <div class="card mb-1">
            <div class="card-body">
                <div class="d-flex">
                    <?php echo Html::img($item->ShowAvatar(),['class' => 'avatar'])?>
                    <div class="avatar-detail">
                        <h6 class="mb-1 fs-15"><?php echo $item->fullname?>
                        </h6>
                        <p class="text-muted mb-0 fs-13"><?php echo $item->positionName()?></p>
                    </div>
                </div>

            </div>
        </div>

    </a>
    <?php endforeach;?>
</div>