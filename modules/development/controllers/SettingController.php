<?php

namespace app\modules\development\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\filters\AccessControl;
use yii\helpers\Url;
use app\models\Categorise;

const TRAVEL_FORM_TEMPLATE_NAME = 'travel_form_template';
const TRAVEL_TEMPLATE_STORE_DIR = 'modules/filemanager/fileupload/development_templates';

/**
 * การตั้งค่าแบบฟอร์มไปราชการ — จัดการข้อมูลหลัก + Template PDF (อัปโหลด + กำหนดตำแหน่ง) แบบเดียวกับ modules/leave
 */
class SettingController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (in_array($action->id, ['serve-template'])) {
            return true;
        }
        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าตั้งค่า');
        }
        return true;
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['hr', 'admin']],
                ],
            ],
        ];
    }

    public function actionSystem()
    {
        return $this->render('system');
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    // ─────────────────────────────────────────────
    //  Template แบบฟอร์มไปราชการ (เหมือน leave)
    // ─────────────────────────────────────────────

    /**
     * ไม่ส่ง code → แสดงรายการ template (default)
     * ส่ง code → หน้ารายละเอียด (อัปโหลด PDF + ลิงก์กำหนดตำแหน่ง)
     */
    public function actionTemplate($code = null)
    {
        if ($code === null) {
            return $this->render('template', [
                'hasDefault' => $this->hasTemplateFile('default'),
                'defaultUrl' => $this->hasTemplateFile('default')
                    ? Url::to(['/development/setting/serve-template', 'code' => 'default'])
                    : null,
            ]);
        }

        $code = $code === '' ? 'default' : $code;
        $hasTemplate = $this->hasTemplateFile($code);

        return $this->render('template-detail', [
            'code'          => $code,
            'hasTemplate'   => $hasTemplate,
            'templateUrl'   => $hasTemplate ? Url::to(['/development/setting/serve-template', 'code' => $code]) : null,
        ]);
    }

    public function actionServeTemplate($code = 'default')
    {
        $path = $this->getTemplatePath($code);
        if (!is_file($path)) {
            throw new \yii\web\NotFoundHttpException('ไม่พบไฟล์เทมเพลต');
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'application/pdf');
        Yii::$app->response->headers->set('Content-Disposition', 'inline; filename="template-' . $code . '.pdf"');
        Yii::$app->response->content = file_get_contents($path);
        return Yii::$app->response;
    }

    public function actionUploadTemplate($code = 'default')
    {
        $isAjax = Yii::$app->request->getIsAjax()
            || Yii::$app->request->getHeaders()->get('X-Requested-With') === 'XMLHttpRequest';

        if (!Yii::$app->request->isPost) {
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => 'กรุณาเลือกไฟล์ PDF'];
            }
            return $this->redirect(['/development/setting/template', 'code' => $code]);
        }

        $file = $this->getUploadedPdfFile();
        if ($file === null) {
            $err = 'กรุณาเลือกไฟล์ PDF';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/development/setting/template', 'code' => $code]);
        }

        if (!$this->validatePdfFile($file)) {
            $err = 'อนุญาตเฉพาะไฟล์ PDF เท่านั้น';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/development/setting/template', 'code' => $code]);
        }

        $path = $this->getTemplatePath($code);
        FileHelper::createDirectory(dirname($path));

        if (!$file->saveAs($path)) {
            $err = 'บันทึกไฟล์ไม่สำเร็จ';
            if ($isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'error' => $err];
            }
            Yii::$app->session->setFlash('error', $err);
            return $this->redirect(['/development/setting/template', 'code' => $code]);
        }

        $this->ensureConfigRecord($code);

        if ($isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true, 'message' => 'อัปโหลดเทมเพลต PDF เรียบร้อย'];
        }
        Yii::$app->session->setFlash('success', 'อัปโหลดเทมเพลต PDF เรียบร้อย');
        return $this->redirect(['/development/setting/template', 'code' => $code]);
    }

    public function actionPositions($code = 'default')
    {
        $effectiveCode = $this->hasTemplateFile($code) ? $code : 'default';

        if (!$this->hasTemplateFile($effectiveCode)) {
            Yii::$app->session->setFlash('warning', 'กรุณาอัปโหลดเทมเพลต PDF ก่อน');
            return $this->redirect(['/development/setting/template', 'code' => $code]);
        }

        $config       = $this->getTravelFormConfig($effectiveCode);
        $items        = $this->getTravelFormItems($effectiveCode);
        $fieldLabels  = $this->getDefaultFields();

        return $this->render('positions', [
            'code'         => $code,
            'effectiveCode'=> $effectiveCode,
            'config'       => $config,
            'items'        => $items,
            'fieldLabels'  => $fieldLabels,
            'signatureKeys' => $this->getSignatureKeys(),
            'templateUrl'  => Url::to(['/development/setting/serve-template', 'code' => $effectiveCode]),
        ]);
    }

    public function actionSavePositions($code = 'default')
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $positions  = Yii::$app->request->post('positions', []);
        $dateFormat = Yii::$app->request->post('date_format');
        if (Yii::$app->request->getIsPost()) {
            $raw = Yii::$app->request->getRawBody();
            if ($raw && is_string($raw) && preg_match('/^\s*\{/', $raw)) {
                $body = json_decode($raw, true);
                if (is_array($body)) {
                    if (!empty($body['positions'])) $positions = $body['positions'];
                    if (isset($body['date_format']) && $body['date_format'] !== '') $dateFormat = (string) $body['date_format'];
                }
            }
        }
        if (!is_array($positions)) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'];
        }
        $defaults = $this->getDefaultFields();
        $sigKeys  = $this->getSignatureKeys();
        $config   = $this->getTravelFormConfig($code);
        if ($dateFormat !== null && in_array($dateFormat, ['short', 'medium', 'long', 'numeric'], true)) {
            $config['date_format'] = $dateFormat;
        }
        $items = [];
        foreach ($positions as $itemId => $pos) {
            if (!is_array($pos)) continue;
            $key = isset($pos['key']) ? (string) $pos['key'] : '';
            if ($key === '' || !isset($defaults[$key])) continue;
            $row = [
                'id' => $itemId, 'key' => $key,
                'x' => (float) ($pos['x'] ?? 0), 'y' => (float) ($pos['y'] ?? 0),
                'fontSize' => (int) ($pos['fontSize'] ?? 15), 'bold' => (int) ($pos['bold'] ?? 0), 'enabled' => (int) ($pos['enabled'] ?? 1),
            ];
            if (in_array($key, $sigKeys, true)) {
                $row['width']  = (float) ($pos['width'] ?? $defaults[$key]['width'] ?? 35);
                $row['height'] = (float) ($pos['height'] ?? $defaults[$key]['height'] ?? 15);
            }
            $items[] = $row;
        }
        $config['items'] = $items;
        $cat = $this->getConfigRecord($code);
        $cat->data_json = json_encode($config);
        if ($cat->save(false)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
    }

    protected function getTemplatePath(string $code = 'default'): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '', $code) ?: 'default';
        return Yii::getAlias('@app') . '/' . TRAVEL_TEMPLATE_STORE_DIR . '/' . $safe . '/template.pdf';
    }

    protected function hasTemplateFile(string $code = 'default'): bool
    {
        return is_file($this->getTemplatePath($code));
    }

    protected function getConfigRecord(string $code = 'default'): Categorise
    {
        $cat = Categorise::findOne(['name' => TRAVEL_FORM_TEMPLATE_NAME, 'code' => $code]);
        if (!$cat) {
            $defaults = $this->getDefaultFields();
            $sigKeys  = $this->getSignatureKeys();
            $items = [];
            foreach ($defaults as $key => $def) {
                $row = [
                    'id' => 'legacy_' . $key, 'key' => $key,
                    'x' => (float) ($def['x'] ?? 0), 'y' => (float) ($def['y'] ?? 0),
                    'fontSize' => (int) ($def['fontSize'] ?? 15), 'bold' => (int) ($def['bold'] ?? 0), 'enabled' => (int) ($def['enabled'] ?? 1),
                ];
                if (in_array($key, $sigKeys, true)) {
                    $row['width']  = (float) ($def['width']  ?? 35);
                    $row['height'] = (float) ($def['height'] ?? 15);
                }
                $items[] = $row;
            }
            $cat = new Categorise();
            $cat->name      = TRAVEL_FORM_TEMPLATE_NAME;
            $cat->code      = $code;
            $cat->title     = $code === 'default' ? 'แบบฟอร์มไปราชการ (default)' : 'แบบฟอร์มไปราชการ (' . $code . ')';
            $cat->data_json = json_encode(['items' => $items, 'date_format' => 'medium']);
            $cat->save(false);
        }
        return $cat;
    }

    protected function ensureConfigRecord(string $code = 'default'): void
    {
        $this->getConfigRecord($code);
    }

    protected function getTravelFormConfig(string $code = 'default'): array
    {
        $cat  = $this->getConfigRecord($code);
        $json = $cat->data_json;
        if (is_string($json)) $json = json_decode($json, true);
        $json = is_array($json) ? $json : [];
        if (empty($json['items'])) $json['fields'] = $this->getDefaultFields();
        return $json;
    }

    protected function getTravelFormItems(string $code = 'default'): array
    {
        $config   = $this->getTravelFormConfig($code);
        $defaults = $this->getDefaultFields();
        $sigKeys  = $this->getSignatureKeys();
        if (!empty($config['items'])) {
            $list = [];
            foreach ($config['items'] as $item) {
                $key = $item['key'] ?? '';
                $row = [
                    'id' => $item['id'] ?? uniqid('item_'), 'key' => $key,
                    'x' => (float) ($item['x'] ?? 0), 'y' => (float) ($item['y'] ?? 0),
                    'fontSize' => (int) ($item['fontSize'] ?? 15), 'bold' => !empty($item['bold']), 'enabled' => isset($item['enabled']) ? (int) $item['enabled'] : 1,
                    'label' => $defaults[$key]['label'] ?? $key,
                ];
                if (in_array($key, $sigKeys, true)) {
                    $row['width']  = (float) ($item['width'] ?? $defaults[$key]['width'] ?? 35);
                    $row['height'] = (float) ($item['height'] ?? $defaults[$key]['height'] ?? 15);
                }
                $list[] = $row;
            }
            return $list;
        }
        $items = [];
        foreach ($defaults as $key => $f) {
            $row = [
                'id' => 'legacy_' . $key, 'key' => $key,
                'x' => (float) ($f['x'] ?? 0), 'y' => (float) ($f['y'] ?? 0),
                'fontSize' => (int) ($f['fontSize'] ?? 15), 'bold' => !empty($f['bold']), 'enabled' => (int) ($f['enabled'] ?? 1),
                'label' => $f['label'] ?? $key,
            ];
            if (in_array($key, $sigKeys, true)) {
                $row['width']  = (float) ($f['width'] ?? 35);
                $row['height'] = (float) ($f['height'] ?? 15);
            }
            $items[] = $row;
        }
        return $items;
    }

    protected function getUploadedPdfFile(): ?UploadedFile
    {
        foreach (['template_pdf', 'file', 'pdf_file', 'upload'] as $name) {
            $file = UploadedFile::getInstanceByName($name);
            if ($file !== null) return $file;
        }
        return null;
    }

    protected function validatePdfFile($file): bool
    {
        if (!$file || !$file->tempName) return false;
        $path = $file->tempName;
        if (!file_exists($path) || !is_readable($path)) return false;
        $head = @file_get_contents($path, false, null, 0, 8);
        if ($head !== false && strpos($head, '%PDF') === 0) return true;
        $ext = strtolower((string) ($file->extension ?? ''));
        return $ext === 'pdf' || (pathinfo($file->name ?? '', PATHINFO_EXTENSION) === 'pdf');
    }

    /** ฟิลด์เริ่มต้นสำหรับใบขอไปราชการ */
    protected function getDefaultFields(): array
    {
        return [
            'doc_number'      => ['label' => 'เลขที่',              'x' => 30,  'y' => 40,  'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'doc_date'        => ['label' => 'วันที่',               'x' => 120, 'y' => 40,  'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'emp_fullname'    => ['label' => 'ชื่อ-นามสกุลผู้ขอ',    'x' => 30,  'y' => 55,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'emp_position'    => ['label' => 'ตำแหน่ง',             'x' => 30,  'y' => 62,  'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'topic'           => ['label' => 'หัวข้อ/เรื่อง',        'x' => 30,  'y' => 75,  'fontSize' => 15, 'bold' => 0, 'enabled' => 1],
            'location'        => ['label' => 'สถานที่',             'x' => 30,  'y' => 85,  'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'location_org'    => ['label' => 'หน่วยงานที่จัด',       'x' => 30,  'y' => 92,  'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'date_start'      => ['label' => 'ตั้งแต่วันที่',        'x' => 30,  'y' => 102, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'date_end'        => ['label' => 'ถึงวันที่',           'x' => 100, 'y' => 102, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'vehicle_date_start' => ['label' => 'วันออกเดินทาง',   'x' => 30,  'y' => 112, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'vehicle_date_end'   => ['label' => 'วันกลับ',          'x' => 100, 'y' => 112, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'total_days'      => ['label' => 'จำนวนวัน',            'x' => 30,  'y' => 122, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'distance'        => ['label' => 'ระยะทาง',            'x' => 100, 'y' => 132, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'vehicle_type'    => ['label' => 'เดินทางโดย',          'x' => 100, 'y' => 122, 'fontSize' => 14, 'bold' => 0, 'enabled' => 1],
            'signature_approver' => ['label' => 'ลายเซ็นผู้อนุมัติ', 'x' => 30,  'y' => 250, 'fontSize' => 12, 'bold' => 0, 'enabled' => 1, 'width' => 35, 'height' => 15],
        ];
    }

    protected function getSignatureKeys(): array
    {
        return ['signature_approver'];
    }
}
