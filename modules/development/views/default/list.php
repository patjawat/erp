<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\development\models\Development;
use app\models\Categorise;

/** @var yii\web\View $this */
/** @var int $thaiYear */
/** @var app\modules\development\models\DevelopmentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'รายการกิจกรรมอบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวมอบรม/ประชุม/ดูงาน', 'url' => ['/development/default/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$developmentTypes = Categorise::find()
    ->where(['name' => 'development_type', 'active' => 1])
    ->orderBy(['title' => SORT_ASC])
    ->all();
$statusList = ['' => 'ทั้งหมด'] + ArrayHelper::map(
    Categorise::find()->where(['name' => 'leave_status', 'active' => 1])->orderBy(['sort' => SORT_ASC, 'title' => SORT_ASC])->all(),
    'code',
    'title'
);

$listThaiYear = Development::find()->select('thai_year')->distinct()->orderBy(['thai_year' => SORT_DESC])->column();
if (empty($listThaiYear)) {
    $listThaiYear = [(int) date('Y') + 543];
}

$totalCount = $dataProvider->getTotalCount();
$pagination = $dataProvider->pagination;
$pageSize = $pagination ? (int) $pagination->pageSize : 20;
$currentPage = $pagination ? (int) $pagination->page : 0;
$totalPages = $pagination && $pageSize > 0 ? (int) ceil($totalCount / $pageSize) : 1;
$from = $totalCount > 0 ? ($currentPage * $pageSize + 1) : 0;
$to = min($currentPage * $pageSize + $pageSize, $totalCount);
?>
<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap gap-2 align-items-center">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>ย้อนกลับก่อนหน้า', Yii::$app->request->referrer ?: ['/development/default/dashboard'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
    <?= $this->render('_menu', ['active' => 'dashboard']) ?>
</div>
<?php $this->endBlock(); ?>

<div class="development-list development-list-modern">


    <!-- Filter: card ค้นหา -->
    <?php
    $hasFilter = (string)$searchModel->topic !== '' || (string)$searchModel->development_type_id !== '' || (string)$searchModel->status !== '' || (string)$searchModel->date_start_from !== '' || (string)$searchModel->date_start_to !== '' || (string)$searchModel->emp_id !== '';
    $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['/development/default/list'],
        'options' => ['class' => 'development-list-filter-form mb-4', 'id' => 'development-list-filter-form'],
        'fieldConfig' => [
            'options' => ['class' => 'mb-0'],
            'inputOptions' => ['class' => 'form-control'],
            'labelOptions' => ['class' => 'form-label small text-muted mb-1'],
        ],
    ]);
    echo Html::hiddenInput('thai_year', $thaiYear);
    ?>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-12 col-xl-12">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-0">
        <div class="card-body p-4">

            <!-- Header: title + year -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-body mb-0 d-flex align-items-center gap-2">
            <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3"><i class="bi bi-calendar-event"></i></span>
            <?= Html::encode($this->title) ?>
        </h4>
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-2 mb-0 d-inline-block">ปีงบประมาณ <?= (int) $thaiYear ?></span>
    </div>

            <!-- Search bar: full width, rounded, with icon -->
            <div class="mb-4">
                <div class="input-group input-group-lg rounded-pill border border-2 border-light shadow-sm">
                    <span class="input-group-text border-0 bg-light rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                    <?= $form->field($searchModel, 'topic', [
                        'template' => '{input}',
                        'options' => ['class' => 'flex-grow-1'],
                    ])->textInput([
                        'placeholder' => 'ค้นหาหัวข้อกิจกรรม บริษัท/หน่วยงาน หรือคำสำคัญ',
                        'class' => 'form-control form-control-lg border-0 bg-light rounded-0 rounded-end-pill',
                    ]) ?>
                </div>
            </div>
            <!-- Filter row: บุคลากร, ประเภท, สถานะ, วันที่ -->
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $searchModel, 'label' => 'ค้นหาบุคลากร', 'placeholder' => 'บุคลากร']) ?>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <?= $form->field($searchModel, 'development_type_id')->dropDownList(
                        ['' => 'ทุกประเภท'] + ArrayHelper::map($developmentTypes, 'code', 'title'),
                        ['class' => 'form-select rounded-pill']
                    )->label('ประเภท') ?>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <?= $form->field($searchModel, 'status')->dropDownList($statusList, ['class' => 'form-select rounded-pill'])->label('สถานะ') ?>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <?= $form->field($searchModel, 'date_start_from')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                        'options' => ['placeholder' => 'เริ่มจากวันที่', 'class' => 'form-control rounded-pill'],
                    ])->label('วันที่เริ่มตั้งแต่') ?>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <?= $form->field($searchModel, 'date_start_to')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                        'options' => ['placeholder' => 'ถึงวันที่', 'class' => 'form-control rounded-pill'],
                    ])->label('ถึงวันที่') ?>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-outline-primary rounded-pill"><i class="bi bi-funnel me-1"></i> กรอง</button>
                    <?= Html::a('ล้างตัวกรอง', ['/development/default/list', 'thai_year' => $thaiYear], ['class' => 'btn btn-outline-secondary rounded-pill']) ?>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <!-- Summary: แสดง X จาก Y รายการ • หน้า ... -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-3">
        <p class="text-muted small mb-0">
            แสดง <strong><?= $totalCount > 0 ? $from : 0 ?></strong>–<strong><?= $to ?></strong> จาก <strong><?= number_format($totalCount) ?></strong> รายการ
            <?php if ($totalPages > 1): ?>
            • หน้า <?= $currentPage + 1 ?> จาก <?= $totalPages ?>
            <?php endif; ?>
        </p>
        <div class="d-flex align-items-center gap-2">
            <?php
            $exportUrl = Url::to(array_merge(['/development/default/export-excel'], [
                'thai_year' => $thaiYear,
                'DevelopmentSearch' => [
                    'topic' => $searchModel->topic,
                    'development_type_id' => $searchModel->development_type_id,
                    'status' => $searchModel->status,
                    'date_start_from' => $searchModel->date_start_from,
                    'date_start_to' => $searchModel->date_start_to,
                ],
            ]));
            ?>
            <a href="<?= Html::encode($exportUrl) ?>" class="btn btn-success btn-sm rounded-pill">
                <i class="bi bi-file-earmark-excel me-1"></i>ส่งออก Excel
            </a>
            <div class="dropdown">
                <button class="btn btn-light border rounded-pill dropdown-toggle btn-sm" type="button" id="dropdownYearList" data-bs-toggle="dropdown" aria-expanded="false">
                    ปีงบประมาณ <?= (int) $thaiYear ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownYearList">
                    <?php foreach ($listThaiYear as $y): ?>
                    <li><a class="dropdown-item" href="<?= Url::to(['/development/default/list', 'thai_year' => $y]) ?>">ปี <?= (int) $y ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Card list -->
    <div class="development-activity-cards">
        <?php foreach ($dataProvider->getModels() as $item): ?>
        <?php
            $emp = $item->emp;
            $avatarUrl = $emp && method_exists($emp, 'ShowAvatar') ? $emp->ShowAvatar() : null;
            $requesterName = $emp ? trim(($emp->fname ?? '') . ' ' . ($emp->lname ?? '')) : (string) $item->emp_id;
            if ($requesterName === '') {
                $requesterName = '-';
            }
            $initials = mb_strlen($requesterName) >= 2 ? mb_substr($requesterName, 0, 2) : ($requesterName !== '-' ? mb_substr($requesterName, 0, 1) : '?');
            $typeTitle = $item->developmentType ? $item->developmentType->title : '-';
            $timeAgo = !empty($item->created_at) ? (AppHelper::timeDifference($item->created_at) . 'ที่ผ่านมา') : '';
        ?>
        <div class="card border-0 shadow-sm rounded-4 mb-3 development-activity-card">
            <div class="card-body p-4">
                <div class="d-flex gap-4 align-items-start">
                    <!-- Avatar: ผู้ขอไปราชการ -->
                    <div class="development-card-avatar flex-shrink-0">
                        <?php if ($avatarUrl): ?>
                        <img src="<?= Html::encode($avatarUrl) ?>" alt="" class="rounded-4" width="72" height="72" style="object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="development-card-avatar-initials rounded-4" style="display: none;" aria-hidden="true"><?= Html::encode($initials) ?></div>
                        <?php else: ?>
                        <div class="development-card-avatar-initials rounded-4"><?= Html::encode($initials) ?></div>
                        <?php endif; ?>
                    </div>
                    <!-- Content -->
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h5 class="text-primary mb-0 development-card-topic"><?= Html::a(Html::encode($item->topic), ['/development/default/view', 'id' => $item->id], ['class' => 'text-primary text-decoration-none']) ?></h5>
                            <?php if ($timeAgo !== ''): ?>
                            <span class="text-muted small flex-shrink-0"><i class="bi bi-clock me-1"></i><?= Html::encode($timeAgo) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small mb-2"><?= Html::encode($requesterName) ?></p>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge bg-light text-body border border-1 border-secondary border-opacity-25 rounded-pill px-3 py-2"><?= Html::encode($typeTitle) ?></span>
                            <?php echo $item->getStatusHtml(); ?>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                            <span><i class="bi bi-calendar3 me-1"></i> <?= ThaiDateHelper::formatThaiDate($item->date_start, 'short') ?> – <?= ThaiDateHelper::formatThaiDate($item->date_end, 'short') ?></span>
                            <?php $provinceName = isset($item->data_json['province_name']) ? trim((string) $item->data_json['province_name']) : ''; if ($provinceName !== ''): ?>
                            <span><i class="bi bi-geo-alt me-1"></i> จังหวัดที่ไป: <?= Html::encode($provinceName) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <?= Html::a('<i class="bi bi-eye me-1"></i> ดูรายละเอียด', ['/development/default/view', 'id' => $item->id], [
                                'class' => 'btn btn-sm btn-outline-primary rounded-pill',
                                'title' => 'ดูรายละเอียด',
                            ]) ?>
                            <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์ใบขอไปราชการ', ['/development/default/print-official', 'id' => $item->id], [
                                'class' => 'btn btn-sm btn-outline-secondary rounded-pill',
                                'target' => '_blank',
                                'title' => 'พิมพ์ใบขอไปราชการ',
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if ($dataProvider->getCount() === 0): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body py-5 text-center text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                <p class="mb-0">ยังไม่มีข้อมูลกิจกรรมในปีนี้</p>
                <p class="small mb-0">ลองเปลี่ยนปีงบประมาณหรือล้างตัวกรอง</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($dataProvider->getCount() > 0 && $totalPages > 1): ?>
    <div class="d-flex justify-content-center mt-4">
        <?= \yii\bootstrap5\LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'options' => ['class' => 'pagination pagination-lg'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link rounded-pill mx-1'],
        ]) ?>
    </div>
    <?php endif; ?>
</div>

<style>
.development-list-modern .development-card-avatar {
    width: 72px;
    height: 72px;
}
.development-list-modern .development-card-avatar img,
.development-list-modern .development-card-avatar-initials {
    width: 72px;
    height: 72px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}
.development-list-modern .development-activity-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.08) !important;
}
</style>
