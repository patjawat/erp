<?php

namespace app\modules\jd\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\components\AppHelper;
use app\modules\jd\models\JdTemplate;
use app\modules\jd\models\JdTemplateSearch;
use app\modules\jd\models\JdTemplateSection;
use app\modules\jd\data\MophSeedData;

class TemplateController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'        => ['POST'],
                    'delete-section' => ['POST'],
                    'import-seed'   => ['POST'],
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
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $model = new JdTemplate();
        $model->is_active = 1;
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            static::normalizeJdApprovedAt($model);
            if ($model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'message' => 'สร้าง template สำเร็จ',
                        'container' => '#jd-template-index',
                    ];
                }
                Yii::$app->session->setFlash('success', 'สร้าง template สำเร็จ');
                return $this->redirect(['view', 'id' => $model->id]);
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
                return $this->redirect(['view', 'id' => $model->id]);
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
