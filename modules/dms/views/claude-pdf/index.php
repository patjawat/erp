
<!-- views/document/index.php -->
<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\helpers\StampHelper;

$this->title = 'ระบบสารบรรณ';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="document-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row mb-3">
        <div class="col-md-6">
            <?= Html::a('อัพโหลดเอกสารใหม่', ['upload-with-stamp'], [
                'class' => 'btn btn-success'
            ]) ?>
            <?= Html::a('อัพโหลดหลายไฟล์', ['bulk-upload'], [
                'class' => 'btn btn-info'
            ]) ?>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <?= Html::a('ส่งออก Excel', ['export', 'format' => 'excel'], [
                    'class' => 'btn btn-outline-primary'
                ]) ?>
                <?= Html::a('ส่งออก PDF', ['export', 'format' => 'pdf'], [
                    'class' => 'btn btn-outline-danger'
                ]) ?>
            </div>
        </div>
    </div>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            
            [
                'attribute' => 'document_number',
                'label' => 'เลขที่เอกสาร',
                'format' => 'text',
                'headerOptions' => ['style' => 'width: 150px;'],
            ],
            
            [
                'attribute' => 'topic',
                'label' => 'ชื่อเรื่อง',
                'format' => 'text',
                'value' => function($model) {
                    return $model->	topic ?: 'ไม่ระบุ';
                }
            ],
            
            [
                'attribute' => 'department',
                'label' => 'หน่วยงาน',
                'format' => 'text',
                'headerOptions' => ['style' => 'width: 200px;'],
            ],
            
            [
                'attribute' => 'doc_transactions_date',
                'label' => 'วันที่รับ',
                'format' => 'datetime',
                'filter' => Html::input('date', 'DocumentSearch[dateFrom]', $searchModel->dateFrom, [
                    'class' => 'form-control',
                    'placeholder' => 'วันที่เริ่มต้น'
                ]) . '<br>' . 
                Html::input('date', 'DocumentSearch[dateTo]', $searchModel->dateTo, [
                    'class' => 'form-control',
                    'placeholder' => 'วันที่สิ้นสุด'
                ]),
                'headerOptions' => ['style' => 'width: 180px;'],
                'value' => function($model) {
                    // return $model->doc_transactions_date ? Yii::$app->formatter->asDatetime($model->doc_transactions_date) : '';
                }
            ],
            
            [
                // 'attribute' => 'file_size',
                'label' => 'ขนาดไฟล์',
                'format' => 'text',
                'headerOptions' => ['style' => 'width: 100px;'],
                'value' => function($model) {
                    // return $model->file_size ? StampHelper::formatBytes($model->file_size) : '';
                }
            ],
            
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {download} {download-original} {delete}',
                'headerOptions' => ['style' => 'width: 150px;'],
                'buttons' => [
                    'download' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-download"></i>', 
                            ['download', 'id' => $model->id, 'type' => 'stamped'], [
                            'title' => 'ดาวน์โหลดไฟล์ที่มีตราประทับ',
                            'class' => 'btn btn-sm btn-success',
                            'data-pjax' => '0'
                        ]);
                    },
                    'download-original' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-file-pdf"></i>', 
                            ['download', 'id' => $model->id, 'type' => 'original'], [
                            'title' => 'ดาวน์โหลดไฟล์ต้นฉบับ',
                            'class' => 'btn btn-sm btn-info',
                            'data-pjax' => '0'
                        ]);
                    },
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'title' => 'ดูรายละเอียด',
                            'class' => 'btn btn-sm btn-primary',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'title' => 'ลบ',
                            'class' => 'btn btn-sm btn-danger',
                            'data' => [
                                'confirm' => 'คุณแน่ใจหรือไม่ที่จะลบเอกสารนี้?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>


<?php
// 4. ตัวอย่าง JavaScript สำหรับ AJAX Upload
$this->registerJs("
// AJAX Upload Function
function uploadFileAjax(fileInput) {
    if (!fileInput.files || fileInput.files.length === 0) return;
    
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('pdf_file', file);
    
    // แสดง Progress Bar
    const progressBar = $('#upload-progress');
    progressBar.show();
    
    $.ajax({
        url: '" . \yii\helpers\Url::to(['ajax-upload']) . "',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    const percentComplete = evt.loaded / evt.total * 100;
                    progressBar.find('.progress-bar').css('width', percentComplete + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            if (response.success) {
                $('#file-info').html(`
                    <div class=\"alert alert-success\">
                        <strong>อัพโหลดสำเร็จ!</strong><br>
                        ไฟล์: \${response.data.fileName}<br>
                        ขนาด: \${response.data.fileSize}
                    </div>
                `);
            } else {
                $('#file-info').html(`
                    <div class=\"alert alert-danger\">
                        <strong>เกิดข้อผิดพลาด!</strong><br>
                        \${response.message}
                    </div>
                `);
            }
        },
        error: function() {
            $('#file-info').html(`
                <div class=\"alert alert-danger\">
                    <strong>เกิดข้อผิดพลาด!</strong><br>
                    ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้
                </div>
            `);
        },
        complete: function() {
            progressBar.hide();
        }
    });
}

// Event Handler
$('#pdf-file-input').on('change', function() {
    uploadFileAjax(this);
});
");
?>