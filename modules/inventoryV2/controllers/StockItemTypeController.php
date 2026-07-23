<?php

namespace app\modules\inventoryV2\controllers;

use app\models\Categorise;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

/**
 * จัดการประเภทวัสดุ (asset_type ใน categorise, category_id = 4)
 */
class StockItemTypeController extends Controller
{
    const ASSET_TYPE_NAME = 'asset_type';
    const ASSET_TYPE_CATEGORY_ID = 4;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'import' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * รายการประเภทวัสดุ
     */
    public function actionIndex()
    {
        $q = trim((string) Yii::$app->request->get('q', ''));
        $dataProvider = new ActiveDataProvider([
            'query' => $this->createListQuery($q),
            'sort' => false,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'q' => $q,
        ]);
    }

    /**
     * ดาวน์โหลด template สำหรับนำเข้า Excel
     */
    public function actionTemplate()
    {
        return $this->sendSpreadsheet(
            $this->createSpreadsheet(false),
            'stock-item-type-template.xlsx'
        );
    }

    /**
     * ส่งออกประเภทวัสดุตามตัวกรองปัจจุบัน
     */
    public function actionExport()
    {
        $q = trim((string) Yii::$app->request->get('q', ''));
        $models = $this->createListQuery($q)->all();

        return $this->sendSpreadsheet(
            $this->createSpreadsheet(true, $models),
            'stock-item-types-' . date('Ymd-His') . '.xlsx'
        );
    }

    /**
     * นำเข้าประเภทวัสดุจาก Excel โดย upsert ตามรหัสประเภท
     */
    public function actionImport()
    {
        $file = UploadedFile::getInstanceByName('excel_file');
        if ($file === null) {
            Yii::$app->session->setFlash('danger', 'กรุณาเลือกไฟล์ Excel ก่อนนำเข้า');
            return $this->redirect(['index']);
        }

        $extension = strtolower($file->extension);
        if (!in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            Yii::$app->session->setFlash('danger', 'รองรับเฉพาะไฟล์ .xlsx, .xls หรือ .csv');
            return $this->redirect(['index']);
        }

        try {
            $readerType = $extension === 'csv' ? 'Csv' : ($extension === 'xls' ? 'Xls' : 'Xlsx');
            $reader = IOFactory::createReader($readerType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->tempName);
            $result = $this->importSpreadsheet($spreadsheet);
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('danger', 'ไม่สามารถอ่านไฟล์ Excel ได้ กรุณาตรวจสอบรูปแบบไฟล์');
            return $this->redirect(['index']);
        }

        if (!empty($result['errors'])) {
            Yii::$app->session->setFlash('danger', implode("\n", $result['errors']));
            return $this->redirect(['index']);
        }

        if ($result['created'] === 0 && $result['updated'] === 0) {
            Yii::$app->session->setFlash('warning', 'ไม่พบรายการสำหรับนำเข้า');
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash(
            'success',
            'นำเข้า Excel สำเร็จ: เพิ่มใหม่ ' . $result['created'] . ' รายการ, อัปเดต ' . $result['updated'] . ' รายการ'
        );

        return $this->redirect(['index']);
    }

    protected function createListQuery($q = '')
    {
        $query = Categorise::find()
            ->where([
                'name' => self::ASSET_TYPE_NAME,
                'category_id' => self::ASSET_TYPE_CATEGORY_ID,
            ])
            ->andWhere(['or', ['active' => 1], ['active' => null]]);

        if ($q !== '') {
            $query->andWhere([
                'or',
                ['like', 'code', $q],
                ['like', 'title', $q],
                ['like', 'description', $q],
            ]);
        }

        return $query->orderBy(['code' => SORT_ASC, 'title' => SORT_ASC]);
    }

    protected function createSpreadsheet($includeData = false, array $models = [])
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ประเภทวัสดุ');

        $headers = ['รหัสประเภท*', 'ชื่อประเภทวัสดุ*', 'รายละเอียด', 'สถานะ'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EDF2F7');
        $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (['A' => 18, 'B' => 30, 'C' => 42, 'D' => 18] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        if ($includeData) {
            $row = 2;
            foreach ($models as $model) {
                $sheet->setCellValueExplicit('A' . $row, (string) $model->code, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('B' . $row, $model->title);
                $sheet->setCellValue('C' . $row, $model->description);
                $sheet->setCellValue('D' . $row, (string) (isset($model->active) ? (int) $model->active : 1));
                $row++;
            }
        } else {
            $guide = $spreadsheet->createSheet();
            $guide->setTitle('วิธีใช้งาน');
            $guide->fromArray([
                ['คอลัมน์', 'รายละเอียด'],
                ['รหัสประเภท*', 'ต้องไม่ซ้ำ เช่น M1-01'],
                ['ชื่อประเภทวัสดุ*', 'ชื่อที่แสดงในระบบ'],
                ['รายละเอียด', 'ไม่บังคับ'],
                ['สถานะ', 'ใส่ 1 เพื่อใช้งาน หรือ 0 เพื่อปิดใช้งาน'],
            ], null, 'A1');
            $guide->getStyle('A1:B1')->getFont()->setBold(true);
            $guide->getColumnDimension('A')->setWidth(24);
            $guide->getColumnDimension('B')->setWidth(48);
        }

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    protected function sendSpreadsheet(Spreadsheet $spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return Yii::$app->response->sendContentAsFile($content, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false,
        ]);
    }

    protected function importSpreadsheet(Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $errors = [];
        $seenCodes = [];
        $created = 0;
        $updated = 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            for ($row = 2; $row <= $highestRow; $row++) {
                $code = $this->readCell($sheet, 'A', $row);
                $title = $this->readCell($sheet, 'B', $row);
                $description = $this->readCell($sheet, 'C', $row);
                $activeValue = $this->readCell($sheet, 'D', $row);

                if ($code === '' && $title === '' && $description === '' && $activeValue === '') {
                    continue;
                }

                if ($code === '') {
                    $errors[] = 'แถว ' . $row . ': กรุณาระบุรหัสประเภท';
                    continue;
                }
                if ($title === '') {
                    $errors[] = 'แถว ' . $row . ': กรุณาระบุชื่อประเภทวัสดุ';
                    continue;
                }

                $codeKey = strtolower($code);
                if (isset($seenCodes[$codeKey])) {
                    $errors[] = 'แถว ' . $row . ': รหัสประเภทซ้ำกับแถว ' . $seenCodes[$codeKey];
                    continue;
                }
                $seenCodes[$codeKey] = $row;

                $active = $this->parseActiveValue($activeValue);
                if ($active === null) {
                    $errors[] = 'แถว ' . $row . ': สถานะต้องเป็น 1, 0, ใช้งาน หรือ ปิดใช้งาน';
                    continue;
                }

                $model = Categorise::findOne([
                    'name' => self::ASSET_TYPE_NAME,
                    'category_id' => self::ASSET_TYPE_CATEGORY_ID,
                    'code' => $code,
                ]);
                $isNew = $model === null;
                if ($isNew) {
                    $model = new Categorise();
                    $model->name = self::ASSET_TYPE_NAME;
                    $model->category_id = self::ASSET_TYPE_CATEGORY_ID;
                }

                $model->code = $code;
                $model->title = $title;
                $model->description = $description;
                $model->active = $active;

                if (!$model->save()) {
                    $errors[] = 'แถว ' . $row . ': ' . implode(', ', $model->getFirstErrors());
                    continue;
                }

                $isNew ? $created++ : $updated++;
            }

            if (!empty($errors)) {
                $transaction->rollBack();
                return ['created' => 0, 'updated' => 0, 'errors' => $errors];
            }

            $transaction->commit();
            return ['created' => $created, 'updated' => $updated, 'errors' => []];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    protected function readCell($sheet, $column, $row)
    {
        $value = $sheet->getCell($column . $row)->getFormattedValue();
        return trim((string) $value);
    }

    protected function parseActiveValue($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return 1;
        }

        $normalized = function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);

        if (in_array($normalized, ['1', 'ใช้งาน', 'active', 'yes', 'y', 'true'], true)) {
            return 1;
        }
        if (in_array($normalized, ['0', 'ปิดใช้งาน', 'inactive', 'no', 'n', 'false'], true)) {
            return 0;
        }

        return null;
    }

    /**
     * แสดงรายละเอียดประเภทวัสดุ
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'title' => 'แสดงประเภทวัสดุ',
                'content' => $this->renderAjax('view', ['model' => $model]),
                'footer' => '',
            ];
        }

        return $this->render('view', ['model' => $model]);
    }

    /**
     * เพิ่มประเภทวัสดุ
     */
    public function actionCreate()
    {
        $model = new Categorise();
        $model->name = self::ASSET_TYPE_NAME;
        $model->category_id = self::ASSET_TYPE_CATEGORY_ID;
        $model->active = 1;

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->code)) {
                $model->addError('code', 'กรุณาระบุรหัสประเภท');
            }
            if (empty($model->title)) {
                $model->addError('title', 'กรุณาระบุชื่อประเภทวัสดุ');
            }
            if (!$model->hasErrors() && $model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'message' => 'เพิ่มประเภทวัสดุเรียบร้อยแล้ว'];
                }
                Yii::$app->session->setFlash('success', 'เพิ่มประเภทวัสดุเรียบร้อยแล้ว');
                return $this->redirect(['index']);
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'title' => 'เพิ่มประเภทวัสดุ',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => '',
            ];
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * แก้ไขประเภทวัสดุ
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->code)) {
                $model->addError('code', 'กรุณาระบุรหัสประเภท');
            }
            if (empty($model->title)) {
                $model->addError('title', 'กรุณาระบุชื่อประเภทวัสดุ');
            }
            if (!$model->hasErrors() && $model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'message' => 'บันทึกเรียบร้อยแล้ว'];
                }
                Yii::$app->session->setFlash('success', 'บันทึกเรียบร้อยแล้ว');
                return $this->redirect(['index']);
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'title' => 'แก้ไขประเภทวัสดุ',
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => '',
            ];
        }
        return $this->render('update', ['model' => $model]);
    }
    /**
     * ลบประเภทวัสดุ (soft: ตั้ง active = 0 ถ้ามีฟิลด์ ไม่ก็ลบจริง)
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (isset($model->active)) {
            $model->active = 0;
            $model->save(false);
        } else {
            $model->delete();
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true];
        }
        Yii::$app->session->setFlash('success', 'ลบประเภทวัสดุเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        $model = Categorise::findOne([
            'id' => $id,
            'name' => self::ASSET_TYPE_NAME,
            'category_id' => self::ASSET_TYPE_CATEGORY_ID,
        ]);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบประเภทวัสดุที่ต้องการ');
    }
}
