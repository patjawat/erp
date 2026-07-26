<?php

namespace app\modules\jd\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use app\components\AppHelper;
use app\modules\jd\models\JdTemplate;
use app\modules\jd\models\JdTemplateSearch;
use app\modules\jd\models\JdTemplateSection;
use app\modules\jd\models\JdTemplateBlock;
use app\modules\jd\services\JdAiDraftService;
use app\modules\jd\data\MophSeedData;
use app\modules\jd\components\RichText;

class TemplateController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['hr', 'admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'        => ['POST'],
                    'delete-section' => ['POST'],
                    'import-seed'   => ['POST'],
                    'copy'          => ['POST'],
                    'new-revision'  => ['POST'],
                    'ai-draft'      => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new JdTemplateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->redirect(['structure', 'id' => $id]);
    }

    public function actionCreate()
    {
        $model = new JdTemplate();
        $model->is_active = 1;
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            static::normalizeJdApprovedAt($model);
            if ($model->save()) {
                JdTemplateBlock::ensureForTemplate((int) $model->id);
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'message' => 'สร้าง template สำเร็จ',
                        'container' => '#jd-template-index',
                    ];
                }
                Yii::$app->session->setFlash('success', 'สร้าง template สำเร็จ');
                return $this->redirect(['structure', 'id' => $model->id]);
            }
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title') ?: '<i class="bi bi-file-earmark-plus"></i> สร้าง Template JD',
                'content' => $this->renderAjax('create', ['model' => $model]),
            ];
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            static::normalizeJdApprovedAt($model);
            if ($model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกแก้ไขแล้ว',
                        'container' => '#jd-template-index',
                    ];
                }
                Yii::$app->session->setFlash('success', 'บันทึกแก้ไขแล้ว');
                return $this->redirect(['structure', 'id' => $model->id]);
            }
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title') ?: 'แก้ไข Template: ' . $model->name,
                'content' => $this->renderAjax('update', ['model' => $model]),
            ];
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionStructure($id)
    {
        $model = $this->findModel($id);
        JdTemplateBlock::ensureForTemplate((int) $model->id);

        if (Yii::$app->request->isPost) {
            $posted = Yii::$app->request->post('blocks', []);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($model->blocks as $block) {
                    if (!array_key_exists($block->section_code, $posted)) {
                        continue;
                    }
                    $payload = json_decode((string) $posted[$block->section_code], true);
                    if (!is_array($payload)) {
                        throw new \RuntimeException('ข้อมูลหมวด ' . $block->title . ' มีรูปแบบไม่ถูกต้อง');
                    }
                    $payload['intro'] = RichText::sanitize($payload['intro'] ?? '');
                    foreach (($payload['items'] ?? []) as &$item) {
                        if (!is_array($item)) {
                            $item = [];
                            continue;
                        }
                        foreach ($item as $key => $value) {
                            if ($key !== 'employee_id') {
                                $item[$key] = RichText::sanitize((string) $value);
                            }
                        }
                    }
                    unset($item);
                    $block->setData($payload);
                    $block->updated_at = date('Y-m-d H:i:s');
                    if (!$block->save()) {
                        throw new \RuntimeException(implode(', ', $block->getFirstErrors()));
                    }
                }
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'บันทึกโครงสร้าง Template แล้ว');
                return $this->redirect(['structure', 'id' => $model->id]);
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('structure', ['model' => $model]);
    }

    public function actionCopy($id)
    {
        $source = $this->findModel($id);
        $copy = new JdTemplate();
        $copy->setAttributes($source->getAttributes(null, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'jd_approved_at', 'ai_generated_at']));
        $copy->name = $source->name . ' (สำเนา)';
        $copy->template_code = null;
        $copy->template_type = 'variant';
        $copy->parent_template_id = $source->id;
        $copy->revision_no = 1;
        $copy->lifecycle_status = 'draft';
        $copy->is_active = 0;
        if (!$copy->save()) {
            throw new \RuntimeException(implode(', ', $copy->getFirstErrors()));
        }
        foreach ($source->blocks as $sourceBlock) {
            $block = new JdTemplateBlock();
            $block->setAttributes($sourceBlock->getAttributes(null, ['id', 'template_id']));
            $block->template_id = $copy->id;
            $block->save(false);
        }
        Yii::$app->session->setFlash('success', 'คัดลอกเป็น Template ใหม่แล้ว กรุณาตรวจสอบชื่อและรายละเอียด');
        return $this->redirect(['update', 'id' => $copy->id]);
    }

    public function actionNewRevision($id)
    {
        $source = $this->findModel($id);
        $copy = new JdTemplate();
        $copy->setAttributes($source->getAttributes(null, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by', 'jd_approved_at', 'ai_generated_at']));
        $copy->parent_template_id = $source->parent_template_id ?: $source->id;
        $copy->revision_no = ((int) $source->revision_no) + 1;
        $copy->lifecycle_status = 'draft';
        $copy->is_active = 0;
        if (!$copy->save()) {
            throw new \RuntimeException(implode(', ', $copy->getFirstErrors()));
        }
        foreach ($source->blocks as $sourceBlock) {
            $block = new JdTemplateBlock();
            $block->setAttributes($sourceBlock->getAttributes(null, ['id', 'template_id']));
            $block->template_id = $copy->id;
            $block->save(false);
        }
        Yii::$app->session->setFlash('success', 'สร้าง Revision ' . $copy->revision_no . ' เป็นฉบับร่างแล้ว');
        return $this->redirect(['structure', 'id' => $copy->id]);
    }

    public function actionAiDraft($id)
    {
        $model = $this->findModel($id);
        JdTemplateBlock::ensureForTemplate((int) $model->id);
        try {
            $service = new JdAiDraftService();
            $draft = $service->generate($model);
            foreach ($model->blocks as $block) {
                if (isset($draft[$block->section_code]) && is_array($draft[$block->section_code])) {
                    $block->setData($draft[$block->section_code]);
                    $block->updated_at = date('Y-m-d H:i:s');
                    $block->save(false);
                }
            }
            $model->ai_generated_at = date('Y-m-d H:i:s');
            $model->save(false, ['ai_generated_at']);
            Yii::$app->session->setFlash('success', 'AI สร้างข้อมูลฉบับร่างแล้ว กรุณาตรวจสอบทุกหมวดก่อนใช้งาน');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['structure', 'id' => $model->id]);
    }

    /**
     * แปลงช่องวันที่อนุมัติ JD จากรูปแบบไทย (ว/ด/พ.ศ.) เป็น Y-m-d H:i:s
     */
    private static function normalizeJdApprovedAt(JdTemplate $model): void
    {
        $v = $model->jd_approved_at;
        if ($v === null || $v === '') {
            return;
        }
        $v = is_string($v) ? trim($v) : $v;
        if (is_string($v) && preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $v)) {
            $converted = AppHelper::convertToGregorian($v);
            if ($converted !== null) {
                $model->jd_approved_at = $converted . ' 00:00:00';
            }
        }
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'ลบ template แล้ว');
        return $this->redirect(['index']);
    }

    /**
     * นำเข้า Template สำเร็จรูป — ตำแหน่งงานกระทรวงสาธารณสุข 15 ตำแหน่ง
     * ข้อมูลจาก modules/jd/data/MophSeedData.php
     */
    public function actionImportSeed()
    {
        $db          = Yii::$app->db;
        $now         = date('Y-m-d H:i:s');
        $userId      = Yii::$app->has('user') && !Yii::$app->user->isGuest ? (int)Yii::$app->user->id : null;
        $positions   = MophSeedData::getPositions();
        $tTemplate   = JdTemplate::tableName();
        $tSection    = JdTemplateSection::tableName();

        $transaction = $db->beginTransaction();
        $inserted    = 0;
        $skipped     = 0;

        try {
            foreach ($positions as $pos) {
                $code = $pos['position_code'];

                // ถ้ามีอยู่แล้ว ข้ามไป (ไม่ทับข้อมูลที่แก้ไขแล้ว)
                $exists = (new \yii\db\Query())
                    ->from($tTemplate)
                    ->where(['position_code' => $code])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Insert template
                $db->createCommand()->insert($tTemplate, [
                    'name'            => $pos['name'],
                    'position_code'   => $code,
                    'job_code'        => $pos['job_code']        ?? null,
                    'job_level'       => $pos['job_level']       ?? null,
                    'department'      => $pos['department']      ?? null,
                    'employment_type' => $pos['employment_type'] ?? null,
                    'job_purpose'     => $pos['job_purpose']     ?? null,
                    'edu_requirement' => $pos['edu_requirement'] ?? null,
                    'exp_years'       => $pos['exp_years']       ?? null,
                    'core_competency' => $pos['core_competency'] ?? null,
                    'is_active'       => 1,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                    'created_by'      => $userId,
                    'updated_by'      => $userId,
                ])->execute();

                $templateId = $db->getLastInsertID();

                // Insert sections
                foreach ($pos['sections'] as [$sort, $title, $content]) {
                    $db->createCommand()->insert($tSection, [
                        'template_id' => $templateId,
                        'title'       => $title,
                        'content'     => $content,
                        'sort_order'  => $sort,
                    ])->execute();
                }

                $inserted++;
            }

            $transaction->commit();

            $msg = "นำเข้า Template สาธารณสุขสำเร็จ {$inserted} ตำแหน่ง";
            if ($skipped > 0) {
                $msg .= " (ข้ามตำแหน่งที่มีอยู่แล้ว {$skipped} รายการ)";
            }
            Yii::$app->session->setFlash('success', $msg);

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('danger', 'นำเข้าไม่สำเร็จ: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /** เพิ่มหัวข้อใน template */
    public function actionAddSection($id)
    {
        $template = $this->findModel($id);
        $section = new JdTemplateSection();
        $section->template_id = $template->id;
        $maxOrder = (int) JdTemplateSection::find()->where(['template_id' => $template->id])->max('sort_order');
        $section->sort_order = $maxOrder + 1;

        if (Yii::$app->request->isPost && $section->load(Yii::$app->request->post()) && $section->save()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'message' => 'เพิ่มหัวข้อแล้ว',
                    'url' => Url::to(['view', 'id' => $template->id]),
                ];
            }
            Yii::$app->session->setFlash('success', 'เพิ่มหัวข้อแล้ว');
            return $this->redirect(['view', 'id' => $template->id]);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title') ?: 'เพิ่มหัวข้อ: ' . $template->name,
                'content' => $this->renderAjax('add-section', ['template' => $template, 'section' => $section]),
            ];
        }
        return $this->render('add-section', ['template' => $template, 'section' => $section]);
    }

    public function actionUpdateSection($id)
    {
        $section = JdTemplateSection::findOne($id);
        if (!$section) {
            throw new NotFoundHttpException('ไม่พบหัวข้อ');
        }
        $template = $section->template;
        if (Yii::$app->request->isPost && $section->load(Yii::$app->request->post()) && $section->save()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'message' => 'บันทึกหัวข้อแล้ว',
                    'url' => Url::to(['view', 'id' => $template->id]),
                ];
            }
            Yii::$app->session->setFlash('success', 'บันทึกหัวข้อแล้ว');
            return $this->redirect(['view', 'id' => $template->id]);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title') ?: 'แก้ไขหัวข้อ: ' . $section->title,
                'content' => $this->renderAjax('update-section', ['template' => $template, 'section' => $section]),
            ];
        }
        return $this->render('update-section', ['template' => $template, 'section' => $section]);
    }

    public function actionDeleteSection($id)
    {
        $section = JdTemplateSection::findOne($id);
        if (!$section) {
            throw new NotFoundHttpException('ไม่พบหัวข้อ');
        }
        $templateId = $section->template_id;
        $section->delete();
        Yii::$app->session->setFlash('success', 'ลบหัวข้อแล้ว');
        return $this->redirect(['view', 'id' => $templateId]);
    }

    protected function findModel($id)
    {
        $model = JdTemplate::find()->where(['id' => $id])->with(['sections'])->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบ template');
        }
        return $model;
    }
}
