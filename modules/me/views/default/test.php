<h1>Test</h1>
<?php

use app\components\ApproveLevelResolver;
use app\modules\leave\components\LeaveApproveResolver;
  $resolvedLevels = ApproveLevelResolver::resolve('leave', 275);
 $rows = LeaveApproveResolver::buildApproveRows((int) 275, (string) 1);
echo "<pre>";
print_r($rows);
echo "</pre>";

echo "<pre>";
print_r($resolvedLevels);
echo "</pre>";

?>