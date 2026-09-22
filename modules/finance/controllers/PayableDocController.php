<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayableDocument;
use app\modules\finance\components\FinancePayableDocumentBuilder as Builder;
use app\modules\purchase\components\DocRenderer;
use app\modules\purchase\models\DocTemplate;

/**
 * ใบอนุมัติจ่ายเจ้าหนี้ (ต่อบิล) — สร้างจากทะเบียนเจ้าหนี้ แก้บนจอ แล้วพิมพ์ PDF
 * reuse เครื่องพิมพ์ (DocRenderer) + หน้าจอแก้ไขของงานพัสดุ เช่นเดียวกับเอกสารเงินยืม
 */
class PayableDocController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['open', 'print'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['save', 'reset'], 'roles' => ['financeOperate']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['save' => ['POST'], 'reset' => ['POST']]],
        ]);
    }

    /** สร้าง/เปิด snapshot ใบอนุมัติจ่ายของบิลที่เลือก (คืน JSON ให้ modal แก้ไข) */
    public function actionOpen($payable_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $payable = FinancePayable::findOne((int) $payable_id);
        if ($payable === null) {
            return ['status' => 'error', 'message' => 'ไม่พบทะเบียนเจ้าหนี้'];
        }

        $document = FinancePayableDocument::findOne([
            'payable_id' => $payable->id, 'template_code' => Builder::CODE, 'deleted_at' => null,
        ]);
        if ($document === null) {
            $document = new FinancePayableDocument([
                'payable_id' => (int) $payable->id,
                'template_code' => Builder::CODE,
                'title' => 'ใบอนุมัติจ่ายเจ้าหนี้ · ' . ($payable->payable_no ?: ('#' . $payable->id)),
                'ref_type' => 'none',
                'thai_year' => (int) date('Y') + 543,
                'doc_date' => date('Y-m-d'),
                'body_html' => Builder::build($payable),
                'orientation' => 'portrait',
                'emblem' => DocTemplate::EMBLEM_NONE,
                'font_size' => 14,
                'margin_json' => ['top' => 15, 'right' => 15, 'bottom' => 15, 'left' => 20],
                'status' => FinancePayableDocument::STATUS_DRAFT,
                'data_json' => ['template_version' => Builder::version()],
            ]);
            if (!$document->save()) {
                return ['status' => 'error', 'message' => 'สร้างเอกสารไม่สำเร็จ: ' . implode(' ', array_merge(...array_values($document->getErrors())))];
            }
        } elseif ($this->upgrade($document, $payable)) {
            $document->save(false);
        }

        return $this->editorResponse($document);
    }

    public function actionSave($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $document = $this->findDocument($id);
        if ($document->status === FinancePayableDocument::STATUS_FINAL) {
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

    public function actionReset($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $document = $this->findDocument($id);
        $payable = FinancePayable::findOne((int) $document->payable_id);
        if ($payable === null) {
            return ['status' => 'error', 'message' => 'ไม่พบทะเบียนเจ้าหนี้ต้นทาง'];
        }
        $document->body_html = Builder::build($payable);
        $document->data_json = array_merge((array) $document->data_json, ['template_version' => Builder::version()]);
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

    private function editorResponse(FinancePayableDocument $document): array
    {
        $routes = [
            'save' => ['/finance/payable-doc/save', 'id' => $document->id],
            'reset' => ['/finance/payable-doc/reset', 'id' => $document->id],
            'print' => ['/finance/payable-doc/print', 'id' => $document->id],
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

    private function upgrade(FinancePayableDocument $document, FinancePayable $payable): bool
    {
        $data = is_array($document->data_json) ? $document->data_json : [];
        if ((int) ($data['template_version'] ?? 0) === Builder::version()) {
            return false;
        }
        $document->body_html = Builder::build($payable);
        $document->data_json = array_merge($data, ['template_version' => Builder::version()]);
        return true;
    }

    private function findDocument($id): FinancePayableDocument
    {
        $document = FinancePayableDocument::findOne(['id' => (int) $id, 'deleted_at' => null]);
        if ($document === null) {
            throw new NotFoundHttpException('ไม่พบเอกสารที่ต้องการ');
        }
        return $document;
    }
}
