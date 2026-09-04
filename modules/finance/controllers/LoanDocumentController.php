<?php

namespace app\modules\finance\controllers;

use app\modules\finance\components\FinanceLoanDocumentBuilder;
use app\modules\finance\components\FinanceLoanDocumentCatalog;
use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanDocument;
use app\modules\finance\models\FinanceLoanFollowup;
use app\modules\purchase\components\DocRenderer;
use app\modules\purchase\models\DocTemplate;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ชุดเอกสารของงานเงินยืม — สร้างจากทะเบียน แก้บนจอ แล้วสั่งพิมพ์
 *
 * ใช้เครื่องพิมพ์เดียวกับงานพัสดุและชุดเอกสารเดินทางไปราชการ คือ DocRenderer
 * กับหน้าจอแก้ไขที่ modules/purchase/views/doc/editor จึงไม่ต้องทำระบบพิมพ์ซ้ำ
 * และเมื่อมีการปรับฟอนต์หรือระยะขอบกระดาษที่ส่วนกลาง เอกสารชุดนี้ได้ตามไปด้วย
 */
class LoanDocumentController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['financeOperate']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['save' => ['POST'], 'reset' => ['POST']]],
        ]);
    }

    /** หน้ารวมการพิมพ์ เลือกใบยืมหนึ่งใบแล้วเลือกแม่แบบ */
    public function actionIndex($loan_id = null)
    {
        $loans = FinanceLoan::find()
            ->with(['expenseType'])
            ->orderBy(['borrowed_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(200)
            ->all();

        $label = static fn(FinanceLoan $loan): string => trim(
            $loan->contract_no . ' · ' . $loan->borrower_name
            . ($loan->purpose !== '' ? ' · ' . mb_substr($loan->purpose, 0, 60) : '')
        );

        $loanOptions = [];
        foreach ($loans as $loan) {
            $loanOptions[(int) $loan->id] = $label($loan);
        }

        // เปิดมาจากปุ่มในหน้ารายละเอียด ใบที่ส่งมาอาจเก่ากว่า 200 ใบล่าสุดที่โหลดไว้
        $selectedId = null;
        if ($loan_id !== null) {
            $selectedId = (int) $loan_id;
            if (!isset($loanOptions[$selectedId])) {
                $selected = FinanceLoan::findOne($selectedId);
                if ($selected === null) {
                    $selectedId = null;
                } else {
                    $loanOptions = [$selectedId => $label($selected)] + $loanOptions;
                }
            }
        }
        if ($selectedId === null) {
            $selectedId = $loanOptions !== [] ? array_key_first($loanOptions) : null;
        }

        return $this->render('index', [
            'documentTypes' => FinanceLoanDocumentCatalog::all(),
            'selectableCodes' => array_column(FinanceLoanDocumentCatalog::selectable(), 'code'),
            'loanOptions' => $loanOptions,
            'defaultLoanId' => $selectedId,
        ]);
    }

    /** สร้างหรือเปิด snapshot เอกสารของใบยืมที่เลือก */
    public function actionOpen($loan_id, $code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $type = FinanceLoanDocumentCatalog::find((string) $code);
        if ($type === null || $type['status'] !== FinanceLoanDocumentCatalog::STATUS_SOURCE_READY) {
            return ['status' => 'error', 'message' => 'เอกสารชนิดนี้ยังไม่มีแม่แบบพร้อมใช้งาน'];
        }

        $loan = FinanceLoan::find()->where(['id' => (int) $loan_id])->with(['items', 'settlements', 'account', 'expenseType'])->one();
        if ($loan === null) {
            return ['status' => 'error', 'message' => 'ไม่พบใบยืมที่เลือก'];
        }

        $document = FinanceLoanDocument::findOne(['loan_id' => $loan->id, 'template_code' => (string) $code, 'deleted_at' => null]);
        if ($document === null) {
            $document = new FinanceLoanDocument([
                'loan_id' => (int) $loan->id,
                'template_code' => (string) $code,
                'title' => (string) $type['name'] . ' · ' . $loan->contract_no,
                'ref_type' => 'none',
                'ref_id' => null,
                'thai_year' => (int) $loan->fiscal_year,
                'doc_date' => date('Y-m-d'),
                'body_html' => FinanceLoanDocumentBuilder::build((string) $code, $loan),
                'orientation' => 'portrait',
                'emblem' => ($type['emblem'] ?? 'none') === 'small' ? DocTemplate::EMBLEM_SMALL : DocTemplate::EMBLEM_NONE,
                'font_size' => 14,
                'margin_json' => ['top' => 15, 'right' => 15, 'bottom' => 15, 'left' => 20],
                'status' => FinanceLoanDocument::STATUS_DRAFT,
                'emp_id' => $loan->borrower_emp_id ? (int) $loan->borrower_emp_id : null,
                'data_json' => [
                    'source_updated_at' => $loan->updated_at,
                    'template_version' => FinanceLoanDocumentBuilder::version((string) $code),
                ],
            ]);
            if (!$document->save()) {
                return ['status' => 'error', 'message' => 'สร้างเอกสารไม่สำเร็จ: ' . implode(' ', array_merge(...array_values($document->getErrors())))];
            }
        } elseif ($this->upgrade($document, $loan)) {
            $document->save(false);
        }

        return $this->editorResponse($document);
    }

    /**
     * เปิดเอกสารหนังสือติดตามฉบับหนึ่ง
     *
     * แยกจาก actionOpen เพราะหนังสือติดตามอ้างรายการติดตาม ไม่ใช่ชนิดเอกสารเฉย ๆ
     * และใบยืมหนึ่งใบมีได้หลายฉบับ จึงเก็บด้วยรหัสที่ต่อท้ายเลขครั้งที่ไว้
     */
    public function actionOpenLetter($followup_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $letter = FinanceLoanFollowup::findOne((int) $followup_id);
        if ($letter === null || $letter->channel !== FinanceLoanFollowup::CHANNEL_LETTER) {
            return ['status' => 'error', 'message' => 'ไม่พบหนังสือติดตามที่เลือก'];
        }
        $loan = FinanceLoan::find()->where(['id' => $letter->loan_id])->with(['items', 'settlements', 'account', 'expenseType'])->one();
        if ($loan === null) {
            return ['status' => 'error', 'message' => 'ไม่พบใบยืมต้นทาง'];
        }

        $code = FinanceLoanDocumentCatalog::letterCode((int) $letter->letter_seq);
        $document = FinanceLoanDocument::findOne(['loan_id' => $loan->id, 'template_code' => $code, 'deleted_at' => null]);
        if ($document === null) {
            $document = new FinanceLoanDocument([
                'loan_id' => (int) $loan->id,
                'template_code' => $code,
                'title' => 'บันทึกขอติดตามลูกหนี้เงินยืม ครั้งที่ ' . (int) $letter->letter_seq . ' · ' . $loan->contract_no,
                'ref_type' => 'none',
                'thai_year' => (int) $loan->fiscal_year,
                'doc_date' => $letter->letter_date ?: date('Y-m-d'),
                'doc_no' => $letter->letter_no ? mb_substr((string) $letter->letter_no, 0, 50) : null,
                'body_html' => FinanceLoanDocumentBuilder::buildLetter($loan, $letter),
                'orientation' => 'portrait',
                'emblem' => DocTemplate::EMBLEM_SMALL,
                'font_size' => 14,
                'margin_json' => ['top' => 15, 'right' => 15, 'bottom' => 15, 'left' => 20],
                'status' => FinanceLoanDocument::STATUS_DRAFT,
                'emp_id' => $loan->borrower_emp_id ? (int) $loan->borrower_emp_id : null,
                'data_json' => [
                    'followup_id' => (int) $letter->id,
                    'template_version' => FinanceLoanDocumentBuilder::version(FinanceLoanDocumentCatalog::FOLLOWUP_MEMO),
                ],
            ]);
            if (!$document->save()) {
                return ['status' => 'error', 'message' => 'สร้างหนังสือไม่สำเร็จ: ' . implode(' ', array_merge(...array_values($document->getErrors())))];
            }
        }

        return $this->editorResponse($document);
    }

    /** บันทึกเนื้อหาจากหน้าจอแก้ไข */
    public function actionSave($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $document = $this->findDocument($id);
        if ($document->status === FinanceLoanDocument::STATUS_FINAL) {
            return ['status' => 'error', 'message' => 'เอกสารฉบับนี้ถูกล็อกแล้ว'];
        }

        $post = Yii::$app->request->post();
        if (array_key_exists('body_html', $post)) {
            $document->body_html = DocRenderer::normalize((string) $post['body_html']);
        }
        if (array_key_exists('font_size', $post)) {
            $document->font_size = (int) $post['font_size'];
        }
        if (array_key_exists('emblem', $post) && array_key_exists((string) $post['emblem'], DocTemplate::emblemList())) {
            $document->emblem = (string) $post['emblem'];
        }

        return $document->save()
            ? ['status' => 'success', 'message' => 'บันทึกร่างแล้ว']
            : ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ'];
    }

    /** ดึงข้อมูลจากทะเบียนกลับมาทับ snapshot ที่แก้ไว้ */
    public function actionReset($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $document = $this->findDocument($id);
        $loan = FinanceLoan::find()->where(['id' => $document->loan_id])->with(['items', 'settlements', 'account', 'expenseType'])->one();
        if ($loan === null) {
            return ['status' => 'error', 'message' => 'ไม่พบใบยืมต้นทาง'];
        }

        $document->body_html = $this->rebuild($document, $loan);
        if ($document->body_html === null) {
            return ['status' => 'error', 'message' => 'ไม่พบหนังสือติดตามที่เอกสารนี้อ้างถึง'];
        }
        $document->data_json = array_merge((array) $document->data_json, [
            'source_updated_at' => $loan->updated_at,
            'template_version' => FinanceLoanDocumentBuilder::version(FinanceLoanDocumentCatalog::baseCode((string) $document->template_code)),
        ]);
        if (!$document->save()) {
            return ['status' => 'error', 'message' => 'ดึงข้อมูลใหม่ไม่สำเร็จ'];
        }

        return ['status' => 'success', 'message' => 'ดึงข้อมูลจากทะเบียนกลับมาแล้ว', 'body_html' => DocRenderer::body($document)];
    }

    public function actionPrint($id)
    {
        $document = $this->findDocument($id);
        $document->markPrinted();

        return Yii::$app->response->sendContentAsFile(
            DocRenderer::pdf($document),
            $document->safeFileName('pdf'),
            ['mimeType' => 'application/pdf', 'inline' => true]
        );
    }

    /**
     * สร้างเนื้อหาใหม่จากทะเบียน รองรับทั้งเอกสารทั่วไปและหนังสือติดตามรายฉบับ
     * คืน null เมื่อเป็นหนังสือติดตามที่หารายการต้นทางไม่เจอแล้ว (ถูกลบไป)
     */
    private function rebuild(FinanceLoanDocument $document, FinanceLoan $loan): ?string
    {
        $base = FinanceLoanDocumentCatalog::baseCode((string) $document->template_code);
        if ($base !== FinanceLoanDocumentCatalog::FOLLOWUP_MEMO) {
            return FinanceLoanDocumentBuilder::build($base, $loan);
        }
        $data = (array) $document->data_json;
        $letter = FinanceLoanFollowup::findOne((int) ($data['followup_id'] ?? 0));
        return $letter ? FinanceLoanDocumentBuilder::buildLetter($loan, $letter) : null;
    }

    private function editorResponse(FinanceLoanDocument $document): array
    {
        $routes = [
            'save' => ['/finance/loan-document/save', 'id' => $document->id],
            'reset' => ['/finance/loan-document/reset', 'id' => $document->id],
            'print' => ['/finance/loan-document/print', 'id' => $document->id],
        ];

        return [
            'status' => 'success',
            'title' => '<i class="bi bi-file-earmark-text me-1"></i>' . Html::encode($document->title)
                . ' <span class="badge bg-warning-subtle text-warning-emphasis ms-1">แก้ไขได้</span>',
            'content' => $this->renderAjax('@app/modules/purchase/views/doc/editor', ['model' => $document, 'routes' => $routes]),
            'footer' => $this->renderAjax('@app/modules/purchase/views/doc/_editor_footer', ['model' => $document, 'routes' => $routes, 'showWord' => false]),
            'initCallback' => 'erpDocEditorInit',
        ];
    }

    /**
     * สร้างเนื้อหาใหม่ให้เอกสารที่บันทึกไว้ด้วยแม่แบบรุ่นเก่ากว่าปัจจุบัน
     *
     * เทียบเลขรุ่นจาก data_json ไม่ใช่จากเครื่องหมายในเนื้อหา เพราะ Doc::beforeSave
     * กรอง body_html ผ่าน HtmlPurifier ทุกครั้ง ซึ่งลบทั้ง HTML comment และแท็กที่
     * ไม่อยู่ใน ALLOWED_HTML ทิ้ง เครื่องหมายที่ฝังในเนื้อหาจึงหายตั้งแต่บันทึกครั้งแรก
     * แล้วการเปิดครั้งถัดไปจะเข้าใจผิดว่าเป็นรุ่นเก่า และสร้างใหม่ทับงานที่ผู้ใช้แก้ไว้
     */
    private function upgrade(FinanceLoanDocument $document, FinanceLoan $loan): bool
    {
        $code = (string) $document->template_code;
        if (FinanceLoanDocumentCatalog::find($code) === null) {
            return false;
        }
        $base = FinanceLoanDocumentCatalog::baseCode($code);
        $data = is_array($document->data_json) ? $document->data_json : [];
        if ((int) ($data['template_version'] ?? 0) === FinanceLoanDocumentBuilder::version($base)) {
            return false;
        }
        $body = $this->rebuild($document, $loan);
        if ($body === null) {
            return false;
        }
        $document->body_html = $body;
        $document->data_json = array_merge($data, [
            'source_updated_at' => $loan->updated_at,
            'template_version' => FinanceLoanDocumentBuilder::version($base),
        ]);
        return true;
    }

    private function findDocument($id): FinanceLoanDocument
    {
        $document = FinanceLoanDocument::findOne(['id' => (int) $id, 'deleted_at' => null]);
        if ($document === null) {
            throw new NotFoundHttpException('ไม่พบเอกสารที่ต้องการ');
        }
        return $document;
    }
}
