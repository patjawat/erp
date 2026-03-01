<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use app\models\Categorise;

const LEAVE_FORM_TEMPLATE_NAME = 'leave_form_template';
const LEAVE_TEMPLATE_RELATIVE_PATH = 'uploads/leave_form_template/template.pdf';

/**
 * การตั้งค่า — แบบฟอร์มใบลา (อัปโหลด PDF + กำหนดตำแหน่ง)
 */
class SettingController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าตั้งค่า');
        }
        return true;
    }

    /**
     * ตั้งค่าผู้อนุมัติใบลา — หน้าสรุปและลิงก์ไปจัดการระดับการอนุมัติ (approveV3)
     */
    public function actionApprovers()
    {
        return $this->render('approvers');
    }

    /**
     * ตั้งค่าฟอร์มใบลา — อัปโหลดเทมเพลต PDF และกำหนดตำแหน่งข้อมูล
     */
    public function actionLeaveTemplate()
    {
        $config = $this->getLeaveFormConfig();
        $hasTemplate = $this->hasTemplateFile();

        return $this->render('leave-template', [
            'config' => $config,
            'hasTemplate' => $hasTemplate,
            'templateUrl' => $hasTemplate ? Yii::getAlias('@web') . '/' . LEAVE_TEMPLATE_RELATIVE_PATH . '?t=' . time() : null,
        ]);
    }

    /**
     * อัปโหลดเทมเพลต PDF
     */
    public function actionUploadTemplate()
    {
        if (!Yii::$app->request->isPost) {
            return $this->redirect(['leave-template']);
        }
        $file = UploadedFile::getInstanceByName('template_pdf');
        if (!$file || $file->extension !== 'pdf') {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกไฟล์ PDF');
            return $this->redirect(['leave-template']);
        }
        $dir = Yii::getAlias('@webroot') . '/uploads/leave_form_template';
        FileHelper::createDirectory($dir);
        $path = $dir . '/template.pdf';
        if ($file->saveAs($path)) {
            $this->ensureConfigRecord();
            Yii::$app->session->setFlash('success', 'อัปโหลดเทมเพลต PDF เรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไฟล์ไม่สำเร็จ');
        }
        return $this->redirect(['leave-template']);
    }

    /**
     * หน้ากำหนดตำแหน่งข้อมูลบน PDF
     */
    public function actionPositions()
    {
        if (!$this->hasTemplateFile()) {
            Yii::$app->session->setFlash('warning', 'กรุณาอัปโหลดเทมเพลต PDF ก่อน');
            return $this->redirect(['leave-template']);
        }
        $config = $this->getLeaveFormConfig();
        $fields = $this->getLeaveFormFields();

        return $this->render('positions', [
            'config' => $config,
            'fields' => $fields,
            'templateUrl' => Yii::getAlias('@web') . '/' . LEAVE_TEMPLATE_RELATIVE_PATH . '?t=' . time(),
        ]);
    }

    /**
     * บันทึกตำแหน่ง (AJAX) — รองรับทั้ง POST form และ JSON body
     */
    public function actionSavePositions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $positions = Yii::$app->request->post('positions', []);
        if (empty($positions) && Yii::$app->request->getIsPost()) {
            $raw = Yii::$app->request->getRawBody();
            if ($raw) {
                $body = json_decode($raw, true);
                $positions = $body['positions'] ?? [];
            }
        }
        if (!is_array($positions)) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'];
        }
        $defaults = $this->getDefaultFields();
        $config = $this->getLeaveFormConfig();
        $merged = [];
        foreach ($positions as $key => $pos) {
            $merged[$key] = array_merge(
                ['label' => $defaults[$key]['label'] ?? $key, 'x' => 0, 'y' => 0, 'fontSize' => 11],
                array_intersect_key($pos, array_flip(['x', 'y', 'fontSize']))
            );
        }
        $config['fields'] = $merged;
        $cat = $this->getConfigRecord();
        $cat->data_json = json_encode($config);
        if ($cat->save(false)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
    }

    protected function hasTemplateFile()
    {
        $path = Yii::getAlias('@webroot') . '/' . LEAVE_TEMPLATE_RELATIVE_PATH;
        return is_file($path);
    }

    protected function getLeaveFormConfig()
    {
        $cat = $this->getConfigRecord();
        $json = $cat->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        $json = is_array($json) ? $json : [];
        if (empty($json['fields'])) {
            $json['fields'] = $this->getDefaultFields();
        }
        return $json;
    }

    protected function getConfigRecord()
    {
        $cat = Categorise::findOne(['name' => LEAVE_FORM_TEMPLATE_NAME]);
        if (!$cat) {
            $cat = new Categorise();
            $cat->name = LEAVE_FORM_TEMPLATE_NAME;
            $cat->code = 'default';
            $cat->title = 'ฟอร์มใบลา';
            $cat->data_json = json_encode(['fields' => $this->getDefaultFields()]);
            $cat->save(false);
        }
        return $cat;
    }

    protected function ensureConfigRecord()
    {
        $this->getConfigRecord();
    }

    protected function getDefaultFields()
    {
        return [
            'emp_fullname' => ['label' => 'ชื่อ-นามสกุลผู้ขอลา', 'x' => 30, 'y' => 50, 'fontSize' => 12],
            'department' => ['label' => 'หน่วยงาน/แผนก', 'x' => 30, 'y' => 58, 'fontSize' => 11],
            'leave_type_title' => ['label' => 'ประเภทการลา', 'x' => 30, 'y' => 66, 'fontSize' => 11],
            'date_start' => ['label' => 'วันที่เริ่มลา', 'x' => 30, 'y' => 74, 'fontSize' => 11],
            'date_end' => ['label' => 'วันที่สิ้นสุด', 'x' => 80, 'y' => 74, 'fontSize' => 11],
            'total_days' => ['label' => 'จำนวนวัน', 'x' => 30, 'y' => 82, 'fontSize' => 11],
            'reason' => ['label' => 'เหตุผลการลา', 'x' => 30, 'y' => 90, 'fontSize' => 11],
            'address' => ['label' => 'ที่อยู่ที่ติดต่อได้', 'x' => 30, 'y' => 98, 'fontSize' => 10],
            'contact_phone' => ['label' => 'เบอร์โทรติดต่อ', 'x' => 30, 'y' => 106, 'fontSize' => 10],
            'place_go' => ['label' => 'สถานที่ไป', 'x' => 30, 'y' => 114, 'fontSize' => 10],
            'create_date' => ['label' => 'วันที่ยื่นคำขอ', 'x' => 30, 'y' => 122, 'fontSize' => 10],
        ];
    }

    protected function getLeaveFormFields()
    {
        $config = $this->getLeaveFormConfig();
        $fields = $config['fields'] ?? [];
        $defaults = $this->getDefaultFields();
        foreach ($defaults as $key => $def) {
            if (!isset($fields[$key])) {
                $fields[$key] = $def;
            } else {
                $fields[$key]['label'] = $def['label'];
            }
        }
        return $fields;
    }
}
