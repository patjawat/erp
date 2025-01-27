<?php
use yii\helpers\Url;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\modules\dms\models;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
?>
<table class="table table-striped table-fixed">
            <thead>
                <tr>
                    <th style="width:80px;" class="fw-semibold">เลขรับ</th>
                    <th class="fw-semibold" style="width:900px;">เรื่อง</th>
                    <th class="fw-semibold" style="width:150px;">ลงความเห็น</th>
                    <th class="fw-semibold text-center" style="width:150px;">ไฟล์แนบ</th>
                    <th class="fw-semibold" style="width:300px;">วันที่รับ</th>
                </tr>
            </thead>
            <tbody class="align-middle  table-group-divider table-hover">
                <?php foreach($dataProvider->getModels() as $item):?>
                <?php // if($item->viewCount()['reading'] <= 0):?>
                <tr class="" style="max-width:200px">
                    <td class="fw-semibold">

                        <?php  echo $item->document->doc_regis_number?></td>
                    <td class="fw-light align-middle">
                        <a href="<?php echo Url::to(['/me/documents/view','id' => $item->id])?>"
                            class="text-dark open-modal-fullscreen-x">
                            <div class=" d-flex flex-column" style="max-width:1000px">
                                <div>
                                    <p class="text-truncate fw-semibold fs-6 mb-0">
                                        <?php if($item->document->doc_speed == 'ด่วนที่สุด'):?>
                                        <span class="badge text-bg-danger fs-13">
                                            <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                        </span>
                                        <?php endif;?>

                                        <?php if($item->document->secret == 'ลับที่สุด'):?>
                                        <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                            ลับที่สุด
                                        </span>
                                        <?php endif;?>
                                        <?php echo $item->document->topic?>
                                    </p>

                                </div>
                            </div>
                            <span class="text-primary fw-normal fs-13">
                                <i class="fa-solid fa-inbox"></i>
                                <?php  echo $item->documentOrg->title ?? '-';?>
                                <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                    <i class="fa-regular fa-eye"></i> <?php echo $item->document->viewCount()?>
                                </span>
                            </span>
                            <?php echo Html::a(($item->bookmark == 'Y' ? '<i class="fa-solid fa-star text-warning"></i>' : '<i class="fa-regular fa-star"></i>'),['/me/documents/bookmark', 'id' => $item->id],['class' => 'bookmark','id' => 'bookmark-'.$item->id])?>
                        </a>
                    </td>
                    <td>
                        <?php echo $item->document->StackDocumentTags('comment')?>
                    </td>
                    <td class="text-center">
                        <?php // echo $item->isFile() ? Html::a('<i class="fas fa-paperclip"></i>',['/dms/documents/clip-file','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']]) : ''?>
                        <?php echo $item->document->isFile() ? '<i class="fas fa-paperclip"></i>' : ''?>
                    </td>
                    <td class="fw-light align-middle">
                        <div class=" d-flex flex-column">
                            <?php
                             echo $item->document->viewCreate()['avatar'];
                            ?>
                            <!-- <span class="fw-normal fs-6"><?php echo $item->document->viewReceiveDate()?></span>
                            <span class="fw-lighter fs-13"><?php echo isset($item->document->doc_time) ? '<i class="fa-solid fa-clock"></i> '.$item->document->doc_time : ''?></span> -->
                        </div>
                    </td>
                   

                </tr>
                <?php //endif;?>
                <?php endforeach;?>

            </tbody>
        </table>


    <?php
    // $getCommentUrl = Url::to(['/dms/documents/comment','id' => $model->id]);
    // $listCommentUrl = Url::to(['/dms/documents/list-comment','id' => $model->id]);


    $js = <<<JS
            \$("body").on("click", ".bookmark", function (e) {
                e.preventDefault();
                var bookmark = $(this).attr('id');
                const starIconSolid = $(this).find('i.fa-regular.fa-star');
                const starIconRegular = $(this).find('i.fa-solid.fa-star');
                
                \$.ajax({
                    type: "get",
                    url: \$(this).attr('href'),
                    dataType: "json",
                    success: async function (res) { 
                        console.log(res.data);
                        if(res.data.bookmark == 'Y'){
                            // $(this).html('<i class="fa-solid fa-star text-warning fs-2"></i>')
                            starIconSolid.attr('class', 'fa-solid fa-star text-warning');
                            success('ติดดาว')  
                        }
                        
                        if(res.data.bookmark == 'N'){
                            // $(this).html('<i class="fa-regular fa-star fs-2"></i>')
                            starIconRegular.attr('class', 'fa-regular fa-star');
                            success('ยกเลิกติดดาว')  
                        }
                        // location.reload();
                    }
                });
            });
        JS;
    $this->registerJS($js);
    ?>