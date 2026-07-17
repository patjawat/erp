<?php

namespace app\modules\medsop\controllers;

use app\modules\hr\models\Organization;
use app\modules\medsop\models\Document;
use app\modules\medsop\models\DocumentSearch;
use app\modules\medsop\services\DocumentAccessService;
use app\modules\medsop\services\DocumentService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DocumentController extends Controller
{
    private $accessService;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    public function actionIndex()
    {
        $access = $this->access();
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $access);
        $models = $dataProvider->getModels();
        $organizationIds = array_values(array_unique(array_filter(array_map(static function (Document $model) {
            return (int) $model->organization_id;
        }, $models))));
        $organizations = $organizationIds
            ? Organization::find()->where(['id' => $organizationIds])->indexBy('id')->all()
            : [];

        $visibleQuery = Document::find()->alias('d')->andWhere(['d.deleted_at' => null]);
        $access->applyVisibleScope($visibleQuery);
        $kpi = [
            'total' => (clone $visibleQuery)->count(),
            'pending' => (clone $visibleQuery)->andWhere(['d.status' => Document::STATUS_PENDING])->count(),
            'published' => (clone $visibleQuery)->andWhere(['d.status' => Document::STATUS_PUBLISHED])->count(),
            'organizations' => (clone $visibleQuery)->select('d.organization_id')->distinct()->count(),
        ];

        return $this->render('index', compact('searchModel', 'dataProvider', 'organizations', 'kpi', 'access'));
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $access = $this->access();
        if (!$access->canView($model)) {
            Yii::$app->response->statusCode = 403;
            return $this->render('access-denied', ['model' => $model]);
        }
        return $this->render('view', ['model' => $model, 'access' => $access]);
    }

    public function actionCreate()
    {
        $access = $this->access();
        if (!$access->canCreate()) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์สร้างเอกสาร');
        }
        $employee = $access->currentEmployee();
        $model = new Document([
            'document_no' => $this->generateDocumentNo(),
            'status' => Document::STATUS_DRAFT,
            'current_revision' => 1,
            'created_emp_id' => $employee ? $employee->id : null,
            'created_by' => Yii::$app->user->id,
            'updated_by' => Yii::$app->user->id,
        ]);
        return $this->saveForm($model);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!$this->access()->canUpdate($model)) {
            throw new ForbiddenHttpException('เอกสารสถานะนี้ไม่สามารถแก้ไขได้');
        }
        $model->updated_by = Yii::$app->user->id;
        return $this->saveForm($model);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->request->isPost || !$this->access()->isAdmin() || $model->status !== Document::STATUS_DRAFT) {
            throw new ForbiddenHttpException('ไม่สามารถลบเอกสารนี้ได้');
        }
        $model->deleted_at = date('Y-m-d H:i:s');
        $model->deleted_by = Yii::$app->user->id;
        $model->save(false, ['deleted_at', 'deleted_by', 'updated_at']);
        Yii::$app->session->setFlash('success', 'ลบฉบับร่างแล้ว');
        return $this->redirect(['index']);
    }

    private function saveForm(Document $model)
    {
        $stepRows = Yii::$app->request->post('steps', []);
        if ($model->load(Yii::$app->request->post()) && (new DocumentService())->save($model, is_array($stepRows) ? $stepRows : [])) {
            Yii::$app->session->setFlash('success', 'บันทึกเอกสารเรียบร้อยแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if (!Yii::$app->request->isPost && !$model->isNewRecord) {
            $stepRows = array_map(static function ($step) {
                return $step->toArray(['title', 'description', 'caution']);
            }, $model->steps);
        }
        return $this->render('form', [
            'model' => $model,
            'stepRows' => $stepRows,
            'organizations' => Organization::find()->where(['active' => 1])->orderBy(['lft' => SORT_ASC])->all(),
        ]);
    }

    private function access(): DocumentAccessService
    {
        return $this->accessService ?: ($this->accessService = new DocumentAccessService());
    }

    private function findModel($id): Document
    {
        $model = Document::find()->where(['id' => $id, 'deleted_at' => null])->one();
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบเอกสาร');
        }
        return $model;
    }

    private function generateDocumentNo(): string
    {
        return 'MED-' . date('Ymd-His') . '-' . strtoupper(bin2hex(random_bytes(2)));
    }
}
