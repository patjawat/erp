<?php

use app\modules\hr\models\Employees;

$leader = null;

if ($node->data_json) {
    $json = json_decode($node->data_json, true);
    $leader = Employees::findOne($json['leader_1'] ?? null);
}

?>

<span class="d-flex align-items-center gap-2">

    <?php if ($leader): ?>
        <img src="<?= $leader->ShowAvatar() ?>"
             width="24"
             height="24"
             class="rounded-circle">
    <?php endif; ?>

    <span>
        <?= $node->name ?>

        <?php if ($leader): ?>
            <small class="text-muted">
                (<?= $leader->fullname ?>)
            </small>
        <?php endif; ?>
    </span>

</span>