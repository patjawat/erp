<?php
use app\modules\iacRisk\services\ContextService;
use app\modules\iacRisk\services\AccessService;
use yii\helpers\Html;

$query = ContextService::query($context ?? []);
$access = new AccessService();
$items = [
    'overview' => ['ภาพรวม', '/iac-risk/default/index'],
    'service-profile' => ['Service Profile', '/iac-risk/default/service-profile'],
    'processes' => ['กระบวนงาน', '/iac-risk/default/processes'],
    'csa' => ['CSA', '/iac-risk/default/csa'],
    'risks' => ['บัญชีความเสี่ยง', '/iac-risk/default/risks'],
    'pk1' => ['ปค.1', '/iac-risk/default/pk1'],
    'pk4' => ['ปค.4', '/iac-risk/default/pk4'],
    'pk5' => ['ปค.5', '/iac-risk/default/pk5'],
    'reports' => ['ส่งรายงาน', '/iac-risk/default/reports'],
    'tracking' => ['ติดตามผล', '/iac-risk/default/tracking'],
    'history' => ['ประวัติ', '/iac-risk/default/history'],
];
if(!$access->canViewOrganizationDocuments())unset($items['pk1']);
if(!$access->canUseReportSubmission())unset($items['reports']);
?>
<nav class="d-flex flex-wrap gap-2" aria-label="เมนู IAC&Risk">
<?php foreach ($items as $key => [$label, $route]): ?>
    <?php
    $options = [
        'class' => 'btn btn-sm ' . (($active ?? '') === $key ? 'btn-primary' : 'btn-outline-secondary'),
        'aria-current' => ($active ?? '') === $key ? 'page' : null,
    ];
    ?>
    <?= Html::a(Html::encode($label), array_merge([$route], $query), $options) ?>
<?php endforeach; ?>
</nav>
