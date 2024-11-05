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
        'name' => 'director',
        'checker' => isset($model->data_json['director']) ? $model->data_json['director'] : '',
        'avatar' => $model->Avatar($model->data_json['director'])['avatar'],
        'status' => isset($model->data_json['director_approve']) ? $model->data_json['director_approve'] : ''
    ],
    [
        'title' => 'หัวหน้ากลุ่มงาน',
        'name' => 'leader_group',
        'checker' => isset($model->data_json['leader_group']) ? $model->data_json['leader_group'] : '',
        'avatar' => $model->Avatar($model->data_json['leader_group'])['avatar'],
        'status' => isset($model->data_json['leader_group_approve']) ? $model->data_json['leader_group_approve'] : ''
    ],
    [
        'title' => 'หัวหน้างาน',
        'name' => 'leader',
        'checker' => isset($model->data_json['leader']) ? $model->data_json['leader'] : '',
        'avatar' => $model->Avatar($model->data_json['leader'])['avatar'],
        'status' => isset($model->data_json['leader_approve']) ? $model->data_json['leader_approve'] : ''
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
                                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white">1</span>
                                            ผู้เห็นชอบ</h6>
                                        <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#leader" aria-expanded="true" aria-controls="collapseCard">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>

                                    <div class="card-body card-body collapse show p-2">
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

                                            <?php if($item['status'] == 'Y'):?>
                                                <i class="fa-regular fa-circle-check"></i> อนุมัติ
                                                <?php elseif($item['status'] == 'N'):?>
                                                    <i class="fa-solid fa-xmark"></i> ไม่อนุมัติ
                                                    <?php else:?>
                                                        <i class="fa-regular fa-clock"></i> รอดำเนินการ
                                                    <?php endif?>
                                            <span class="fw-lighter"><?php // $model->viewCommentDate()?></span>
                                        </div>
                                        <?=Html::a('ดำเนินการ',['/lm/leave/approve','id' => $model->id,'checker' => $item['checker'],'name' => $item['name']],['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal'])?>
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