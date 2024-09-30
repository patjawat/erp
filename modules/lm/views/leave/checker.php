<?php
use yii\helpers\Html;
$this->registerCssFile('@web/css/timeline.css');
?>
<style>
.modal-body {
    background: #f1f2f8;
}
</style>
<?php
$items = [
    [
        'title' => 'ผู้อำนวยการ',
        'leader' => isset($model->data_json['director']) ? $model->data_json['director'] : '',
        'avatar' => $model->Avatar($model->data_json['director'])['avatar']
    ],
    [
        'title' => 'หัวหน้ากลุ่มงาน',
        'leader' => isset($model->data_json['leader_group']) ? $model->data_json['leader_group'] : '',
        'avatar' => $model->Avatar($model->data_json['leader_group'])['avatar']
    ],
    [
        'title' => 'หัวหน้างาน',
        'leader' => isset($model->data_json['leader']) ? $model->data_json['leader'] : '',
        'avatar' => $model->Avatar($model->data_json['leader'])['avatar']
    ],
];
?>
<section class="bsb-timeline-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-8 col-sm-12">
                <ul class="timeline">
                    <?php foreach ($items as $item): ?>
                    <li class="timeline-item">
                        <div class="timeline-body">
                            <div class="timeline-content">
                                <div class="card border-0 shadow-none">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-subtitle text-dark py-2"><?php echo $item['title']; ?></h6>
                                        </div>
                                        <div class="col-12 text-truncate px-2">
                                            <?php
                                            try {
                                                //code...
                                                echo $item['avatar'];
                                            } catch (\Throwable $th) {
                                                //throw $th;
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1 d-flex justify-content-between">
                                        <div>
                                            <i class="fa-regular fa-clock"></i> รออนุมัติ
                                            <span class="fw-lighter"><?php // $model->viewCommentDate()?></span>
                                        </div>
                                        <?=Html::a('ดำเนินการ',['/lm/leave/approve'],['class' => 'btn btn-sm btn-primary rounded-pill shadow'])?>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </li>
<?php endforeach?>
                </ul>

            </div>
        </div>
    </div>
</section>