<?php

use yii\helpers\Html;
use app\modules\hr\models\Organization;

/** @var app\modules\amSurvey\models\AssetSurvey $survey */
/** @var array $departments */

$this->title = 'สถานะตามหน่วยงาน — ' . $survey->survey_name;
$this->params['breadcrumbs'][] = ['label' => 'การสำรวจครุภัณฑ์', 'url' => ['/am-survey/default/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'รายงานสรุป', 'url' => ['summary', 'survey_id' => $survey->id]];
$this->params['breadcrumbs'][] = 'ตามหน่วยงาน';
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?></h4>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>หน่วยงาน</th>
                                <th class="text-end">จำนวนรายการสำรวจ</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider align-middle">
                            <?php
                            $ids = array_filter(array_keys($departments), function ($v) { return $v !== null && $v !== ''; });
                            $orgs = $ids ? Organization::find()->where(['id' => $ids])->indexBy('id')->all() : [];
                            foreach ($departments as $deptId => $cnt):
                                $name = ($deptId && isset($orgs[$deptId])) ? $orgs[$deptId]->name : 'ไม่ระบุหน่วยงาน';
                            ?>
                            <tr>
                                <td><?= Html::encode($name) ?></td>
                                <td class="text-end"><?= number_format($cnt) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($departments)): ?>
                    <p class="text-muted mb-0">ยังไม่มีข้อมูลตามหน่วยงาน</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
