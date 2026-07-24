<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $model */

$this->title = $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนบุคลากร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$this->registerCss(<<<CSS
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
@media(prefers-reduced-motion:reduce){.profile-nav-group__chevron{transition:none}}
CSS);


?>
<?php Pjax::begin(['id' => 'hr-container','enablePushState' => true,'timeout' => 50000 ]); ?>
<?php $this->beginBlock('page-title'); ?>
ข้อมูลส่วนบุคคล | <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php
$isSelfProfile = Yii::$app->controller->uniqueId === 'profile';
if (!$isSelfProfile):
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'employees']) ?>
<?php $this->endBlock(); ?>
<?php endif; ?>



<div class="row d-flex">
    <div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-sx-12">
        <?= $this->render('avatar',['model' => $model])?>

        <?php //  $this->render('member_on_dep',['model' => $model])?>

        <?=Html::a('<i class="bi bi-cloud-plus-fill fs-3"></i> แบบสารสนเทศเบื้องต้น', ['upload-basic-doc', 'id' => $model->id], ['class' => 'w-100 mb-3 btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
        <nav class="profile-section-nav" aria-label="เมนูข้อมูลส่วนบุคคล">
            <?php foreach ($model->generalMenuGroups() as $group):
                $groupNames = array_column($group['items'], 'name');
                $groupActive = in_array((string) $name, array_map('strval', $groupNames), true);
                $groupOpen = $groupActive || (!$name && $group['key'] === 'general');
            ?>
            <details class="profile-nav-group" <?= $groupOpen ? 'open' : '' ?>>
                <summary class="profile-nav-group__summary">
                    <span class="profile-nav-group__icon"><i data-lucide="<?= Html::encode($group['icon']) ?>" aria-hidden="true"></i></span>
                    <span class="profile-nav-group__copy">
                        <span class="profile-nav-group__title"><?= Html::encode($group['title']) ?></span>
                        <span class="profile-nav-group__subtitle"><?= Html::encode($group['subtitle']) ?></span>
                    </span>
                    <i data-lucide="chevron-down" class="profile-nav-group__chevron" aria-hidden="true"></i>
                </summary>
                <div class="profile-nav-group__items">
                    <?php foreach ($group['items'] as $list):
                        $menuUrl = $list['url'] ?? ['/hr/employees/view', 'id' => $model->id, 'name' => $list['name']];
                        if ($isSelfProfile && ($list['name'] ?? '') === 'training_roadmap') {
                            $menuUrl = ['/profile', 'name' => 'training_roadmap'];
                        } elseif ($isSelfProfile && !isset($list['url'])) {
                            $menuUrl = ['/profile', 'name' => $list['name']];
                        }
                        $itemActive = ((string) ($list['name'] ?? '') === (string) $name);
                    ?>
                    <a href="<?= Url::to($menuUrl) ?>"
                       class="profile-nav-item <?= $itemActive ? 'is-active' : '' ?>"
                       aria-current="<?= $itemActive ? 'page' : 'false' ?>"
                       data-pjax="<?= isset($list['url']) ? '0' : '1' ?>">
                        <span class="profile-nav-item__icon"><?= $list['icon'] ?></span>
                        <span class="profile-nav-item__copy">
                            <span class="profile-nav-item__title"><?= Html::encode($list['title']) ?></span>
                            <span class="profile-nav-item__subtitle"><?= Html::encode($list['subtitle']) ?></span>
                        </span>
                        <?php if ((int) $list['count'] > 0): ?><span class="profile-nav-item__count"><?= (int) $list['count'] ?></span><?php endif ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </details>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
        <?php echo $this->render('box_summary',['model' => $model, 'name' => $name])?>
        <?php if($name === 'training_roadmap'):?>
        <?= $this->render('@app/modules/hr/views/training-roadmap/_employee_panel', [
            'employee' => $model,
            'plans' => $trainingPlans ?? $model->trainingPlans,
        ]) ?>
        <?php elseif($name):?>
        <div>
            <?php echo $this->render('./lists/'.$name.'_list',['model' => $model,'name' => $name, 'dataProvider' => $dataProvider])?>
        </div>
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
