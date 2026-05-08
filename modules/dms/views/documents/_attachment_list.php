<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Uploads;
use app\components\ThaiDateHelper;

/** @var app\modules\dms\models\Documents $model */

$files = Uploads::find()
    ->where(['ref' => $model->ref, 'name' => 'document_clip'])
    ->orderBy(['id' => SORT_DESC])
    ->all();

$iconByExt = function ($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $map = [
        'pdf'  => ['fa-file-pdf', 'text-danger'],
        'doc'  => ['fa-file-word', 'text-primary'],
        'docx' => ['fa-file-word', 'text-primary'],
        'xls'  => ['fa-file-excel', 'text-success'],
        'xlsx' => ['fa-file-excel', 'text-success'],
        'ppt'  => ['fa-file-powerpoint', 'text-warning'],
        'pptx' => ['fa-file-powerpoint', 'text-warning'],
        'png'  => ['fa-file-image', 'text-info'],
        'jpg'  => ['fa-file-image', 'text-info'],
        'jpeg' => ['fa-file-image', 'text-info'],
        'gif'  => ['fa-file-image', 'text-info'],
        'webp' => ['fa-file-image', 'text-info'],
        'zip'  => ['fa-file-zipper', 'text-secondary'],
        'rar'  => ['fa-file-zipper', 'text-secondary'],
    ];
    return $map[$ext] ?? ['fa-file', 'text-muted'];
};

$formatSize = function ($bytes) {
    if (!$bytes || $bytes <= 0) { return ''; }
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
    return number_format($bytes, $bytes >= 10 || $i === 0 ? 0 : 1) . ' ' . $units[$i];
};

$uploadDir = Yii::getAlias('@app/modules/filemanager/fileupload/');
?>

<?php if (empty($files)): ?>
    <div class="text-center py-5 text-muted">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width:56px;height:56px;">
            <i class="fa-solid fa-paperclip fs-4 opacity-50"></i>
        </div>
        <div class="small">ยังไม่มีไฟล์แนบ</div>
    </div>
<?php else: ?>
    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
        <?php foreach ($files as $f): ?>
            <?php
            list($icon, $colorClass) = $iconByExt($f->real_filename);
            $displayName = $f->file_name ?: $f->real_filename;
            $filePath = $uploadDir . $f->ref . '/' . $f->real_filename;
            $sizeBytes = file_exists($filePath) ? filesize($filePath) : 0;
            $sizeStr = $formatSize($sizeBytes);
            $thai = '';
            try {
                if (!empty($f->create_date)) {
                    $thai = ThaiDateHelper::formatThaiDate($f->create_date);
                }
            } catch (\Throwable $th) {
                $thai = $f->create_date ?? '';
            }
            $downloadUrl = Url::to(['/filemanager/uploads/get-image', 'id' => $f->id]);
            ?>
            <li>
                <a href="<?= $downloadUrl ?>"
                   download="<?= Html::encode($displayName) ?>"
                   class="d-flex align-items-center gap-3 p-3 rounded-3 border border-light-subtle bg-white text-decoration-none text-dark attachment-item">
                    <span class="d-inline-flex align-items-center justify-content-center bg-light rounded-3 flex-shrink-0" style="width:44px;height:44px;">
                        <i class="fa-solid <?= $icon ?> <?= $colorClass ?> fs-4"></i>
                    </span>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-truncate" title="<?= Html::encode($displayName) ?>"><?= Html::encode($displayName) ?></div>
                        <div class="small text-muted d-flex flex-wrap gap-2">
                            <?php if ($sizeStr): ?>
                                <span><?= Html::encode($sizeStr) ?></span>
                            <?php endif; ?>
                            <?php if ($thai): ?>
                                <span><i class="fa-regular fa-calendar me-1"></i><?= Html::encode($thai) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0" style="width:36px;height:36px;">
                        <i class="fa-solid fa-download"></i>
                    </span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
