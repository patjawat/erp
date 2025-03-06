<?php
use yii\helpers\Html;

$this->registerCssFile('@web/css/timeline.css');

?>



                    <?php foreach ($model->listApprove() as $item): ?>
                
                                <div class="card <?php echo $item->status == 'Pending' ? 'border-1 border-warning' : 'border-0' ?> shadow-none">
                                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0"><span class="badge bg-primary rounded-pill text-white"><?php echo $item->level ?></span>
                                            <?php echo $item->title ?></h6>
                                        <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#<?php echo $item->id ?>" aria-expanded="true" aria-controls="collapseCard">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>

                                    <div class="card-body card-body collapse p-2 <?php echo $item->status == 'Pending' ? 'show' : null ?>" id="<?php echo $item->id ?>">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="card-subtitle text-dark py-2"><?php // echo  $item['title']; ?></h6>
                                        </div>
                                        <div class="col-12 text-truncate px-2">
                                            <?php
                                            echo $item->getAvatar()['avatar']
                                            ?>
                                        </div>
                                    </div>
                                    <div class="card-footer py-1 d-flex justify-content-between">
                                        <div>

                                          
                                                <?php
                                                $approveDate = $item->viewApproveDate();
                                                if ($item->status == 'Pending') {
                                                    echo '<i class="bi bi-hourglass-bottom  fw-semibold text-warning"></i> รอ' . ($item->level == 3 ? $item->title : $item->data_json['topic']);
                                                } else if ($item->status == 'Approve') {
                                                    echo '<i class="bi bi-check-circle fw-semibold text-success"></i> ' . $item->data_json['topic'] . '  <i class="bi bi-clock-history"></i> ' . $approveDate;
                                                } else if ($item->status == 'Reject') {
                                                    echo '<i class="bi bi-stop-circle  fw-semibold text-danger"></i> ไม่' . $item->data_json['topic'] . ' <i class="bi bi-clock-history"></i> ' . $approveDate;
                                                } else if ($item->status == 'Cancel') {
                                                }
                                                ?>
                                        </div>
                                        <?php if($model->status == 'ReqCancel' || $model->status == 'Cancel'):?>
                                            -
                                            <?php else:?>
                                        <?php echo Html::a('ดำเนินการ', ['/me/leave/approve', 'id' => $item->id, 'title' => $item->title], ['class' => 'btn btn-sm btn-primary rounded-pill shadow open-modal']) ?>
                                        <?php endif?>
                                    </div>
                                </div>
                  
                    <?php endforeach ?>
