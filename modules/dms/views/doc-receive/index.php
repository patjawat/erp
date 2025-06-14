<?php
use yii\helpers\Html;
$this->title = 'หนังสือสำนักงานสาธารณสุขจังหวัดเลย';
?>


<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-journal-text fs-4"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu') ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['active' => 'receive']) ?>
<?php $this->endBlock(); ?>


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
                <th scope="col">เลขที่หนังสือ</th>
                <th scope="col">ชื่อหนังสือ</th>
                <th scope="col">จาก</th>
                <th scope="col">วันที่ส่งให้</th>
                <th scope="col">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
<?php foreach($documentTemps as $item):?>
        <tr class="">
                <td scope="row"><?=$item['doc_number']?></td>
                <td><?=$item['topic']?></td>
                <td><?=$item['doc_from']?></td>
                <td><?=$item['doc_to']?></td>
                <td><?=Html::a('รับเข้า',['/dms/documents/create',
                'document_type' => 'DT1',
                'document_group' => 'receive',
                'doc_number' => $item['doc_number'],
                'doc_speed' => 'ปกติ',
                'secret' => 'ปกติ',
                'document_org' => $item['org_name'],
                'file_name' => $item['downloaded_file'],
                'topic' => $item['topic']
                ])?></td>
            </tr>
<?php endforeach;?>
        </tbody>
    </table>
</div>
</div>
</div>
