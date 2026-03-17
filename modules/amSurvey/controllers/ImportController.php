<?php

namespace app\modules\amSurvey\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use app\modules\amSurvey\models\AssetSurvey;
use app\modules\amSurvey\models\CsvImportForm;
use app\modules\amSurvey\services\CsvImportService;
use app\modules\hr\models\Employees;

/**
 * CSV upload for survey: parse, match, create survey items in batch.
 */
class ImportController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ];
    }

    /**
     * ดาวน์โหลดไฟล์ตัวอย่าง CSV สำหรับนำเข้าสำรวจ
     * คอลัมน์แรก = หมายเลขครุภัณฑ์ (บังคับ), คอลัมน์ 2 เป็นตัวเลือก (สถานที่). หน่วยงานใช้จากหน่วยงานเริ่มต้นในฟอร์ม
     */
    public function actionDownloadSample()
    {
        $headers = ['หมายเลขครุภัณฑ์', 'สถานที่ (ตัวเลือก)'];
        $examples = [
            ['7910-003-0003/66.01', 'อาคาร A ห้อง 101'],
            ['7910-003-0004/66.02', 'อาคาร B ชั้น 2'],
            ['1234-001-0001', ''],
        ];

        $bom = "\xEF\xBB\xBF";
        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $bom);
        fputcsv($fp, $headers);
        foreach ($examples as $row) {
            fputcsv($fp, $row);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        $filename = 'sample_import_survey_' . date('Ymd') . '.csv';
        Yii::$app->response->sendContentAsFile($csv, $filename, [
            'mimeType' => 'text/csv',
            'inline' => false,
        ]);
        Yii::$app->end();
    }

    public function actionIndex($survey_id)
    {
        $survey = $this->findSurvey($survey_id);

        if (Yii::$app->request->isPost) {
            $file = UploadedFile::getInstanceByName('csv_file');
            if (!$file || !in_array(strtolower($file->extension), ['csv'], true)) {
                Yii::$app->session->setFlash('error', 'กรุณาเลือกไฟล์ CSV');
                $importForm = new CsvImportForm();
                $importForm->load(Yii::$app->request->post());
                return $this->render('index', ['survey' => $survey, 'importForm' => $importForm]);
            }

            $path = Yii::getAlias('@runtime') . '/survey_import_' . time() . '.' . $file->extension;
            $file->saveAs($path);

            $importForm = new CsvImportForm();
            $importForm->load(Yii::$app->request->post());
            $scannedByUserId = null;
            if ($importForm->emp_id) {
                $emp = Employees::findOne($importForm->emp_id);
                if ($emp && $emp->user_id) {
                    $scannedByUserId = (int) $emp->user_id;
                }
            }

            $service = new CsvImportService();
            $service->assetNumberColumnIndex = (int) (Yii::$app->request->post('asset_number_column', 0));
            $service->scannedByUserId = $scannedByUserId;
            $defaultDept = Yii::$app->request->post('default_department_id');
            $service->defaultSurveyDepartmentId = ($defaultDept !== null && $defaultDept !== '') ? (int) $defaultDept : null;
            $result = $service->importFromCsv($survey->id, $path);

            @unlink($path);

            if (!empty($result['errors'])) {
                Yii::$app->session->setFlash('error', 'นำเข้าเสร็จด้วยข้อผิดพลาด: ' . implode(' ', array_slice($result['errors'], 0, 5)));
            }
            Yii::$app->session->setFlash('success', "นำเข้าสำเร็จ {$result['imported']} รายการ (จาก {$result['rows']} แถว)");
            return $this->redirect(['/am-survey/report/summary', 'survey_id' => $survey->id]);
        }

        $importForm = new CsvImportForm();
        return $this->render('index', ['survey' => $survey, 'importForm' => $importForm]);
    }

    private function findSurvey($id)
    {
        $survey = AssetSurvey::findOne($id);
        if (!$survey) {
            throw new NotFoundHttpException('ไม่พบโครงการสำรวจ');
        }
        return $survey;
    }
}
