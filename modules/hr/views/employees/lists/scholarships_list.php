<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
$title = '<i class="fa-solid fa-graduation-cap"></i> ประวัติการการรับทุน';
?>
<?php Pjax::begin(['id' => 'scholarships']);?>

<div class="card border-0">
    <div class="card-body">

                <div class="d-flex justify-content-between">
            <h5 class="card-title"><?=$title?></h5>
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'scholarships', 'title' => $title], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>

            </div>
            
        </div>
        <div class="table-responsive">
            <table class="table table-striped" style="margin-bottom: 100px;">
                <thead>
                    <tr>
                        <th style="width: 32px;">#</th>
                        <th>วันรับทุน</th>
                        <th> วันสำเร็จ</th>
                        <th>ชื่อทุน</th>
                        <th>หลักสูตร</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->scholarships as $key => $item): ?>
                    <tr class="">
                        <td><?=$key+1?></td>
                        <td>
                            <?=$item->data_json['date_start'];?>
                        </td>
                        <td>
                            <?=$item->data_json['date_end'];?>
                        </td>
                        <td>
                            <?=$item->data_json['name']?>
                        </td>
                        <td>
                            <?=$item->data_json['course']?>
                        </td>
                        <td class="text-center align-middle">
                            <?=$this->render('./actions',['model' => $item,'name' => $name,'title' => $title])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>


    </div>
</div>

<?php Pjax::end();?>