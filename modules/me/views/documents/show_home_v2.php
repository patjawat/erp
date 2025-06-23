<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

                                     <?php foreach($dataProvider->getModels() as $key => $item):?>
                                    <!-- <div class="d-flex align-items-center p-3 border border-danger bg-danger bg-opacity-10 rounded-3"> -->
                                    <div class="d-flex align-items-center p-3 border bg-opacity-10 rounded-3">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                               <i class="fa-solid fa-file-lines text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id,'callback' => '/me'])?>" class="open-modal" data-size="modal-xxl">
                                                <h6 class="fw-medium text-dark mb-0"><?php echo $item->document ? $item->document->topic : ''?></h6>
                                                </a>
                                                    <?php if (isset($item->document) && $item->document->doc_speed == 'ด่วนที่สุด'): ?>
                                                        <span class="badge bg-danger text-white">ด่วนที่สุด</span>
                                                        <?php elseif (isset($item->document) && $item->document->secret == 'ลับที่สุด'): ?>                                                            
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger text-white">ลับที่สุด</span>
                                                                    
                                                                    <?php endif?>
                                            </div>
                                            <div class="small text-muted mt-1">จากศูนย์เทคโนโลยีสารสนเทศ - 1 ชั่วโมงที่แล้ว</div>
                                        </div>
                                    </div>
                                    <?php endforeach;?>
                                    <!-- <div class="d-flex align-items-center p-3 border rounded-3">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-medium text-dark mb-0">ขอเชิญประชุมประจำเดือน</h6>
                                                <span class="badge bg-primary">ด่วน</span>
                                            </div>
                                            <div class="small text-muted mt-1">จากฝ่ายบริหาร - 3 ชั่วโมงที่แล้ว</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center p-3 border rounded-3">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-indigo bg-opacity-25 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-indigo">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-medium text-dark mb-0">แจ้งกำหนดการอบรมออนไลน์</h6>
                                                <span class="badge bg-secondary">ปกติ</span>
                                            </div>
                                            <div class="small text-muted mt-1">จากฝ่ายพัฒนาบุคลากร - 1 วันที่แล้ว</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center p-3 border rounded-3">
                                        <div class="me-3">
                                            <div class="rounded-circle bg-success bg-opacity-25 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-success">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <h6 class="fw-medium text-dark mb-0">ประกาศวันหยุดราชการเพิ่มเติม</h6>
                                                <span class="badge bg-secondary">ปกติ</span>
                                            </div>
                                            <div class="small text-muted mt-1">จากฝ่ายบุคคล - 2 วันที่แล้ว</div>
                                        </div>
                                    </div> -->
                  