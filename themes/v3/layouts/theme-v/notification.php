<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\helpdesk\models\Helpdesk;

$helpdesks  = Helpdesk::find()->where(['created_by' => Yii::$app->user->id])->andWhere(['in','status',[1,2,3]])
// ->andWhere(new \yii\db\Expression("JSON_EXTRACT(data_json, '$.repair_status') IN ("ร้องขอ",""รับเรื่อง','ดำเนินการ')"))
->all();
?>
<?php if(count($helpdesks)> 0):?>
<div class="d-inline-flex ms-0 ms-sm-2 dropdown" data-aos="zoom-in" data-aos-delay="300">
                <button data-bs-toggle="dropdown" aria-haspopup="true" type="button"
                    id="page-header-notification-dropdown" aria-expanded="false"
                    class="btn header-item notify-icon position-relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <span class="badge bg-danger badge-pill notify-icon-badge bg-danger rounded-pill text-white"><?=count($helpdesks)?></span>
                </button>
                <div aria-labelledby="page-header-notification-dropdown"
                    class="dropdown-menu-lg dropdown-menu-right p-0 dropdown-menu" style="width: 350px;">
                    <div class="notify-title p-3">
                        <h5 class="fs-14 fw-semibold mb-0">
                            <span>Notification</span>
                            <!-- <a class="text-primary" href="javascript: void(0);">
                                <small>Clear All</small>
                            </a> -->
                        </h5>
                    </div>
                    <div class="notify-scroll">
                        <div class="scroll-content" id="notify-scrollbar" data-scrollbar="true" tabindex="-1"
                            style="overflow: hidden; outline: none;">
                            <div class="scroll-content">
                                <div class="scroll-content">
                                    <?php foreach($helpdesks as $helpdesk):?>
                                    <a href="<?=Url::to(['/helpdesk/repair/timeline','id' => $helpdesk->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'])?>" class="dropdown-item notification-item open-modal">
                                        <div class="d-flex">
                                            <div class="avatar avatar-xs bg-primary">
                                                <i class="bx bx-user-plus"></i>
                                            </div>
                                            <p class="media-body">
                                            <?=$helpdesk->viewCreateUser()?>
                                                <small class="text-muted"><?=Yii::$app->thaiFormatter->asDateTime($helpdesk->created_at,'short')?> | <?=$helpdesk->data_json['title']?></small>
                                            </p>
                                        </div>
                                    </a>

                                    <?php // Html::a($model->data_json['title'],['/helpdesk/repair/view-task','id' => $model->id,'title' => '<i class="fa-solid fa-circle-exclamation text-danger"></i> แจ้งซ่อม'],['class' => 'open-modal','data' => ['size' => 'modal-lg']])?>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <div class="scrollbar-track scrollbar-track-x" style="display: none;">
                                <div class="scrollbar-thumb scrollbar-thumb-x"></div>
                            </div>
                            <div class="scrollbar-track scrollbar-track-y" style="display: none;">
                                <div class="scrollbar-thumb scrollbar-thumb-y"></div>
                            </div>
                        </div>
                        <div class="notify-all">
                            <a href="<?=Url::to(['/me'])?>" class="text-primary text-center p-3">
                                <small>View All</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif;?>