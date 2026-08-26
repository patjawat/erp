<?php

namespace app\modules\serviceProfile\controllers;

use app\components\AppHelper;
use app\modules\serviceProfile\forms\AiTemplateForm;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\models\ServiceProfileTemplate;
use app\modules\serviceProfile\models\ServiceProfileTemplateSection;
use app\modules\serviceProfile\services\TemplateService;
use app\modules\serviceProfile\services\AiTemplateService;
use app\modules\serviceProfile\services\OwnerDirectoryService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TemplateController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['serviceProfileTemplateManage']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST'], 'delete-section' => ['POST'], 'publish' => ['POST'], 'clone' => ['POST']],
            ],
        ]);
    }

    public function actionIndex()
    {
        $query = ServiceProfileTemplate::find();
        $ownerId = (int) Yii::$app->request->get('owner_id', 0);
        $year = (int) AppHelper::YearBudget();
        $status = trim((string) Yii::$app->request->get('status', ''));
        if ($ownerId > 0) {
            try {
                $resolved = (new OwnerDirectoryService())->resolveOwner($ownerId, $year);
                $query->andWhere(['owner_type' => $resolved['owner_type'], 'owner_id' => $resolved['owner_id']]);
            } catch (\DomainException $e) { $query->andWhere('0=1'); }
        }
        if ($status !== '') $query->andWhere(['lifecycle_status' => $status]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['owner_name_snapshot' => SORT_ASC, 'revision_no' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider, 'ownerId' => $ownerId, 'status' => $status,
            'ownerOptions' => (new OwnerDirectoryService())->ownerOptions($year, $ownerId),
        ]);
    }

    public function actionCreate()
    {
        $model = new ServiceProfileTemplate([
            'owner_type' => ServiceProfileTemplate::OWNER_DEPARTMENT,
            'revision_no' => 1,
            'effective_fiscal_year' => (int) AppHelper::YearBudget(),
        ]);
        if ($model->load(Yii::$app->request->post())) {
            $tx = Yii::$app->db->beginTransaction();
            try {
                if (!$model->save()) throw new \RuntimeException(implode(' ', $model->getFirstErrors()));
                (new TemplateService())->seedDefaultSections($model);
                $tx->commit();
                return $this->success('สร้าง Template และโครงหัวข้อมาตรฐานแล้ว', ['structure', 'id' => $model->id]);
            } catch (\Throwable $e) {
                $tx->rollBack();
                $model->addError('name', $e->getMessage());
            }
        }
        return $this->formResponse('_form', ['model' => $model, 'ownerOptions' => (new OwnerDirectoryService())->ownerOptions((int) $model->effective_fiscal_year, (int) $model->org_unit_id)], 'สร้าง Template Service Profile');
    }

    public function actionAiGenerate()
    {
        $model = new AiTemplateForm([
            'effective_fiscal_year' => (int) AppHelper::YearBudget(),
            'section_count' => 12,
        ]);
        if ($model->load(Yii::$app->request->post())) {
            try {
                $template = (new AiTemplateService())->generate($model);
                return $this->success('AI สร้าง Template ฉบับร่างแล้ว กรุณาตรวจสอบทุกหัวข้อก่อนเผยแพร่', ['structure', 'id' => $template->id]);
            } catch (\Throwable $e) {
                $model->addError('mission', $this->aiErrorMessage($e));
            }
        }
        return $this->formResponse('_ai_form', ['model' => $model, 'ownerOptions' => (new OwnerDirectoryService())->ownerOptions((int) $model->effective_fiscal_year, (int) $model->owner_id)], 'สร้าง Template ด้วย AI');
    }

    public function actionUpdate($id)
    {
        $model = $this->findTemplate($id);
        $this->assertDraft($model);
        $unit = (new OwnerDirectoryService())->findOrgUnit($model->owner_type, (int) $model->owner_id, (int) $model->effective_fiscal_year);
        $model->org_unit_id = $unit?->id;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->success('บันทึกข้อมูล Template แล้ว');
        }
        return $this->formResponse('_form', ['model' => $model, 'ownerOptions' => (new OwnerDirectoryService())->ownerOptions((int) $model->effective_fiscal_year, (int) $model->org_unit_id)], 'แก้ไข Template');
    }

    public function actionDelete($id)
    {
        $model = $this->findTemplate($id);
        $this->assertDraft($model);
        if (ServiceProfile::find()->where(['template_id' => $model->id])->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เนื่องจากมี Service Profile ใช้ Template นี้แล้ว');
            return $this->redirect(['index']);
        }
        if (!$model->delete()) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบ Template ได้');
            return $this->redirect(['index']);
        }
        Yii::$app->session->setFlash('success', 'ลบ Template ฉบับร่างแล้ว');
        return $this->redirect(['index']);
    }

    public function actionStructure($id)
    {
        $model = $this->findTemplate($id);
        return $this->render('structure', ['model' => $model]);
    }

    public function actionCreateSection($template_id)
    {
        $template = $this->findTemplate($template_id);
        $this->assertDraft($template);
        $section = new ServiceProfileTemplateSection([
            'template_id' => $template->id,
            'sort_order' => (int) ServiceProfileTemplateSection::find()->where(['template_id' => $template->id])->max('sort_order') + 10,
            'is_enabled' => 1,
        ]);
        if ($section->load(Yii::$app->request->post()) && $section->save()) {
            return $this->success('เพิ่มหัวข้อแล้ว', null, '#sp-template-sections');
        }
        return $this->formResponse('_section_form', ['model' => $section, 'template' => $template], 'เพิ่มหัวข้อ');
    }

    public function actionUpdateSection($id)
    {
        $section = $this->findSection($id);
        $this->assertDraft($section->template);
        if ($section->load(Yii::$app->request->post()) && $section->save()) {
            return $this->success('บันทึกหัวข้อแล้ว', null, '#sp-template-sections');
        }
        return $this->formResponse('_section_form', ['model' => $section, 'template' => $section->template], 'แก้ไขหัวข้อ');
    }

    public function actionDeleteSection($id)
    {
        $section = $this->findSection($id);
        $template = $section->template;
        $this->assertDraft($template);
        $section->delete();
        Yii::$app->session->setFlash('success', 'ลบหัวข้อแล้ว');
        return $this->redirect(['structure', 'id' => $template->id]);
    }

    public function actionPublish($id)
    {
        $model = $this->findTemplate($id);
        $this->assertDraft($model);
        if (!$model->getSections()->andWhere(['is_enabled' => 1])->exists()) {
            Yii::$app->session->setFlash('error', 'ต้องมีหัวข้อที่เปิดใช้งานอย่างน้อยหนึ่งหัวข้อ');
        } else {
            (new TemplateService())->publish($model);
            Yii::$app->session->setFlash('success', 'ประกาศใช้ Template แล้ว');
        }
        return $this->redirect(['structure', 'id' => $model->id]);
    }

    public function actionClone($id)
    {
        $copy = (new TemplateService())->cloneRevision($this->findTemplate($id));
        Yii::$app->session->setFlash('success', 'สร้าง Template Revision ' . $copy->revision_no . ' แล้ว');
        return $this->redirect(['structure', 'id' => $copy->id]);
    }

    private function success(string $message, ?array $redirect = null, string $container = '#sp-template-list')
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = ['status' => 'success', 'message' => $message, 'container' => $container];
            if ($redirect) $result['redirect_url'] = \yii\helpers\Url::to($redirect);
            return $result;
        }
        Yii::$app->session->setFlash('success', $message);
        return $this->redirect($redirect ?: ['index']);
    }

    private function formResponse(string $view, array $params, string $title)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => $title, 'content' => $this->renderAjax($view, $params)];
        }
        return $this->render($view, $params);
    }

    private function findTemplate($id): ServiceProfileTemplate
    {
        $model = ServiceProfileTemplate::find()->with('sections')->where(['id' => $id])->one();
        if (!$model) throw new NotFoundHttpException('ไม่พบ Template');
        return $model;
    }

    private function findSection($id): ServiceProfileTemplateSection
    {
        $model = ServiceProfileTemplateSection::find()->with('template')->where(['id' => $id])->one();
        if (!$model) throw new NotFoundHttpException('ไม่พบหัวข้อ');
        return $model;
    }

    private function assertDraft(ServiceProfileTemplate $model): void
    {
        if ($model->lifecycle_status !== ServiceProfileTemplate::STATUS_DRAFT) {
            throw new NotFoundHttpException('Template ที่ประกาศใช้แล้วแก้ไขไม่ได้ กรุณาสร้าง Revision ใหม่');
        }
    }

    private function aiErrorMessage(\Throwable $e): string
    {
        $message = $e->getMessage();
        if (stripos($message, 'api key') !== false || stripos($message, 'not configured') !== false) {
            return 'ยังไม่ได้เชื่อมต่อ OpenRouter API key กรุณาตั้งค่าในระบบ AI ก่อนใช้งาน';
        }
        if (preg_match('/HTTP status (401|403)/i', $message)) return 'OpenRouter ปฏิเสธการเชื่อมต่อ กรุณาตรวจสอบ API key';
        if (preg_match('/HTTP status 429/i', $message)) return 'โควตา AI ไม่เพียงพอหรือมีคำขอมากเกินไป กรุณาลองใหม่ภายหลัง';
        return $message;
    }
}
