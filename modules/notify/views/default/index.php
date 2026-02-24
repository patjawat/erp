<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use yii\web\View;
use app\modules\notify\models\Notify;

$this->title = 'แจ้งเตือน';
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$totalCount = $dataProvider->getTotalCount();
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-white bg-opacity-25 rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i data-lucide="bell" class="text-white" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-bold">แจ้งเตือน</h5>
                <p class="mb-0 small opacity-75">การขออนุมัติลา จัดซื้อ ลงเวลาเข้างาน และอื่นๆ</p>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?= Html::a('ส่งแจ้งเตือนทดสอบ', ['send-test-form', 'title' => 'ส่งแจ้งเตือนทดสอบ'], ['class' => 'btn btn-outline-light rounded-3 px-3 open-modal', 'data' => ['size' => 'modal-md']]) ?>
            <?php if ($unreadCount > 0): ?>
                <?= Html::a('ทำเครื่องหมายอ่านทั้งหมด', ['mark-all-read'], ['class' => 'btn btn-light rounded-3 px-3']) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($unreadCount > 0): ?>
            <p class="text-muted small mb-3">
                <span class="badge bg-primary rounded-pill"><?= (int) $unreadCount ?></span> รายการยังไม่อ่าน
            </p>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['method' => 'get', 'options' => ['class' => 'row g-2 mb-3']]); ?>
            <div class="col-auto">
                <?= Html::activeDropDownList($searchModel, 'type', array_merge(['' => 'ทั้งหมด'], Notify::typeLabels()), ['class' => 'form-select form-select-sm']) ?>
            </div>
            <div class="col-auto">
                <?= Html::activeDropDownList($searchModel, 'read_at', ['' => 'ทั้งหมด', 'unread' => 'ยังไม่อ่าน', 'read' => 'อ่านแล้ว'], ['class' => 'form-select form-select-sm']) ?>
            </div>
            <div class="col-auto">
                <?= Html::submitButton('ค้นหา', ['class' => 'btn btn-primary btn-sm rounded-3']) ?>
            </div>
        <?php ActiveForm::end(); ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">สถานะ</th>
                        <th class="text-nowrap">ประเภท</th>
                        <th>หัวข้อ</th>
                        <th class="text-nowrap">วันที่</th>
                        <th class="text-nowrap text-end">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php if (empty($models)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">ยังไม่มีรายการแจ้งเตือน</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($models as $model): ?>
                            <tr>
                                <td class="text-nowrap align-middle">
                                    <?php if ($model->read_at): ?>
                                        <span class="text-muted small"><?= Yii::$app->formatter->asDatetime($model->read_at, 'php:d/m/Y H:i') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill">ยังไม่อ่าน</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap align-middle">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= Html::encode($model->getTypeLabel()) ?></span>
                                </td>
                                <td class="align-middle">
                                    <?= Html::a(Html::encode($model->title), ['view', 'id' => $model->id], ['class' => 'text-decoration-none text-dark' . ($model->read_at ? '' : ' fw-bold')]) ?>
                                </td>
                                <td class="text-nowrap align-middle text-muted small">
                                    <?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i') ?>
                                </td>
                                <td class="text-nowrap align-middle text-end">
                                    <?= Html::a('<i data-lucide="eye" class="icon-sm"></i>', ['view', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary rounded-3',
                                        'title' => 'ดู',
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $sendTestUrl = Url::to(['/notify/default/send-test']);
        $this->registerJs(<<<JS
\$(document).off('submit','#notify-send-test-form').on('submit','#notify-send-test-form',function(e){
  e.preventDefault();
  var form=this;
  var btn=form.querySelector('#notify-send-test-btn');
  if(btn){btn.disabled=true;btn.textContent='กำลังส่ง...';}
  var type=form.querySelector('#notify-type').value;
  if(!type){
    if(btn){btn.disabled=false;btn.textContent='ส่งแจ้งเตือนทดสอบ';}
    alert('กรุณาเลือกประเภทแจ้งเตือน');
    return;
  }
  var formData=new FormData(form);
  var params=new URLSearchParams(formData);
  var url='{$sendTestUrl}'+(params.toString()?'?'+params.toString():'');
  fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json();}).then(function(res){
    if(res.success){
      if(\$('#main-modal').length)\$('#main-modal').modal('hide');
      if(typeof Swal!=='undefined')Swal.fire({icon:'success',title:res.message||'ส่งแล้ว',timer:1500,showConfirmButton:false});
      window.location.reload();
    }else{
      if(btn){btn.disabled=false;btn.textContent='ส่งแจ้งเตือนทดสอบ';}
      alert(res.message||'ส่งไม่สำเร็จ');
    }
  }).catch(function(){
    if(btn){btn.disabled=false;btn.textContent='ส่งแจ้งเตือนทดสอบ';}
    alert('เกิดข้อผิดพลาด');
  });
});
JS
        , View::POS_END);
        ?>
        <?php if ($pagination->getPageCount() > 1 || $totalCount > 0): ?>
            <div class="d-flex justify-content-between align-items-center py-2">
                <div class="text-muted small">
                    แสดง <?= $pagination->getOffset() + 1 ?> - <?= min($pagination->getOffset() + $pagination->getLimit(), $totalCount) ?> จาก <?= $totalCount ?> รายการ
                </div>
                <?= LinkPager::widget([
                    'pagination' => $pagination,
                    'options' => ['class' => 'pagination pagination-sm mb-0'],
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
