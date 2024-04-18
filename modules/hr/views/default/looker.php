<!-- <iframe class="rounded-4" width="100%" height="1000px"
    src="https://lookerstudio.google.com/embed/reporting/af9a1aff-8a43-495d-98ee-77fd3016d804/page/pHqpD"
    frameborder="0" style="border:0" allowfullscreen
    sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe> -->

    <iframe width="100%" height="3024" src="https://lookerstudio.google.com/embed/reporting/647472b8-d90b-4e08-abc0-80d79d0a9832/page/d81pD" frameborder="0" style="border:0" allowfullscreen sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe>
    
    
    
        <div class="card">
        <div class="card-body">
    
    
        </div>
    </div>
<!-- <div id="show-content"></div> -->

<?php
use yii\web\View;
$js = <<<JS

// $.ajax({
//     type: "get",
//     url: "https://lookerstudio.google.com/embed/reporting/647472b8-d90b-4e08-abc0-80d79d0a9832/page/d81pD",
//     // data: "data",
//     dataType: "json",
//     success: function (response) {
//         $('#show-content').html(response)
//     }
// });

JS;
$this->registerJS($js, View::POS_END)
    ?>