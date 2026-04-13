<h1>Test</h1>
<?php

use app\components\ApproveLevelResolver;
use app\modules\leave\components\LeaveApproveResolver;
use yii\bootstrap5\Html;
  $resolvedLevels = ApproveLevelResolver::resolve('leave', 275);
 $rows = LeaveApproveResolver::buildApproveRows((int) 275, (string) 1);


?>
<div class="d-flex flex-column gap-3">
  <?= Html::a('React-App', ['/react'],['class' => 'btn btn-primary']) ?>
  <?= Html::a('Nest-API', ['/api'],['class' => 'btn btn-secondary']) ?>
</div>