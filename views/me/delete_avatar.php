<?php
use yii\helpers\Html;
use app\modules\hr\models\Employees;
$model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
?>
<div class="d-flex">
            <div class="position-relative">
                <?= Html::img('@web/avatar/patjwat2.png',['class' => 'avatar avatar-xl border border-primary-subtl border-1','data-aos' => 'zoom-in'])?>
                <span class="contact-status offline"></span>
            </div>
            <div class="flex-grow-1 w-50">
                <div class="row">
                    <div class="col-lg-8 col-md-12 col-sm-12 fw-semibold mb-1 d-inline-block text-truncate">
                        <h6>สวัสดี! </h6>
                        <h5>
                            <a href="<?php // Url::to(['/hr/employees/view','id' => $model->id])?>"
                                class="text-dark"><?= $model->fullname?></a>
                        </h5>
                        
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">

                        <div class="dropdown float-end">
                            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-sliders"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                                <?php 
                                // AppHelper::Btn([
                                //     'type' => 'update',
                                //     'url' => ['/hr/employees/update', 'id' => $model->id],
                                //     'modal' => true,
                                //     'size' => 'lg',
                                //     'class' => 'dropdown-item'
                                //     ])
                                    ?>

                                <?php //  Html::a('<i class="fa-solid fa-user-gear me-1"></i> ตั้งค่า', ['/usermanager/user/update-employee', 'id' => $model->user_id], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

                                <?php
                                //  Html::a('<i
                                // class="bi bi-database-fill-gear me-1"></i>ตั้งค่า', ['/hr/categorise', 'id' => $model->id,'title' => ''], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])
                                ?>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="row">

                    <div class="col-12 text-truncate">
                        <!-- <p class="text-muted mb-0"><i class="bi bi-check2-circle text-primary"></i>  </p> -->
<?=$model->positionName()?>
                    </div>

                </div>

            </div>

        </div>