<?php

namespace app\modules\jobdescription\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\modules\jobdescription\models\JdTemplate;
use app\modules\jobdescription\models\JdTemplateSearch;
use app\modules\jobdescription\models\JdTemplateSection;

class TemplateController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'delete-section' => ['POST'],
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
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->save()) {
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
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->save()) {
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
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title') ?: 'แก้ไข Template: ' . $model->name,
                'content' => $this->renderAjax('update', ['model' => $model]),
            ];
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'ลบ template แล้ว');
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
