<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\ThaiDateHelper;
$this->title = 'หนังสือสำนักงานสาธารณสุขจังหวัดเลย';
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  // echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php  // echo $this->render('@app/modules/dms/menu',['active' => 'receive']) ?>
<?php $this->endBlock(); ?>

<style>
  /* ปรับแต่ง Modal ให้ดูเบาบาง */

  
  /* ปรับแต่ง List Item ให้เหมือน Card เล็กๆ */
  .doc-item-link {
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    border-radius: 12px;
    margin-bottom: 8px;
    border: 1px solid #f0f0f0 !important;
  }
  
  .doc-item-link:hover {
    background-color: #f8faff !important;
    border-color: #d1d9ff !important;
    transform: translateY(-2px);
  }

  /* ไอคอนวงกลมด้านหน้า */
  .doc-icon {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2f7;
    border-radius: 12px;
    color: #5e72e4;
    font-size: 1.2rem;
  }

  /* Badge ความเร็วหนังสือแบบ Minimal */
  .badge-minimal {
    font-weight: 500;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  .speed-urgent { background-color: #fff5f5; color: #ff4d4d; }
  .speed-normal { background-color: #f0f7ff; color: #007bff; }
</style>
<?php
$waitingList = $documentTemps;
?>

<div class="d-flex justify-content-between align-items-center mb-4 px-2">
    <h5 class="mb-0 fw-bold">รายการหนังสือเข้าใหม่</h5>
    <span class="badge bg-primary-soft text-primary rounded-pill px-3">
        <?= count($waitingList) ?> ฉบับ
    </span>
</div>

<div class="list-group list-group-flush border-0">
    <?php foreach ($waitingList as $index => $item): 
        $content = $item['content'];
        $file = $item['attachment_info'];
        $isUrgent = ($content['doc_speed'] ?? '') == 'ด่วนที่สุด';
    ?>
    <a href="<?= Url::to(['/dms/documents/create',
                'document_type' => 'DT1',
                'document_group' => 'receive',
                'document_org' => $content['hoscode'],
                'doc_number' => $content['doc_number'],
                'doc_date' => $content['doc_date'],
                'doc_speed' => 'ปกติ',
                'secret' => 'ปกติ',
                'topic' => $content['topic'],
                'file_name' => $file['filename'],
                'request_id' => $content['request_id'],
                'hosname' => $content['hosname'],
                'hoscode' => $content['hoscode']
])?>" class="list-group-item doc-item-link d-flex align-items-center p-3 open-modal" data-size="modal-fullscreen">

        <div class="doc-icon me-3">
            <i class="bi bi-file-earmark-text"></i> </div>

        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="mb-1 fw-bold text-dark text-truncate" style="max-width: 350px;">
                    <?= ($content['topic']) ?>
                </h6>
                <span class="badge badge-minimal <?= $isUrgent ? 'speed-urgent' : 'speed-normal' ?>">
                    <?= $content['doc_speed'] ?? 'ปกติ' ?>
                </span>
            </div>
            
            <div class="d-flex align-items-center mt-1">
                <small class="text-muted d-flex align-items-center me-3">
                    <i class="bi bi-building me-1"></i> <?= ($content['form_org_name']) ?>
                </small>
                <small class="text-muted">
                    <i class="bi bi-hash me-1"></i> <?= ($content['doc_number']) ?>
                </small>
            </div>
            
            <div class="mt-2" style="font-size: 0.75rem; color: #adb5bd;">
                <i class="bi bi-clock-history me-1"></i> ได้รับเมื่อ: <?= $item['received_at'] ?>
            </div>
        </div>

        <div class="ms-3 text-muted">
            <i class="bi bi-chevron-right"></i>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($waitingList)): ?>
    <div class="text-center py-5">
        <img src="https://cdn-icons-png.flaticon.com/512/6108/6108830.png" width="80" style="opacity: 0.3;">
        <p class="mt-3 text-muted">ไม่มีหนังสือรอรับในขณะนี้</p>
    </div>
<?php endif; ?> 
<!-- 
<div class="card">
    <div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">รายการหนังสือรอรับ</h6>
</div>
<div
    class="table-responsive"
>
    <table
        class="table"
    >
        <thead>
            <tr>
                <th scope="col">วันที่ส่ง</th>
                <th scope="col">เลขที่หนังสือ</th>
                <th scope="col">ชื่อหนังสือ</th>
                <th scope="col">จาก</th>
                <th scope="col">วันที่ส่งให้</th>
                <th scope="col">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
<?php foreach($documentTemps as $data):?>
    <?php $item = $data['content']?>
    <?php $item2 = $data['attachment_info']?>
        <tr class="">
                <td scope="row"><?php // ThaiDateHelper::formatThaiDate($item['send_date']);?></td>
                <td scope="row"><?=$item['doc_number']?></td>
                <td><?=$item['topic']?></td>
                <td><?php // $item['doc_from']?></td>
                <td><?php // $item['doc_to']?></td>
                <td>
                    <?=Html::a('รับเข้า',['/dms/documents/create',
                'document_type' => 'DT1',
                'document_group' => 'receive',
                'doc_number' => $item['doc_number'],
                'doc_speed' => 'ปกติ',
                'secret' => 'ปกติ',
                'document_org' => $item['form_org_name'],
                'file_name' => $item2['filename'],
                'topic' => $item['topic']
],['class' => 'btn btn-sm btn-primary'])?>
            | 
        <?php // Html::a('ลบ',['/dms/doc-receive/delete','id' => $item['no']],['class' => 'btn btn-sm btn-danger delete-item'])?>
        </td>
            </tr>
<?php endforeach;?>
        </tbody>
    </table>
</div>
</div>
</div> -->
