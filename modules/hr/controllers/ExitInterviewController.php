<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\modules\hr\models\Employees;
use app\modules\hr\models\ExitInterview;
use app\modules\hr\models\ExitInterviewLink;
use app\modules\hr\models\ExitInterviewQuestion;
use app\modules\hr\models\ExitInterviewTemplate;
use app\modules\hr\models\ExitInterviewTemplateVersion;
use app\modules\hr\services\ExitInterviewService;

class ExitInterviewController extends Controller
{
    private ExitInterviewService $service;

    public function init()
    {
        parent::init();
        $this->service = new ExitInterviewService();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['respond'], 'allow' => true, 'roles' => ['?', '@']],
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['issue-link' => ['POST'], 'send-telegram' => ['POST'], 'revoke-link' => ['POST'], 'publish' => ['POST'], 'clone-version' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->assertPermission('exitInterviewViewAnalytics');
        $filters = [
            'date_from' => Yii::$app->request->get('date_from'),
            'date_to' => Yii::$app->request->get('date_to'),
            'department' => Yii::$app->request->get('department'),
            'exit_type' => Yii::$app->request->get('exit_type'),
        ];
        $departmentItems = ExitInterview::find()->select(['department_id_snapshot', 'department_name_snapshot'])->where(['not', ['department_id_snapshot' => null]])->distinct()->orderBy('department_name_snapshot')->asArray()->all();
        $departmentItems = ArrayHelper::map($departmentItems, 'department_id_snapshot', 'department_name_snapshot');
        return $this->render('index', ['dashboard' => $this->service->dashboard($filters), 'filters' => $filters, 'departmentItems' => $departmentItems]);
    }

    public function actionRegistry()
    {
        $this->assertPermission('exitInterviewManage');
        $query = ExitInterview::find()->alias('i')->with(['links']);
        $q = trim((string) Yii::$app->request->get('q'));
        $status = trim((string) Yii::$app->request->get('status'));
        if ($q !== '') $query->andWhere(['or', ['like', 'i.employee_name_snapshot', $q], ['like', 'i.department_name_snapshot', $q]]);
        if ($status !== '') $query->andWhere(['i.status' => $status]);
        return $this->render('registry', [
            'dataProvider' => new ActiveDataProvider(['query' => $query->orderBy(['i.exit_date' => SORT_DESC, 'i.id' => SORT_DESC]), 'pagination' => ['pageSize' => 20]]),
            'q' => $q, 'status' => $status,
        ]);
    }

    public function actionCreate()
    {
        $this->assertPermission('exitInterviewManage');
        $model = new ExitInterview(['response_source' => 'hr_interview', 'exit_type' => 'resignation']);
        if ($model->load(Yii::$app->request->post())) {
            $employee = Employees::find()->with(['empDepartment', 'employeePosition', 'employeeType'])->where(['id' => $model->emp_id])->one();
            if (!$employee) throw new NotFoundHttpException('ไม่พบบุคลากร');
            $created = $this->service->createInterview($employee, [
                'exit_type' => $model->exit_type, 'exit_date' => $model->exit_date,
                'interview_date' => $model->interview_date, 'response_source' => $model->response_source,
                'interviewer_id' => Yii::$app->user->id,
            ]);
            if (Yii::$app->request->isAjax) return $this->jsonSuccess('สร้างรายการสัมภาษณ์เรียบร้อย', '#exit-registry');
            return $this->redirect(['form', 'id' => $created->id]);
        }
        return $this->modalOrPage('_create_form', [
            'model' => $model,
            'employeeItems' => ArrayHelper::map(Employees::find()->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->all(), 'id', static fn($e) => trim($e->fname . ' ' . $e->lname)),
        ], 'สร้างรายการสัมภาษณ์');
    }

    public function actionForm($id)
    {
        if (Yii::$app->request->isPost) {
            $this->assertPermission('exitInterviewManage');
        } else {
            $this->assertAnyPermission(['exitInterviewViewIdentified', 'exitInterviewManage']);
        }
        $model = $this->findInterview($id);
        if (Yii::$app->request->isPost) {
            try {
                $submit = Yii::$app->request->post('intent') === 'submit';
                $reason = $model->status === 'submitted' ? trim((string) Yii::$app->request->post('edit_reason')) : null;
                if ($model->status === 'submitted' && $reason === '') throw new \yii\web\BadRequestHttpException('กรุณาระบุเหตุผลที่แก้ไขคำตอบที่ส่งแล้ว');
                $this->service->saveAnswers($model, (array) Yii::$app->request->post('answers'), $submit, $reason, true, false);
                Yii::$app->session->setFlash('success', $submit ? 'ส่งแบบสัมภาษณ์เรียบร้อย' : 'บันทึกร่างเรียบร้อย');
                return $this->redirect(['form', 'id' => $model->id]);
            } catch (\Throwable $e) {
                Yii::$app->session->setFlash('danger', $e->getMessage());
            }
        }
        return $this->render('form', [
            'model' => $model,
            'sections' => $this->service->questionsFor($model, true),
            'answers' => $this->service->answerMap($model),
            'publicMode' => false,
            'canEdit' => Yii::$app->user->can('exitInterviewManage') || Yii::$app->user->can('admin'),
        ]);
    }

    public function actionIssueLink($id)
    {
        $this->assertPermission('exitInterviewManage');
        $model = $this->findInterview($id);
        $token = $this->service->issueLink($model, (int) Yii::$app->request->post('days', 14));
        $url = Url::to(['/hr/exit-interview/respond', 'token' => $token], true);
        $this->service->audit($model->id, 'link_copied', null, null, null, null);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'success', 'message' => 'สร้างลิงก์แล้ว', 'url' => $url];
    }

    public function actionRevokeLink($id)
    {
        $this->assertPermission('exitInterviewManage');
        $model = $this->findInterview($id);
        ExitInterviewLink::updateAll(['status' => 'revoked', 'updated_at' => date('Y-m-d H:i:s')], ['interview_id' => $model->id, 'status' => 'active']);
        $this->service->audit($model->id, 'link_revoked', null, null, null, null);
        return $this->redirect(['registry']);
    }

    public function actionSendTelegram($id)
    {
        $this->assertPermission('exitInterviewManage');
        $model = $this->findInterview($id);
        $employee = Employees::find()->with(['user'])->where(['id' => $model->emp_id])->one();
        $chatId = trim((string) ($employee?->user?->telegram_id ?? ''));
        if ($chatId === '') {
            Yii::$app->session->setFlash('warning', 'บุคลากรรายนี้ยังไม่ได้เชื่อม Telegram');
            return $this->redirect(['registry']);
        }
        $token = $this->service->issueLink($model, 14);
        $url = Url::to(['/hr/exit-interview/respond', 'token' => $token], true);
        $sent = Yii::$app->telegram->sendDirectMessage($chatId, "แบบสอบถามความคิดเห็นกรณีออกจากงาน\nกรุณาตอบภายใน 14 วัน\n{$url}");
        $this->service->audit($model->id, $sent ? 'telegram_sent' : 'telegram_failed', null, null, null, null);
        Yii::$app->session->setFlash($sent ? 'success' : 'danger', $sent ? 'ส่งลิงก์ผ่าน Telegram แล้ว' : 'ส่ง Telegram ไม่สำเร็จ กรุณาใช้ปุ่มคัดลอกลิงก์แทน');
        return $this->redirect(['registry']);
    }

    public function actionRespond($token)
    {
        $this->layout = '@app/views/layouts/none';
        try {
            $link = $this->service->findUsableLink((string) $token);
            $model = $link->interview;
            if (Yii::$app->request->isPost) {
                $submit = Yii::$app->request->post('intent') === 'submit';
                if ($submit && (string) Yii::$app->request->post('consent') !== '1') throw new \yii\web\BadRequestHttpException('กรุณายืนยันการยินยอมก่อนส่งแบบสัมภาษณ์');
                $this->service->saveAnswers($model, (array) Yii::$app->request->post('answers'), $submit, null, false, $submit);
                if ($submit) {
                    $link->status = 'submitted'; $link->submitted_at = date('Y-m-d H:i:s'); $link->save(false);
                    return $this->render('thanks');
                }
                Yii::$app->session->setFlash('success', 'บันทึกร่างแล้ว ท่านสามารถกลับมาตอบต่อผ่านลิงก์เดิม');
                return $this->redirect(['respond', 'token' => $token]);
            }
            return $this->render('respond', ['model' => $model, 'link' => $link, 'sections' => $this->service->questionsFor($model, false), 'answers' => $this->service->answerMap($model), 'publicMode' => true, 'canEdit' => true]);
        } catch (\yii\web\BadRequestHttpException $e) {
            return $this->render('link_error', ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            return $this->render('link_error', ['message' => 'ระบบไม่สามารถเปิดแบบสัมภาษณ์ได้ในขณะนี้ กรุณาติดต่อฝ่ายทรัพยากรบุคคล']);
        }
    }

    public function actionTemplates()
    {
        $this->assertPermission('exitInterviewManageTemplate');
        return $this->render('templates', ['templates' => ExitInterviewTemplate::find()->with(['versions.sections.questions.options'])->all()]);
    }

    public function actionCloneVersion($id)
    {
        $this->assertPermission('exitInterviewManageTemplate');
        $source = ExitInterviewTemplateVersion::find()->where(['id' => $id])->with(['sections.questions.options'])->one();
        if (!$source) throw new NotFoundHttpException('ไม่พบเวอร์ชันต้นทาง');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $version = new ExitInterviewTemplateVersion(['template_id' => $source->template_id, 'version_no' => ((int) ExitInterviewTemplateVersion::find()->where(['template_id' => $source->template_id])->max('version_no')) + 1, 'status' => 'draft', 'intro_text' => $source->intro_text]);
            if (!$version->save()) throw new \RuntimeException(implode(' ', $version->getFirstErrors()));
            foreach ($source->sections as $section) {
                $newSection = new \app\modules\hr\models\ExitInterviewSection($section->getAttributes(['code', 'title', 'description', 'sequence', 'condition_json']));
                $newSection->version_id = $version->id; if (!$newSection->save()) throw new \RuntimeException(implode(' ', $newSection->getFirstErrors()));
                foreach ($section->questions as $question) {
                    $newQuestion = new ExitInterviewQuestion($question->getAttributes(['code', 'prompt', 'question_type', 'is_required', 'sequence', 'analytics_key', 'config_json', 'condition_json', 'is_hr_only']));
                    $newQuestion->section_id = $newSection->id; if (!$newQuestion->save()) throw new \RuntimeException(implode(' ', $newQuestion->getFirstErrors()));
                    foreach ($question->options as $option) {
                        $newOption = new \app\modules\hr\models\ExitInterviewQuestionOption($option->getAttributes(['value', 'label', 'score', 'sequence', 'is_other']));
                        $newOption->question_id = $newQuestion->id; if (!$newOption->save()) throw new \RuntimeException(implode(' ', $newOption->getFirstErrors()));
                    }
                }
            }
            $transaction->commit(); Yii::$app->session->setFlash('success', 'สร้างเวอร์ชันร่างใหม่แล้ว');
        } catch (\Throwable $e) { $transaction->rollBack(); Yii::$app->session->setFlash('danger', $e->getMessage()); }
        return $this->redirect(['templates']);
    }

    public function actionQuestion($id = null, $section_id = null)
    {
        $this->assertPermission('exitInterviewManageTemplate');
        $model = $id ? ExitInterviewQuestion::find()->where(['id' => (int) $id])->with(['section', 'options'])->one() : new ExitInterviewQuestion([
            'section_id' => (int) $section_id,
            'question_type' => 'long_text',
            'sequence' => ((int) ExitInterviewQuestion::find()->where(['section_id' => (int) $section_id])->max('sequence')) + 1,
            'is_required' => 0,
        ]);
        if (!$model || !$model->section) throw new NotFoundHttpException('ไม่พบคำถาม');
        $version = ExitInterviewTemplateVersion::findOne($model->section->version_id);
        if (!$version || $version->status !== 'draft') throw new \yii\web\BadRequestHttpException('แก้ไขได้เฉพาะเวอร์ชันร่าง กรุณาคัดลอกเป็นเวอร์ชันใหม่ก่อน');
        if (!$model->isNewRecord) $model->options_text = implode("\n", array_map(static fn($o) => $o->value . '|' . $o->label, $model->options));
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->syncQuestionOptions($model, (string) $model->options_text);
            if (Yii::$app->request->isAjax) return $this->jsonSuccess('บันทึกคำถามเรียบร้อย', '#exit-templates');
            return $this->redirect(['templates']);
        }
        return $this->modalOrPage('_question_form', ['model' => $model], 'แก้ไขคำถาม');
    }

    private function syncQuestionOptions(ExitInterviewQuestion $question, string $text): void
    {
        if (!in_array($question->question_type, ['single_choice', 'multi_choice', 'ranking'], true)) return;
        \app\modules\hr\models\ExitInterviewQuestionOption::deleteAll(['question_id' => $question->id]);
        $sequence = 1;
        foreach (preg_split('/\r\n|\r|\n/', trim($text)) as $line) {
            if (trim($line) === '') continue;
            [$value, $label] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            if ($value === '' || $label === '') continue;
            $option = new \app\modules\hr\models\ExitInterviewQuestionOption(['question_id' => $question->id, 'value' => $value, 'label' => $label, 'sequence' => $sequence++, 'is_other' => $value === 'other']);
            if (!$option->save()) throw new \yii\web\BadRequestHttpException(implode(' ', $option->getFirstErrors()));
        }
    }

    public function actionPublish($id)
    {
        $this->assertPermission('exitInterviewManageTemplate');
        $version = ExitInterviewTemplateVersion::findOne($id);
        if (!$version || $version->status !== 'draft') throw new NotFoundHttpException('ไม่พบเวอร์ชันร่าง');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $version = ExitInterviewTemplateVersion::find()->where(['id' => $id])->with(['sections.questions.options'])->one();
            if (!$version || $version->status !== 'draft') throw new NotFoundHttpException('ไม่พบเวอร์ชันร่าง');
            $this->service->validateTemplateVersion($version);
            ExitInterviewTemplateVersion::updateAll(['status' => 'retired', 'updated_at' => date('Y-m-d H:i:s')], ['template_id' => $version->template_id, 'status' => 'published']);
            $version->status = 'published';
            $version->published_at = date('Y-m-d H:i:s');
            $version->published_by = Yii::$app->user->id;
            if (!$version->save()) throw new \yii\web\BadRequestHttpException(implode(' ', $version->getFirstErrors()));
            $transaction->commit();
        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack();
            throw $e;
        }
        Yii::$app->session->setFlash('success', 'เผยแพร่แบบสัมภาษณ์เวอร์ชัน ' . $version->version_no . ' แล้ว');
        return $this->redirect(['templates']);
    }

    public function actionDownloadTemplate()
    {
        $this->assertPermission('exitInterviewImport');
        $version = ExitInterviewTemplateVersion::published();
        if (!$version) {
            throw new \yii\web\BadRequestHttpException('ยังไม่มีแบบสัมภาษณ์เวอร์ชันที่เผยแพร่');
        }
        $questions = ExitInterviewQuestion::find()->alias('q')->innerJoinWith('section s')->where(['s.version_id' => $version->id])->orderBy(['s.sequence' => SORT_ASC, 'q.sequence' => SORT_ASC])->all();
        $sheet = (new Spreadsheet())->getActiveSheet();
        $headers = ['emp_id', 'exit_date', 'exit_type']; foreach ($questions as $question) $headers[] = $question->code;
        $sheet->fromArray($headers, null, 'A1'); $sheet->freezePane('A2'); $sheet->setTitle('Exit Interview');
        $path = Yii::getAlias('@runtime/exit-interview-template-' . date('YmdHis') . '.xlsx'); (new Xlsx($sheet->getParent()))->save($path);
        return Yii::$app->response->sendFile($path, 'exit-interview-template.xlsx')->on(Response::EVENT_AFTER_SEND, static fn() => @unlink($path));
    }

    public function actionImport()
    {
        $this->assertPermission('exitInterviewImport');
        if (Yii::$app->request->isPost && ($file = UploadedFile::getInstanceByName('import_file'))) {
            $rows = IOFactory::load($file->tempName)->getActiveSheet()->toArray(null, true, true, false);
            $headers = array_map('trim', array_shift($rows) ?: []); $created = 0; $errors = [];
            if (!$headers || in_array('', $headers, true) || count($headers) !== count(array_unique($headers))) {
                throw new \yii\web\BadRequestHttpException('หัวคอลัมน์ในไฟล์ต้องไม่ว่างและห้ามซ้ำกัน');
            }
            foreach ($rows as $index => $row) {
                $values = array_slice(array_pad($row, count($headers), null), 0, count($headers));
                $data = array_combine($headers, $values);
                $employee = Employees::findOne((int) ($data['emp_id'] ?? 0));
                if (!$employee) { $errors[] = 'แถว ' . ($index + 2) . ': ไม่พบบุคลากร'; continue; }
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $interview = $this->service->createInterview($employee, ['exit_date' => $data['exit_date'] ?: null, 'exit_type' => $data['exit_type'] ?: 'resignation', 'response_source' => 'excel_import']);
                    $answerInput = [];
                    foreach (ExitInterviewQuestion::find()->alias('q')->innerJoinWith('section s')->where(['s.version_id' => $interview->version_id])->all() as $question) {
                        if (!array_key_exists($question->code, $data) || $data[$question->code] === null || $data[$question->code] === '') continue;
                        $answerInput[$question->id] = in_array($question->question_type, ['ranking', 'multi_choice'], true) ? array_map('trim', explode('|', (string) $data[$question->code])) : $data[$question->code];
                    }
                    $this->service->saveAnswers($interview, $answerInput, true, 'นำเข้าจาก Excel', true, false);
                    $transaction->commit();
                    $created++;
                } catch (\Throwable $e) {
                    if ($transaction->isActive) $transaction->rollBack();
                    $errors[] = 'แถว ' . ($index + 2) . ': ' . $e->getMessage();
                }
            }
            Yii::$app->session->setFlash($errors ? 'warning' : 'success', 'นำเข้าสำเร็จ ' . $created . ' รายการ' . ($errors ? ' พบข้อผิดพลาด: ' . implode('; ', array_slice($errors, 0, 10)) : ''));
            return $this->redirect(['registry']);
        }
        return $this->render('import');
    }

    public function actionExportCsv()
    {
        $this->assertPermission('exitInterviewExportIdentified');
        $rows = ExitInterview::find()->with(['answers'])->orderBy(['exit_date' => SORT_DESC])->all();
        $stream = fopen('php://temp', 'w+'); fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['ID', 'ชื่อ', 'หน่วยงาน', 'ประเภทการออก', 'วันที่ออก', 'สถานะ', 'ช่องทาง']);
        foreach ($rows as $model) fputcsv($stream, array_map([$this, 'safeCsv'], [$model->id, $model->employee_name_snapshot, $model->department_name_snapshot, $model->exit_type, $model->exit_date, $model->status, $model->response_source]));
        rewind($stream); $content = stream_get_contents($stream); fclose($stream);
        return Yii::$app->response->sendContentAsFile($content, 'exit-interview-' . date('Ymd') . '.csv', ['mimeType' => 'text/csv; charset=UTF-8']);
    }

    public function safeCsv($value): string
    {
        $value = (string) $value;
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }

    private function findInterview($id): ExitInterview
    {
        $model = ExitInterview::find()->where(['id' => (int) $id])->with(['answers', 'version'])->one();
        if (!$model) throw new NotFoundHttpException('ไม่พบรายการสัมภาษณ์');
        return $model;
    }

    private function assertPermission(string $permission): void
    {
        if (!Yii::$app->user->can($permission) && !Yii::$app->user->can('admin')) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดำเนินการส่วนนี้');
    }

    private function assertAnyPermission(array $permissions): void
    {
        foreach ($permissions as $permission) {
            if (Yii::$app->user->can($permission)) return;
        }
        if (Yii::$app->user->can('admin')) return;
        throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดำเนินการส่วนนี้');
    }

    private function modalOrPage(string $view, array $params, string $title)
    {
        if (Yii::$app->request->isAjax) { Yii::$app->response->format = Response::FORMAT_JSON; return ['title' => $title, 'content' => $this->renderAjax($view, $params)]; }
        return $this->render($view, $params);
    }

    private function jsonSuccess(string $message, string $container): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['status' => 'success', 'message' => $message, 'container' => $container];
    }
}
