<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ApproveHelper;

/** @var yii\web\View $this */
$me = \app\components\UserHelper::GetEmployee();
$this->title = "การอนุมัติวันลา";
$this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;

$msg = 'ขอ';
?>


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check">
      <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
      <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
      <path d="m9 14 2 2 4-4" />
    </svg>
    <?= $this->title ?>
  </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php // $this->render('@app/components/ui/btnReturn') 
?>
<?php //  $this->render('@app/modules/approveV2/menu', ['active' => 'leave']) 
?>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/approveV2/tab_menu', [
  'menu' => 'leave'
]) ?>



<div class="card approve-v2-card">
  <div class="card-body">
    <div class="d-flex justify-content-between">
      <h6>เห็นชอบการลา <?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ</h6>

      <?php echo $this->render('@app/modules/approveV2/views/default/_search', 
      ['model' => $searchModel,
      'emp_label' => 'ผู้ขออนุมัติการลา',
      'approveAllUrl' => Url::to(['/approve-v2/leave/approve-all'])
      ]
      ) ?>

    </div>
    <div class="table-responsive" style="max-height: 600px;max-height: 600px;min-height:300px; overflow: auto;">
      <table class="table table-striped table-hover mb-0">
        <thead style="position: sticky; top: 0; z-index: 10;">
          <tr>
            <!-- Checkbox เลือกทั้งหมด -->
            <th class="text-center" style="width:30px">
              <input type="checkbox" id="check-all">
            </th>
            <th class="text-center" style="width:30px">ลำดับ</th>
            <th class="text-start" style="width: 165px;">สถานะ</th>
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th scope="col" style="width:100px">ประเภทเวร</th>
            <th>ประเภทการลา</th>
            <th style="width: 150px;">ระหว่างวันที่</th>
            <th class="text-start" scope="col">หน่วยงาน</th>
            <th scope="col" style="width: 127px;">ผู้อนุมัติ</th>
            <th class="text-start" style="width: 165px;">ความคืบหน้า</th>
            <th class="text-center" style="width:115px;">ดำเนินการ</th>
          </tr>
        </thead>
        <tbody class="align-middle table-group-divider">
          <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr class="">
              <td class="text-center">
                <input
                  type="checkbox"
                  class="check-item"
                  name="selected[]"
                  value="<?= $item->id ?>"
                  <?= ($item->status == 'Pending'  ? '' : 'disabled') ?>>
              </td>
              <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
              <td>
                <?= $item->viewApproveStatus() ?>
              </td>
              <td class="text-truncate" style="max-width: 230px;">
                <a href="<?php echo Url::to(['/me/leave/view', 'id' => $item->leave->id, 'title' => '<i class="fa-solid fa-calendar-plus"></i> แก้ไขวันลา']) ?>"
                  class="open-modal" data-size="modal-xl">
                  <?php echo  $item->leave->employee->getAvatar(false) ?>
                </a>
              </td>
              <td><?= $item->leave->work_shift_name ?></td>
              <td>
                <div class="fw-medium text-dark mb-1">
                  <?= $item->leave->leaveType?->title ?? '-' ?>
                  <span class="badge bg-primary-subtle text-primary border-0 ms-1 fw-normal">
                    <?= $item->leave->total_days ?> วัน
                  </span>
                </div>
                <div class="text-muted small text-truncate" style="max-width: 200px;" title="<?= $item->leave->data_json['reason'] ?>">
                  <?= $item->leave->data_json['reason'] ?>
                </div>
              </td>
              <td>
                <span class="text-dark fw-semibold small">
                  <?php echo $item->leave->showLeaveDate() ?>
              </td>
              </span>
              <td class="text-start text-truncate" style="max-width:150px;"><?php echo $item->leave->employee->departmentName() ?></td>
              <td><?php echo $item->leave->stackChecker() ?></td>
              <td class="fw-light align-middle text-start" style="width:150px;">
                <?php echo $item->leave->viewStatus(); ?>
                <?php echo ApproveHelper::viewStep('leave', $item->leave->id); ?>
              </td>

              <td class="text-center">
                <?= Html::a(
                  '<i class="fa-regular fa-circle-check"></i> ตรวจสอบ',
                  ['/approve-v2/leave/update', 'id' => $item->id],
                  [
                    'class' => 'btn btn-sm btn-outline-primary rounded-pill open-modal',
                    'data' => ['size' => 'modal-xl']
                  ]
                ) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
        <?= yii\bootstrap5\LinkPager::widget([
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
