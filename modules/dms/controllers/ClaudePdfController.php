<?php

// 1. ตัวอย่าง Controller สำหรับหน้าหลัก
namespace app\modules\dms\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use app\components\StampHelper;
use yii\filters\AccessControl;
use app\components\PdfStampGenerator;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentSearch;

class ClaudePdfController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'upload-with-stamp', 'download'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // เฉพาะผู้ใช้ที่ล็อกอินแล้ว
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * หน้าแสดงรายการเอกสารทั้งหมด
     */
    public function actionIndex()
    {
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * หน้าดูรายละเอียดเอกสาร
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * หน้าอัพโหลดและเพิ่มตราประทับ - ตัวอย่างแบบสมบูรณ์
     */
    public function actionUploadWithStamp()
    {
        $model = new Documents();
        
        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                $uploadedFile = UploadedFile::getInstance($model, 'pdf_file');
                
                if ($uploadedFile) {
                    // ตรวจสอบไฟล์
                    StampHelper::validatePdfFile($uploadedFile->tempName);
                    
                    // สร้างเลขที่เอกสารอัตโนมัติ
                    if (empty($model->document_number)) {
                        $model->document_number = StampHelper::generateDocumentNumber();
                    }
                    
                    // สร้างชื่อไฟล์และพาธ
                    $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $uploadedFile->name);
                    $originalPath = Yii::getAlias('@webroot/uploads/original/') . $fileName;
                    $stampedPath = Yii::getAlias('@webroot/uploads/stamped/') . $fileName;
                    
                    // บันทึกไฟล์ต้นฉบับ
                    if (!$uploadedFile->saveAs($originalPath)) {
                        throw new \Exception('ไม่สามารถบันทึกไฟล์ได้');
                    }
                    
                    // เตรียมข้อมูลสำหรับตราประทับ
                    $stampData = [
                        'doc_number' => $model->document_number,
                        'receive_date' => date('Y-m-d'),
                        'receive_time' => date('H:i'),
                        'department' => $model->department ?: Yii::$app->params['stamp']['default']['department'],
                        'office' => $model->office ?: Yii::$app->params['stamp']['default']['office'],
                        'phone' => $model->phone ?: Yii::$app->params['stamp']['default']['phone'],
                        'logo_path' => Yii::getAlias('@webroot/images/garuda_logo.png')
                    ];
                    
                    // สร้างตราประทับ
                    $stampConfig = Yii::$app->params['stamp'];
                    $stampGenerator = new PdfStampGenerator($stampConfig);
                    $stampGenerator->addStampToPdf($originalPath, $stampedPath, $stampData);
                    
                    // บันทึกข้อมูลลงฐานข้อมูล
                    $model->original_file_path = $originalPath;
                    $model->stamped_file_path = $stampedPath;
                    $model->file_size = filesize($originalPath);
                    $model->mime_type = 'application/pdf';
                    $model->receive_date = date('Y-m-d H:i:s');
                    $model->created_by = Yii::$app->user->id;
                    
                    if (!$model->save()) {
                        throw new \Exception('ไม่สามารถบันทึกข้อมูลลงฐานข้อมูลได้: ' . implode(', ', $model->getFirstErrors()));
                    }
                    
                    $transaction->commit();
                    
                    // Log การทำงาน
                    Yii::info("Document uploaded and stamped successfully: {$model->document_number}", __METHOD__);
                    
                    Yii::$app->session->setFlash('success', 
                        "อัพโหลดและเพิ่มตราประทับเรียบร้อยแล้ว<br>เลขที่เอกสาร: {$model->document_number}"
                    );
                    
                    return $this->redirect(['view', 'id' => $model->id]);
                }
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                
                // ลบไฟล์ที่อาจสร้างขึ้นแล้ว
                if (isset($originalPath) && file_exists($originalPath)) {
                    unlink($originalPath);
                }
                if (isset($stampedPath) && file_exists($stampedPath)) {
                    unlink($stampedPath);
                }
                
                Yii::error("Error uploading document: " . $e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            }
        }
        
        return $this->render('upload', ['model' => $model]);
    }
    
    /**
     * ดาวน์โหลดไฟล์ PDF
     */
    public function actionDownload($id, $type = 'stamped')
    {
        $model = $this->findModel($id);
        
        // ตรวจสอบสิทธิ์การเข้าถึง
        if (!StampHelper::checkFileAccess($model->stamped_file_path, Yii::$app->user->id)) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าถึงไฟล์นี้');
        }
        
        $filePath = ($type === 'original') ? $model->original_file_path : $model->stamped_file_path;
        
        if (!file_exists($filePath)) {
            throw new \yii\web\NotFoundHttpException('ไม่พบไฟล์ที่ระบุ');
        }
        
        $fileName = basename($filePath);
        $displayName = $model->document_number . '_' . ($type === 'original' ? 'ต้นฉบับ' : 'มีตราประทับ') . '.pdf';
        
        return Yii::$app->response->sendFile($filePath, $displayName);
    }
    
    /**
     * ลบเอกสาร
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // ลบไฟล์
        if ($model->original_file_path && file_exists($model->original_file_path)) {
            unlink($model->original_file_path);
        }
        if ($model->stamped_file_path && file_exists($model->stamped_file_path)) {
            unlink($model->stamped_file_path);
        }
        
        $model->delete();
        
        Yii::$app->session->setFlash('success', 'ลบเอกสารเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }
    
    /**
     * API สำหรับอัพโหลดแบบ AJAX
     */
    public function actionAjaxUpload()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $uploadedFile = UploadedFile::getInstanceByName('pdf_file');
            
            if (!$uploadedFile) {
                throw new \Exception('ไม่พบไฟล์ที่อัพโหลด');
            }
            
            // ตรวจสอบไฟล์
            StampHelper::validatePdfFile($uploadedFile->tempName);
            
            // บันทึกไฟล์ชั่วคราว
            $tempFileName = uniqid() . '_' . $uploadedFile->name;
            $tempPath = Yii::getAlias('@webroot/uploads/temp/') . $tempFileName;
            
            if (!$uploadedFile->saveAs($tempPath)) {
                throw new \Exception('ไม่สามารถบันทึกไฟล์ชั่วคราวได้');
            }
            
            return [
                'success' => true,
                'message' => 'อัพโหลดเรียบร้อย',
                'data' => [
                    'tempPath' => $tempPath,
                    'fileName' => $uploadedFile->name,
                    'fileSize' => StampHelper::formatBytes($uploadedFile->size),
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * ตัวอย่างหน้าสำหรับ Bulk Upload
     */
    public function actionBulkUpload()
    {
        if (Yii::$app->request->isPost) {
            $uploadedFiles = UploadedFile::getInstancesByName('pdf_files');
            $results = [];
            
            foreach ($uploadedFiles as $file) {
                try {
                    // ประมวลผลแต่ละไฟล์
                    $model = new Document();
                    $model->document_number = StampHelper::generateDocumentNumber();
                    $model->department = Yii::$app->request->post('department', 'กรมการสาธารณสุข');
                    $model->office = Yii::$app->request->post('office', 'กรุงเทพมหานคร');
                    
                    // ประมวลผลไฟล์ (คล้ายกับ actionUploadWithStamp)
                    // ... โค้ดประมวลผล ...
                    
                    $results[] = [
                        'success' => true,
                        'filename' => $file->name,
                        'doc_number' => $model->document_number
                    ];
                    
                } catch (\Exception $e) {
                    $results[] = [
                        'success' => false,
                        'filename' => $file->name,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return $this->render('bulk-result', ['results' => $results]);
        }
        
        return $this->render('bulk-upload');
    }
    
    protected function findModel($id)
    {
        if (($model = Document::findOne($id)) !== null) {
            return $model;
        }
        
        throw new \yii\web\NotFoundHttpException('ไม่พบเอกสารที่ระบุ');
    }
}
