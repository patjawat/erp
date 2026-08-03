<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $model */

$this->title = $model->fullname;
$isRequestedManagedProfile = ($profileMode ?? null) === 'manager';
$this->params['breadcrumbs'][] = $isRequestedManagedProfile
    ? ['label' => 'กลุ่มงานของฉัน', 'url' => ['/me']]
    : ['label' => 'ทะเบียนบุคลากร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
// หมายเหตุ: ต้อง output CSS นี้เป็น <style> ภายใน Pjax container (ดูด้านล่าง)
// ไม่ใช้ registerCss เพราะ jquery-pjax ไม่ re-inject <style> ที่อยู่ใน <head>
// เมื่อเข้าหน้านี้ผ่าน pjax (จากหน้า list) ทำให้เมนูไม่มีสไตล์/เพี้ยน
$profileNavCss = <<<CSS
.profile-section-nav{display:grid;gap:.65rem;margin-bottom:1rem}
.profile-nav-group{overflow:hidden;background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:10px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
.profile-nav-group__summary{display:flex;align-items:center;min-height:58px;gap:.7rem;padding:.7rem .8rem;cursor:pointer;list-style:none;color:#1a202c;background:#eef2f7}
.profile-nav-group__summary::-webkit-details-marker{display:none}
.profile-nav-group__summary:hover{background:#e7edf5}
.profile-nav-group__summary:focus-visible,.profile-nav-item:focus-visible{outline:3px solid rgba(13,110,253,.18);outline-offset:-3px}
.profile-nav-group__icon{display:grid;place-items:center;width:36px;height:36px;flex:0 0 36px;color:#334155;background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:8px}
.profile-nav-group__icon svg{width:17px;height:17px}
.profile-nav-group__copy,.profile-nav-item__copy{min-width:0;flex:1}
.profile-nav-group__title,.profile-nav-item__title{display:block;color:#1a202c;font-size:.86rem;font-weight:600;line-height:1.3}
.profile-nav-group__title{font-size:.9rem}
.profile-nav-group__subtitle,.profile-nav-item__subtitle{display:block;margin-top:.12rem;overflow:hidden;color:#718096;font-size:.72rem;line-height:1.3;text-overflow:ellipsis;white-space:nowrap}
.profile-nav-group__chevron{width:16px;height:16px;flex:0 0 16px;color:#718096;transition:transform 180ms cubic-bezier(.16,1,.3,1)}
.profile-nav-group[open] .profile-nav-group__chevron{transform:rotate(180deg)}
.profile-nav-group__items{position:relative;padding:.45rem .45rem .55rem 3rem;background:#fff;border-top:1px solid rgba(15,23,42,.08)}
.profile-nav-group__items::before{position:absolute;top:.7rem;bottom:.8rem;left:1.7rem;width:1px;background:rgba(100,116,139,.28);content:""}
.profile-nav-item{display:flex;align-items:center;width:100%;min-height:44px;gap:.5rem;padding:.4rem .5rem;color:#1a202c;border-radius:8px;text-decoration:none}
.profile-nav-item:hover{color:#1a202c;background:#f1f5f9}
.profile-nav-item.is-active{color:#0a58ca;background:#f7f9fc;box-shadow:inset 0 0 0 1px rgba(13,110,253,.22)}
.profile-nav-item.is-active .profile-nav-item__title,.profile-nav-item.is-active .profile-nav-item__icon{color:#0a58ca}
.profile-nav-item__icon{display:grid;place-items:center;width:20px;height:20px;flex:0 0 20px;color:#718096}
.profile-nav-item__icon svg{width:14px;height:14px}
.profile-nav-item__count{min-width:24px;padding:.15rem .4rem;color:#4a5568;background:#eef2f7;border-radius:999px;font-size:.7rem;font-variant-numeric:tabular-nums;text-align:center}
.profile-nav-item__count.is-alert{color:#fff;background:#b91c1c;font-weight:700}
.profile-nav-item.is-coming-soon{cursor:default}
.profile-nav-item.is-coming-soon:hover{background:transparent}
.profile-nav-item.is-coming-soon .profile-nav-item__title,.profile-nav-item.is-coming-soon .profile-nav-item__icon{color:#475569}
.profile-nav-item__status{flex:0 0 auto;padding:.16rem .42rem;color:#475569;background:#eef2f7;border:1px solid rgba(100,116,139,.16);border-radius:999px;font-size:.62rem;font-weight:600;line-height:1.2;white-space:nowrap}
.managed-profile-context{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem;padding:.9rem 1rem;border:1px solid var(--bs-primary-border-subtle);border-left:4px solid var(--bs-primary);border-radius:12px;background:var(--bs-primary-bg-subtle)}
.managed-profile-context__label{display:flex;align-items:center;gap:.75rem;color:var(--bs-emphasis-color)}
.managed-profile-context__icon{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;border-radius:10px;color:var(--bs-primary);background:var(--bs-body-bg)}
.managed-profile-context__eyebrow{display:block;color:var(--bs-primary-text-emphasis);font-size:.72rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
.managed-profile-context__title{margin:0;font-size:.95rem;font-weight:700}
.managed-profile-card{overflow:hidden;border:1px solid var(--bs-border-color);border-radius:12px;background:var(--bs-body-bg);box-shadow:var(--bs-box-shadow-sm)}
.managed-profile-card__head{display:flex;align-items:center;gap:1rem;padding:1rem;border-bottom:1px solid var(--bs-border-color);background:var(--bs-tertiary-bg)}
.managed-profile-card__avatar{width:64px;height:64px;object-fit:cover;border:3px solid var(--bs-body-bg);border-radius:18px;box-shadow:var(--bs-box-shadow-sm)}
.managed-profile-card__body{padding:1rem}
@media(max-width:575.98px){.managed-profile-context{align-items:flex-start;flex-direction:column}.managed-profile-context .btn{width:100%}}
@media(prefers-reduced-motion:reduce){.profile-nav-group__chevron{transition:none}}
CSS;


?>
<?php Pjax::begin(['id' => 'hr-container','enablePushState' => true,'timeout' => 50000 ]); ?>
<style><?= $profileNavCss ?></style>
<?php $this->beginBlock('page-title'); ?>
<?= $isRequestedManagedProfile ? 'แฟ้มบริหารบุคลากร' : 'ข้อมูลส่วนบุคคล' ?> | <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php
$isSelfProfile = Yii::$app->controller->uniqueId === 'profile';
$profileMode = $profileMode ?? ($isSelfProfile ? 'self' : 'hr');
$isManagedProfile = $profileMode === 'manager';
$managerMenuLabels = [
    '' => ['ภาพรวม', 'ข้อมูลการทำงานและสายบังคับบัญชา'],
    'employment_contract' => ['สัญญาจ้าง', 'ช่วงเวลาจ้างและเอกสารอ้างอิง'],
    'position_history' => ['ประวัติดำรงตำแหน่ง', 'ตำแหน่งและการเปลี่ยนแปลงที่ผ่านมา'],
    'job_description_history' => ['Job Description', 'หน้าที่และความรับผิดชอบ'],
    'kpi' => ['KPI', 'เป้าหมายและผลการปฏิบัติงาน'],
    'license' => ['ข้อมูลใบประกอบวิชาชีพ', 'ใบอนุญาตและวันหมดอายุ'],
    'develop' => ['ข้อมูลการอบรม ดูงาน', 'อบรม ประชุม สัมมนา และศึกษาดูงาน'],
    'idp' => ['IDP', 'แผนพัฒนารายบุคคล'],
    'training_roadmap' => ['Training Roadmap', 'แผนการเรียนรู้ตามตำแหน่ง'],
    'annual_appraisal' => ['การประเมินผล', 'การประเมินผลการปฏิบัติงานปีละ 2 ครั้ง'],
    'performance_appraisal' => ['ประเมินทดลองงาน', 'เฉพาะช่วงเริ่มต้นการทำงาน'],
];
$managerGroupLabels = [
    'general' => ['ภาพรวม', 'ข้อมูลการจ้างและสถานะการทำงาน'],
    'work' => ['งานและหน้าที่', 'ตำแหน่ง Job Description และ KPI'],
    'development' => ['การพัฒนา', 'การอบรม Training Roadmap และ IDP'],
    'appraisal' => ['การประเมินผล', 'รอบประเมินผลการปฏิบัติงานประจำปี'],
    'probation' => ['ประเมินทดลองงาน', 'ใช้เฉพาะช่วงเริ่มต้นการทำงาน'],
];
if (!$isSelfProfile && !$isManagedProfile):
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'employees']) ?>
<?php $this->endBlock(); ?>
<?php endif; ?>

<?php if ($isManagedProfile): ?>
<aside class="managed-profile-context" aria-label="บริบทการเข้าดูข้อมูลบุคลากร">
    <div class="managed-profile-context__label">
        <span class="managed-profile-context__icon"><i data-lucide="users-round" aria-hidden="true"></i></span>
        <span><span class="managed-profile-context__eyebrow">มุมมองผู้บังคับบัญชา</span><span class="managed-profile-context__title">คุณกำลังดูแฟ้มบริหารบุคลากรของ <?= Html::encode($model->fullname) ?></span></span>
    </div>
    <?= Html::a('<i data-lucide="arrow-left" class="me-1" aria-hidden="true"></i> กลับไปกลุ่มงานของฉัน', ['/me'], ['class' => 'btn btn-sm btn-outline-primary', 'data-pjax' => '0']) ?>
</aside>
<?php endif; ?>



<div class="row d-flex">
    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-sx-12">
        <?php if ($isManagedProfile): ?>
            <?= $this->render('_management_profile_card', ['model' => $model]) ?>
        <?php else: ?>
            <?= $this->render('avatar',['model' => $model])?>
        <?php endif; ?>

        <?php //  $this->render('member_on_dep',['model' => $model])?>

        <?php if (!$isManagedProfile): ?>
            <?=Html::a('<i class="bi bi-cloud-plus-fill fs-3"></i> แบบสารสนเทศเบื้องต้น', ['upload-basic-doc', 'id' => $model->id], ['class' => 'w-100 mb-3 btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
        <?php endif; ?>
        <?php if ($isManagedProfile): ?>
            <div class="d-flex align-items-center gap-2 mb-3 px-1"><i data-lucide="folder-user" class="text-primary" aria-hidden="true"></i><h2 class="h6 mb-0">แฟ้มประวัติพนักงาน</h2></div>
        <?php endif; ?>
        <nav class="profile-section-nav" aria-label="<?= $isManagedProfile ? 'เมนูแฟ้มบริหารบุคลากร' : 'เมนูข้อมูลส่วนบุคคล' ?>">
            <?php
            $profileGroups = $model->generalMenuGroups();
            if ($isManagedProfile) {
                $managerItems = [];
                foreach ($model->generalMenu() as $item) $managerItems[(string)$item['name']] = $item;
                foreach ($managerMenuLabels as $itemName => $copy) {
                    if (!isset($managerItems[$itemName])) {
                        $managerItems[$itemName] = ['name' => $itemName, 'title' => $copy[0], 'subtitle' => $copy[1], 'icon' => '<i data-lucide="file-text" class="lucide-icon text-primary"></i>', 'count' => 0];
                    }
                }
                $profileGroups = [
                    ['key' => 'general', 'title' => '', 'subtitle' => '', 'icon' => 'user-round', 'items' => [$managerItems[''], $managerItems['employment_contract']]],
                    ['key' => 'work', 'title' => '', 'subtitle' => '', 'icon' => 'briefcase-business', 'items' => [$managerItems['position_history'], $managerItems['job_description_history'], $managerItems['kpi'], $managerItems['license']]],
                    ['key' => 'development', 'title' => '', 'subtitle' => '', 'icon' => 'chart-no-axes-combined', 'items' => [$managerItems['develop'], $managerItems['training_roadmap'], $managerItems['idp']]],
                    ['key' => 'appraisal', 'title' => '', 'subtitle' => '', 'icon' => 'calendar-check-2', 'items' => [$managerItems['annual_appraisal']]],
                    ['key' => 'probation', 'title' => '', 'subtitle' => '', 'icon' => 'clipboard-check', 'items' => [$managerItems['performance_appraisal']]],
                ];
            }
            foreach ($profileGroups as $group):
                $groupNames = array_column($group['items'], 'name');
                $groupActive = in_array((string) $name, array_map('strval', $groupNames), true);
                $groupOpen = $groupActive || (!$name && $group['key'] === 'general');
            ?>
            <details class="profile-nav-group" <?= $groupOpen ? 'open' : '' ?>>
                <summary class="profile-nav-group__summary">
                    <span class="profile-nav-group__icon"><i data-lucide="<?= Html::encode($group['icon']) ?>" aria-hidden="true"></i></span>
                    <span class="profile-nav-group__copy">
                        <span class="profile-nav-group__title"><?= Html::encode($isManagedProfile ? $managerGroupLabels[$group['key']][0] : $group['title']) ?></span>
                        <span class="profile-nav-group__subtitle"><?= Html::encode($isManagedProfile ? $managerGroupLabels[$group['key']][1] : $group['subtitle']) ?></span>
                    </span>
                    <i data-lucide="chevron-down" class="profile-nav-group__chevron" aria-hidden="true"></i>
                </summary>
                <div class="profile-nav-group__items">
                    <?php foreach ($group['items'] as $list):
                        $menuUrl = $list['url'] ?? ['/hr/employees/view', 'id' => $model->id, 'name' => $list['name']];
                        if ($isManagedProfile) {
                            $menuUrl = ['/hr/employees/view', 'id' => $model->id, 'name' => $list['name'], 'view' => 'manager'];
                        }
                        if ($isSelfProfile && ($list['name'] ?? '') === 'training_roadmap') {
                            $menuUrl = ['/profile', 'name' => 'training_roadmap'];
                        } elseif ($isSelfProfile && ($list['name'] ?? '') === 'idp') {
                            $menuUrl = ['/profile', 'name' => 'idp'];
                        } elseif (($list['name'] ?? '') === 'housing') {
                            $menuUrl = $isSelfProfile
                                ? ['/profile', 'name' => 'housing']
                                : ['/housing/request/index'];
                        } elseif (!$isSelfProfile && !$isManagedProfile && ($list['name'] ?? '') === 'idp') {
                            $menuUrl = ['/hr/idp/employee', 'emp_id' => $model->id];
                        } elseif ($isSelfProfile && !isset($list['url'])) {
                            $menuUrl = ['/profile', 'name' => $list['name']];
                        }
                        $itemActive = ((string) ($list['name'] ?? '') === (string) $name);
                        $comingSoon = !empty($list['coming_soon']);
                        $itemCount = (($list['name'] ?? '') === 'housing' && $isSelfProfile)
                            ? (int)($housingActionCount ?? 0)
                            : ((($list['name'] ?? '') === 'performance_appraisal' && $isSelfProfile)
                                ? (int)($probationActionCount ?? 0)
                                : (int)($list['count'] ?? 0));
                        $isHousingAlert = (($list['name'] ?? '') === 'housing' && $isSelfProfile && $itemCount > 0);
                    ?>
                    <?php if ($comingSoon): ?>
                    <div class="profile-nav-item is-coming-soon"
                         role="link"
                         aria-disabled="true"
                         title="เตรียมเปิดใช้งาน">
                        <span class="profile-nav-item__icon"><?= $list['icon'] ?></span>
                        <span class="profile-nav-item__copy">
                            <span class="profile-nav-item__title"><?= Html::encode($isManagedProfile ? $managerMenuLabels[$list['name']][0] : $list['title']) ?></span>
                            <span class="profile-nav-item__subtitle"><?= Html::encode($isManagedProfile ? $managerMenuLabels[$list['name']][1] : $list['subtitle']) ?></span>
                        </span>
                        <span class="profile-nav-item__status">เตรียมเปิดใช้</span>
                    </div>
                    <?php else: ?>
                    <a href="<?= Url::to($menuUrl) ?>"
                       class="profile-nav-item <?= $itemActive ? 'is-active' : '' ?>"
                       aria-current="<?= $itemActive ? 'page' : 'false' ?>"
                       data-pjax="<?= isset($list['url']) ? '0' : '1' ?>">
                        <span class="profile-nav-item__icon"><?= $list['icon'] ?></span>
                        <span class="profile-nav-item__copy">
                            <span class="profile-nav-item__title"><?= Html::encode($isManagedProfile ? $managerMenuLabels[$list['name']][0] : $list['title']) ?></span>
                            <span class="profile-nav-item__subtitle"><?= Html::encode($isManagedProfile ? $managerMenuLabels[$list['name']][1] : $list['subtitle']) ?></span>
                        </span>
                        <?php if ($itemCount > 0): ?><span class="profile-nav-item__count <?= $isHousingAlert ? 'is-alert' : '' ?>"><?= $itemCount ?></span><?php endif ?>
                    </a>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?php if (!$isManagedProfile) echo $this->render('box_summary',['model' => $model, 'name' => $name])?>
        <?php if($name === 'training_roadmap'):?>
        <?= $this->render('@app/modules/hr/views/training-roadmap/_employee_panel', [
            'employee' => $model,
            'plans' => $trainingPlans ?? $model->trainingPlans,
        ]) ?>
        <?php elseif($name === 'idp'):?>
        <?= $this->render('@app/modules/hr/views/idp/_employee_panel', [
            'employee' => $model,
            'cycle' => $idpCycle ?? null,
            'plan' => $idpPlan ?? null,
            'isSelfProfile' => $isSelfProfile,
        ]) ?>
        <?php elseif($name === 'housing'):?>
        <?= $this->render('@app/modules/housing/views/my/_profile_panel', [
            'context' => $housingContext ?? [
                'mode' => 'unavailable',
                'employee' => $model,
                'occupancy' => null,
                'request' => null,
            ],
            'vacancies' => $housingVacancies ?? [],
        ]) ?>
        <?php elseif($name === 'performance_appraisal'):?>
        <?= $this->render('@app/modules/hr/views/probation-appraisal/_profile_panel', [
            'employee' => $model,
            'actorEmployee' => $viewerEmployee ?? $model,
            'isManagedProfile' => $isManagedProfile,
            'cases' => $probationCases ?? [],
            'actionCount' => $probationActionCount ?? 0,
        ]) ?>
        <?php elseif($name === 'employment_contract'):?>
        <?= $this->render('_management_contract', ['model' => $model, 'records' => $dataProvider ? $dataProvider->getModels() : []]) ?>
        <?php elseif($name === 'position_history'):?>
        <?= $this->render('_management_position_history', ['model' => $model, 'records' => $dataProvider ? $dataProvider->getModels() : []]) ?>
        <?php elseif($name === 'annual_appraisal'):?>
        <?= $this->render('_management_annual_appraisal', ['model' => $model]) ?>
        <?php elseif($name === 'license' && $isManagedProfile):?>
        <?= $this->render('_management_license', ['model' => $model, 'records' => $model->licenses]) ?>
        <?php elseif($name):?>
        <div>
            <?php echo $this->render('./lists/'.$name.'_list',['model' => $model,'name' => $name, 'dataProvider' => $dataProvider, 'isManagedProfile' => $isManagedProfile])?>
        </div>
        <?php elseif ($isManagedProfile): ?>
        <?= $this->render('_management_overview', ['model' => $model]) ?>
        <?php else :?>
        <?php echo $this->render('general',['model' => $model])?>
        <?php echo $this->render('@app/views/profile/point_chart',['model' => $model])?>
        <?php // echo $this->render('@app/views/profile/estimate_chart')?>
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-sm-row justify-content-between mb-4 text-center text-sm-left">
                    <h5>หน้าที่รับมอบหมาย</h5>
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                                aria-selected="true"> คณะกรรมการทีมประสาน</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile"
                                aria-selected="false" tabindex="-1">ครุภัณฑ์ที่รับผิดชอบ</button>
                        </li>


                    </ul>
                </div>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade active show" id="pills-home" role="tabpanel"
                        aria-labelledby="pills-home-tab" tabindex="0">
                            <?=$this->render('list_committee')?>
                        <?php // $this->render('@app/modules/hr/views/employees/team',['model' => $model])?>
                    </div>
                    <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab"
                        tabindex="0">
                        <?php //$this->render('@app/modules/hr/views/employees/assets',['model' => $model])?>
                    </div>

                </div>
            </div>
        </div>
        <?php endif;?>
    </div>
</div>

<?php Pjax::end(); ?>
