<?php
use yii\helpers\Url;
?>
<div class="row">
<div class="col-12">
    
<iframe src="<?= Url::to(['/dms/documents/show','ref' => $model->ref]);?>&embedded=true"
width='100%' height='800px' frameborder="0"></iframe>

</div>
</div>