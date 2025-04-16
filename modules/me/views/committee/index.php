<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-top align-items-center">
            <div class="d-flex gap-3">
                <h6>
                    <i class="bi bi-ui-checks"></i> ทะเบียนกลุ่ม/ทีมประสาน
                    <span
                    class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                    รายการ
                </h6>
            </div>
        </div>
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
        <div class="table-responsive">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="fw-semibold" scope="col">รายการ</th>
                        <th class="fw-semibold" scope="col">ตำแหน่ง</th>
                        <th class="fw-semibold" scope="col">ลงความเห็น</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $key => $item):?>
                    <tr>
                        <td class="text-center fw-semibold">
                            <?php echo (($dataProvider->pagination->offset + 1)+$key)?>
                        </td>
                        <td class="fw-light align-middle">
                        <a href="<?php echo Url::to(['/me/documents/view', 'id' => $item->id]) ?>"
                            class="text-dark open-modal-fullscreen-x">
                            <div>
                            <p class="text-primary fw-semibold fs-13 mb-0">
                                        <?php if (isset($item->document) && $item->document->doc_speed === 'ด่วนที่สุด'): ?>
                                            <span class="badge text-bg-danger fs-13">
                                                <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                                            </span>
                                        <?php endif; ?>

                                        <?php if (isset($item->document) && $item->document->secret === 'ลับที่สุด'): ?>
                                            <span class="badge text-bg-danger fs-13">
                                                <i class="fa-solid fa-lock"></i> ลับที่สุด
                                            </span>
                                        <?php endif; ?>

                                        <?= Html::img('@web/img/krut.png', ['style' => 'width:20px']); ?>

                                        <?= isset($item->document) ? Html::encode($item->document->doc_number) : 'ไม่พบเลขที่เอกสาร'; ?>
            
                                    </p>
                                        <p style="width:600px" class="text-truncate fw-semibold fs-6 mb-0"><?php echo isset($item->document) ? $item->document->topic : '' ?> <?php echo (isset($item->document) && $item->document->isFile()) ? '<i class="fas fa-paperclip"></i>' : '' ?></p>
                                        </div>
                                        <span class="text-primary fw-normal fs-13">
                                        <i class="fa-solid fa-inbox"></i>
                                            <?php echo $item->documentOrg->title ?? '-'; ?>
                                        <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                                            <i class="fa-regular fa-eye"></i> <?php echo isset($item->document) ? $item->document->viewCount() : '' ?>
                                        </span>
                                    </span>
                            <?php echo Html::a(($item->bookmark() == 'Y' ? '<i class="fa-solid fa-star text-warning"></i>' : '<i class="fa-regular fa-star"></i>'), ['/me/documents/bookmark', 'id' => $item->id], ['class' => 'bookmark', 'id' => 'bookmark-' . $item->id]) ?>
                        </a>
                    </td>
                    <td><?=$item->data_json['committee_name'] ?? '-'?></td>
                    <td>
                        <?php echo isset($item->document) ? $item->document->StackDocumentTags('comment') : '' ?>
                    </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>