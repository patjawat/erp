<?php

use yii\helpers\Html;
use app\modules\hr\models\Organization;

/** @var app\modules\amSurvey\models\AssetSurvey $survey */
/** @var app\modules\amSurvey\models\CsvImportForm $importForm */

$this->title = 'นำเข้า CSV';
$this->params['breadcrumbs'][] = ['label' => 'การสำรวจครุภัณฑ์', 'url' => ['/am-survey/default/dashboard']];
$this->params['breadcrumbs'][] = ['label' => $survey->survey_name, 'url' => ['/am-survey/report/summary', 'survey_id' => $survey->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid px-2 px-md-3 pb-3">
    <div class="row g-3">
        <div class="col-12">
            <h4 class="fw-semibold mb-0"><?= Html::encode($this->title) ?> — <?= Html::encode($survey->survey_name) ?></h4>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <?= Html::a('<i class="fa-solid fa-download me-2"></i>ดาวน์โหลดตัวอย่าง CSV', ['/am-survey/import/download-sample'], ['class' => 'btn btn-outline-primary', 'target' => '_blank']) ?>
                                <small class="text-muted">ใช้เป็นแบบฟอร์มกรอกหมายเลขครุภัณฑ์แล้วนำเข้าระบบ</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="text-muted mb-0">ไฟล์ CSV ต้องมีคอลัมน์หมายเลขครุภัณฑ์ (คอลัมน์แรกเป็นค่าเริ่มต้น) แถวแรกเป็นหัวตาราง จะไม่นำเข้า หน่วยงานให้เลือกจากหน่วยงานเริ่มต้นด้านล่าง</p>
                        </div>
                    </div>
                    <?php $form = \yii\widgets\ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">คอลัมน์หมายเลขครุภัณฑ์ (0 = คอลัมน์แรก)</label>
                            <input type="number" name="asset_number_column" value="0" min="0" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">หน่วยงานเริ่มต้น (ใช้เมื่อไม่ระบุคอลัมน์แผนกใน CSV)</label>
                            <?= \kartik\tree\TreeViewInput::widget([
                                'name' => 'default_department_id',
                                'id' => 'import-default-department-id',
                                'query' => Organization::find()->addOrderBy('root, lft'),
                                'value' => '',
                                'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                                'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                                'fontAwesome' => true,
                                'asDropdown' => true,
                                'multiple' => false,
                                'options' => ['class' => 'form-control', 'placeholder' => '-- ไม่กำหนด --'],
                                'pluginOptions' => ['allowClear' => true],
                            ]) ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">ข้อมูลผู้สำรวจ</label>
                            <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $importForm, 'label' => false, 'placeholder' => 'ผู้สำรวจ']) ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">เลือกไฟล์ CSV</label>
                            <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <?= Html::submitButton('<i class="fa-solid fa-file-import me-1"></i> นำเข้า', ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('ยกเลิก', ['/am-survey/report/summary', 'survey_id' => $survey->id], ['class' => 'btn btn-outline-secondary']) ?>
                        </div>
                    </div>
                    <?php \yii\widgets\ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
