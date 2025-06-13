<?php
use yii\web\View;

?>


<table id="document-table" class="display" style="width:100%; table-layout: fixed;"><table id="document-table" class="display" style="width:100%; table-layout: fixed;">
    <thead>
        <tr>
            <th>ชื่อเรื่อง</th>
        </tr>
    </thead>
</table>

<?php
$ajaxUrl = \yii\helpers\Url::to(['/dms/documents/list-topic-data']);
$js = <<<JS
$(document).ready(function() {

    $('#document-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '$ajaxUrl',
            type: 'GET'
        },
            columns: [
                {
                    data: null,
                    width: '10%',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return '<a href="#" class="btn-action" data-topic="' + row.topic.replace(/"/g, '&quot;') + '">'+row.topic+'</a>';
                    }
                }
            ]
    });

    // กำหนดการคลิกปุ่ม
    $('#document-table').on('click', '.btn-action', function(e) {
        e.preventDefault();
        const topic = $(this).data('topic');

        $('#documents-topic').val(topic)
        $("#main-modal").modal("toggle");
        // หรือจะ console.log หรือส่ง Ajax ก็ได้
        // console.log(topic);
    });
    });
JS;
$this->registerJs($js,View::POS_END);
?>
