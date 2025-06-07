<?php
use yii\helpers\Html;
?>
<style>
.card-img-top {
    max-height: 320px;
    min-height: 320px;
}

.card-img-top {}
</style>

<div class="row row-cols-1 row-cols-xl-12 row-cols-lg-5 row-cols-md-4">
    <?php foreach($dataProvider->getModels() as $key => $model):?>
    <div class="col">

        <div class="card text-black position-relative">
            <!-- <i class="fab fa-apple fa-lg pt-3 pb-1 px-3"></i> -->
            <!-- <img
            src="https://mdbcdn.b-cdn.net/img/Photos/Horizontal/E-commerce/Products/3.webp"
            class="card-img-top"
            alt="Apple Computer"
          /> -->

            <div class="card-body">
                <div class="text-center">
                    <h5 class="card-title text-truncate">
                        <?=Html::a($model->AssetitemName(), ['/am/asset/view','id' => $model->id],['class' => '', ])?></h5>
                    <p class="text-muted mb-4"><?=$model->AssetTypeName();?></p>
                </div>
                <?= Html::a(Html::img($model->showImg(),['class' => 'card-img-top p-2 rounded border border-2 border-secondary-subtle']), ['/am/asset/view','id' => $model->id],['class' => '', ]) ?>
                <div>
                    <ul class="list-inline">
                        <li>
                            <i class="bi bi-check2-circle text-primary fs-5"></i> <span
                                class="fw-semibold">เลขครุภัณฑ์</span>
                            <span class="text-danger"><?=$model->code?><span>
                        </li>
                        <li>
                            <i class="bi bi-check2-circle text-primary fs-5"></i>
                            <span class="fw-semibold">วันเดือนปีทีซื้อ</span>
                            <?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium')?>
                        </li>
                        <li>
                            <i class="bi bi-check2-circle text-primary fs-5"></i>
                            <span class="fw-semibold">วิธีได้มา</span> <?=$model->method_get?>
                        </li>
                        <li>
                            <i class="bi bi-check2-circle text-primary fs-5"></i>
                            <span class="fw-semibold">ประเภทเงิน</span> :
                            <?=$model->budget_type?>
                        </li>

                        <li class="text-truncate"><i class="bi bi-check2-circle text-primary fs-5 text-truncate"></i>
                            <span class="fw-semibold">ประจำหน่วยงาน</span>
                            <?php if(isset($model->data_json['department_name']) && $model->data_json['department_name'] == ''):?>
                            <?= isset($model->data_json['department_name_old']) ? $model->data_json['department_name_old'] : ''?>
                            <?php else:?>
                            <?= isset($model->data_json['department_name']) ? $model->data_json['department_name'] : ''?>
                            <?php endif;?>
                        </li>
                        <li>
                            <div class="d-flex justify-content-between">

                                <div>
                                    <i class="bi bi-check2-circle text-primary fs-5"></i>
                                <span class="fw-semibold">มูลค่า</span> :
                                <span
                                class="text-white bg-primary badge rounded-pill fs-6 fw-semibold shadow"><?=isset($model->price) ? number_format($model->price,2) : ''?></span>
                                บาท
                            </div>
                            <div>
                             <?=$model->viewstatus()?>
                                </div>
                            </div>
                        </li>


                    </ul>

                </div>
                <div class="d-flex justify-content-between total font-weight-bold mt-4 bg-secondary-subtle rounded p-2">
                    <?=$model->getOwner()?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach?>
</div>