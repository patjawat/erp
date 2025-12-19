<?php

use yii\helpers\Url;
use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use app\components\UserHelper;
$me = UserHelper::GetEmployee();

$this->title = 'ทะเบียนหนังสือ';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = ['label' => 'หนังสือ', 'url' => ['/me']];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
      <?= $to ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?php echo $this->render('@app/modules/me/views/documents/menu', ['action' => $action]) ?>
    <?= $this->render('@app/components/ui/btnReturn')?>
</div>
<?php $this->endBlock(); ?>

<?php if (!isset($list)): ?>
<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?php echo $this->render('_search', ['model' => $searchModel, 'action' => $action]); ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white"> <i class="bi bi-ui-checks"></i> ทะเบียนหนังสือ <span
                class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
            รายการ</h6>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-top align-items-center">
            <?php if (isset($list)): ?>
            <?= Html::a('แสดงทั้งหมด', ['/me/documents'], ['class' => 'btn btn-sm btn-light rounded-pill', 'data' => ['pjax' => 0]]) ?>
            <?php endif; ?>
        </div>
        <table class="table table-striped table-fixed">
            <thead>
                <tr>
                    <th class="text-center" style="width:50px;">ลำดับ</th>
                    <th class="text-center" style="min-width:100px; width:100px;">เลขที่รับ</th>
                    <th style="min-width:320px;">เรื่อง</th>
                    <th style="min-width:250px;">ผู้บันทึก</th>
                    <th style="min-width:130px;">สถานะ</th>
                    <th style="width:120px;">ลงความเห็น</th>
                </tr>
            </thead>
            <tbody class="align-middle  table-group-divider table-hover">
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                <td class="text-center">
                    <?php echo $item->doc_regis_number ?>
                </td>
                <td class="fw-light align-middle">
                    <div>
                        <h6 style="width:600px" class="text-truncate fs-6 mb-0">
                            <?php if ($item->doc_speed == 'ด่วนที่สุด'): ?>
                            <span class="badge text-bg-danger fs-13">
                                <i class="fa-solid fa-circle-exclamation"></i> ด่วนที่สุด
                            </span>
                            <?php endif; ?>

                            <?php if ($item->secret == 'ลับที่สุด'): ?>
                            <span class="badge text-bg-danger fs-13"><i class="fa-solid fa-lock"></i>
                                ลับที่สุด
                            </span>
                            <?php endif; ?>
                            <?php if (isset($item->documentTags)): ?>
                            <a href="<?php echo Url::to(['/me/documents/view', 'id' => $item->documentTags->id]) ?>"
                                class="open-modal" data-size="modal-xxl">
                                เรื่อง : <?php echo $item->topic ?>
                            </a>
                            <?php endif; ?>

                            <?php if (isset($item->documentDepartment)): ?>
                            <a href="<?php echo Url::to(['/me/documents/view', 'id' => $item->documentDepartment->id]) ?>"
                                class="open-modal" data-size="modal-xxl">
                                เรื่อง : <?php echo $item->topic ?>
                            </a>
                            <?php endif; ?>

                            <?php echo $item->isFile() ? '<i class="fas fa-paperclip"></i>' : '' ?>
                        </h6>
                    </div>
                    <p class="fw-normal fs-13 mb-0">
                        <?= $item->data_json['des'] ?? '' ?>
                    </p>
                    <?php // echo Html::img('@web/img/krut.png',['style' => 'width:20px']);
                        ?>
                    <span class="text-danger">
                        <?php echo $item->doc_number ?>
                    </span>
                    <span class="text-primary fw-normal fs-13">
                        |
                        <i class="fa-solid fa-inbox"></i>
                        <?php echo $item->documentOrg->title ?? '-'; ?>
                        <span class="badge rounded-pill badge-soft-secondary text-primary fw-lighter fs-13">
                            <i class="fa-regular fa-eye"></i> <?php echo $item->viewCount() ?>
                        </span>
                    </span>
                    <?php echo $item->StackDocumentTags('comment') ?>
                </td>
                <td class="fw-light align-middle">
                    <div class=" d-flex flex-column">
                        <?= $item->viewCreate()['avatar']; ?>
                    </div>
                </td>
                <td>
                    <?php if (isset($item->documentTags)): ?>
                    <?php echo Html::a(($item->documentTags->docRead('fs-3')['view']), ['/me/documents/bookmark', 'id' => $item->documentTags->id], ['class' => 'bookmark bookmark-star-'.$item->documentTags->id, 'id' => $item->documentTags->id]) ?>
                    <?php echo $item->documentStatus->title ?? '-' ?>
                    <?php endif; ?>

                    <?php if (isset($item->documentDepartment)): ?>
                    <?php echo Html::a(($item->documentDepartment->docRead('fs-3')['view']), ['/me/documents/bookmark', 'id' => $item->documentDepartment->id], ['class' => 'bookmark', 'id' => $item->documentDepartment->id]) ?>
                    <?php echo $item->documentDepartment->title ?? '-' ?>
                    <?php endif; ?>

                </td>
                <td>
                    <?php if (isset($item->documentTags)): ?>
                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>', ['view', 'id' => $item->documentTags->id],['class' => 'open-modal','data' => ['size' => 'modal-xxl']]) ?>
                    <?php endif; ?>
                    <?php if (isset($item->documentDepartment)): ?>
                    <?php echo Html::a('<i class="fa-regular fa-pen-to-square fa-2x"></i>', ['view', 'id' => $item->documentDepartment->id],['class' => 'open-modal','data' => ['size' => 'modal-xxl']]) ?>
                    <?php endif; ?>
                </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            <div class="text-muted">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'listOptions' => 'pagination pagination-sm',
                        'class' => 'pagination-sm',
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>