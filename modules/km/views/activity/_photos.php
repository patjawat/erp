<?php

use kartik\file\FileInput;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmActivity $activity */
/** @var bool $canManage */
?>
<div class="card border shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 fw-semibold mb-0"><i class="bi bi-images me-1"></i> คลังภาพ <span class="text-body-secondary fw-normal">(<?= count($activity->photos) ?>)</span></h2>
        </div>

        <?php if ($canManage): ?>
            <?= Html::beginForm(['upload-photos', 'id' => $activity->id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'mb-3']) ?>
                <?= FileInput::widget([
                    'name' => 'photos[]',
                    'options' => ['multiple' => true, 'accept' => 'image/*'],
                    'pluginOptions' => [
                        'showUpload' => false,
                        'showRemove' => false,
                        'showCaption' => false,
                        'browseLabel' => 'เลือกรูป',
                        'browseIcon' => '<i class="bi bi-image me-1"></i>',
                        'maxFileCount' => 20,
                        'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
                        'maxFileSize' => 8192,
                        'msgPlaceholder' => 'เลือกรูปกิจกรรม (เลือกได้หลายรูป)...',
                    ],
                ]) ?>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="form-text mb-0">ไฟล์ละไม่เกิน 8 MB · jpg/png/webp/gif</span>
                    <?= Html::submitButton('<i class="bi bi-upload me-1"></i> อัปโหลดรูปที่เลือก', ['class' => 'btn btn-primary btn-sm']) ?>
                </div>
            <?= Html::endForm() ?>
        <?php endif; ?>

        <?php if (!$activity->photos): ?>
            <div class="text-body-secondary small">ยังไม่มีรูปในกิจกรรมนี้</div>
        <?php else: ?>
            <div class="row g-2">
                <?php foreach ($activity->photos as $p): ?>
                    <?php $isCover = ((int) $activity->cover_photo_id === (int) $p->id); ?>
                    <div class="col-6 col-md-4">
                        <div class="position-relative border rounded overflow-hidden" style="aspect-ratio:1/1;background:#eef1f6">
                            <a href="<?= Url::to(['photo', 'id' => $p->id]) ?>" target="_blank" rel="noopener">
                                <img src="<?= Url::to(['photo', 'id' => $p->id, 'thumb' => 1]) ?>" alt="<?= Html::encode($p->caption ?: $p->file_name) ?>" style="width:100%;height:100%;object-fit:cover">
                            </a>
                            <?php if ($isCover): ?>
                                <span class="badge text-bg-warning position-absolute top-0 start-0 m-1"><i class="bi bi-star-fill me-1"></i>ปก</span>
                            <?php endif; ?>
                            <?php if ($canManage): ?>
                                <div class="position-absolute bottom-0 end-0 m-1 d-flex gap-1">
                                    <?php if (!$isCover): ?>
                                        <?= Html::beginForm(['set-cover', 'id' => $p->id], 'post', ['class' => 'd-inline']) ?>
                                            <?= Html::submitButton('<i class="bi bi-star"></i>', ['class' => 'btn btn-sm btn-light border', 'title' => 'ตั้งเป็นรูปปก']) ?>
                                        <?= Html::endForm() ?>
                                    <?php endif; ?>
                                    <?= Html::beginForm(['delete-photo', 'id' => $p->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ลบรูปนี้?');"]) ?>
                                        <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-light border text-danger', 'title' => 'ลบรูป']) ?>
                                    <?= Html::endForm() ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
