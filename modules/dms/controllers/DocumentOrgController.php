<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\ModalHelper;
use yii\web\NotFoundHttpException;
use app\modules\dms\models\DocumentOrg;
use app\modules\dms\models\DocumentOrgSearch;

/**
 * DocumentOrgController implements the CRUD actions for DocumentOrg model.
 */
class DocumentOrgController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

        // ตรวจสอบความถูกต้อง
public function actionValidator()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $model = new DocumentOrg();
    $result = [];

    if ($this->request->isPost && $model->load($this->request->post())) {
        $postData = $this->request->post('DocumentOrg', []);
        $currentId = isset($postData['id']) && $postData['id'] !== '' ? (int) $postData['id'] : null;
        $name = $postData['name'] ?? $model->name ?? 'document_org';
        
        // 1. ตรวจสอบค่าว่าง (Required)
        if (empty($model->title)) {
            $model->addError('title', 'ต้องระบุชื่อเรื่อง');
        }
        if (empty($model->code)) {
            $model->addError('code', 'ต้องระบุรหัส');
        }

        // 2. ตรวจสอบความซ้ำ (Unique Check)
        // ตรวจสอบ title ซ้ำ
        $titleQuery = DocumentOrg::find()
            ->where([
                'name' => $name,
                'title' => $model->title,
            ]);
        if ($currentId !== null) {
            $titleQuery->andWhere(['<>', 'id', $currentId]);
        }
        $existsTitle = $titleQuery->exists();
        if ($existsTitle) {
            $model->addError('title', 'ชื่อเรื่องนี้มีอยู่ในระบบแล้ว');
        }

        // ตรวจสอบ code ซ้ำ
        $codeQuery = DocumentOrg::find()
            ->where([
                'name' => $name,
                'code' => $model->code,
            ]);
        if ($currentId !== null) {
            $codeQuery->andWhere(['<>', 'id', $currentId]);
        }
        $existsCode = $codeQuery->exists();
        if ($existsCode) {
            $model->addError('code', 'รหัสนี้มีอยู่ในระบบแล้ว');
        }

        // สรุป Error ทั้งหมดส่งกลับ
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }

        return $result; // ถ้าว่างจะเป็น [] หมายถึงผ่านการตรวจสอบ
    }
}

    /**
     * Lists all DocumentOrg models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DocumentOrgSearch([
            'name' => 'document_org'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
         $q = trim($searchModel->q ?? '');
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'title', $q],
            ['like', new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.url')"), $q],
        ]);

        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DocumentOrg model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
 public function actionCreate()
    {
        $model = new DocumentOrg([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'name' => 'document_org',
            'code' => $this->NextOrgCode()
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->save(false);
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success'];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
                'footer' =>  ModalHelper::modalFooterSaveClose(),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                 Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success'];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
                'footer' =>  ModalHelper::modalFooterSaveClose(),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

         protected function NextOrgCode()
    {
           $maxCode = Categorise::find()->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED)+1)')])->where(['=', 'name', 'document_org'])->scalar();
           return $maxCode;

    }

        protected function UpdateDocOrg($model)
    {
        // try {
        $title = $model->document_org;
        $check = Categorise::find()->where(['name' => 'document_org', 'title' => $title])->one();
        if (!$check) {
            $maxCode = Categorise::find()->select(['code' => new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))')])->where(['like', 'name', 'document_org'])->scalar();
            $newModel = new Categorise();
            $newModel->code = ($maxCode + 1);
            $newModel->name = 'document_org';
            $newModel->title = $title;
            $newModel->save(false);
            return $newModel->code;
        }

        // } catch (\Throwable $th) {
        // }
    }
    


    public function actionSetActive()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $active = Yii::$app->request->post('active');

        $model = Categorise::findOne($id);
        if ($model) {
            $model->active = $active;
            if ($model->save(false)) {
                return ['success' => true];
            }
        }
        return ['success' => false];
    }


    /**
     * Deletes an existing DocumentOrg model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the DocumentOrg model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DocumentOrg the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DocumentOrg::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
