<?php
use yii\web\View;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroup $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Team Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/views/employees/menu') ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-body">
    <div class="d-flex justify-content-between">
            <h6>
                <i class="bi bi-ui-checks"></i> ประวัติคำสั่งแต่งตั้ง<?=$this->title?> รายการ
                <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format(0, 0) ?></span>
            </h6>
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างคำสั่งใหม่',['/hr/team-group-detail/create','name' => 'appointment','category_id' => $model->id,'title' => 'เพิ่มคำสั่ง'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
        </div>
        <?php
use yii\widgets\DetailView;
?>
<div
    class="table-responsive"
>
    <table
        class="table table-primary mb-5"
    >
        <thead>
            <tr>
            <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                <th scope="col">พ.ศ.</th>
                <th scope="col">รายการ</th>
                <th scope="col">ทีมประสาน</th>
                <th class=" fw-semibold text-center" scope="col" style="width:130px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
        <tbody class="align-middle table-group-divider">
            <?php foreach(array_reverse($model->teamGroupDetail) as $key => $item):?>
            <tr>
                <td class="text-center fw-semibold"><?php echo ($key+1)?>
            </td>
            <td><?=$item->thai_year?></td>
                <td><?=$item->title?></td>
                <td><?php echo $item->stackComittee()?></td>
                <td>
                <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                    <?=Html::a('<i class="fa-solid fa-eye"></i>',['/hr/team-group-detail/view','id' => $item->id],['class' => 'btn btn-light'])?>
                    <?=Html::a('<i class="fa-solid fa-pen-to-square"></i>',['/hr/team-group-detail/update','id' => $item->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขคำสั่ง'],['class' => 'btn btn-light open-modal','data' => ['size' => 'modal-lg']])?>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-sort-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                        <li><?=Html::a('<i class="fa-solid fa-trash-can me-1"></i> ลบข้อมูล',['delete-appointment','id' => $item->id],['class' => 'dropdown-item delete-item'])?></li>
                        </ul>
                    </div>
                    </div>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>


    </div>
</div>
<?php
$js = <<< JS

loadData();

function loadData()
{
    $.ajax({
        type: "get",
        url: "/hr/team-group-detail/list-appointment",
        data:{
            'team_group_id':$model->id
        },
        dataType: "json",
        success: function (res) {
            $('.show').html(res.content)
        }
    });
}

JS;
$this->registerJs($js,View::POS_END);
?>